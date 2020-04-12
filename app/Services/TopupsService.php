<?php
namespace App\Services;

use App\Services\PartnersService;
use App\Services\MembershipsService;
use App\Services\TransactionsService;
use App\Services\PublicFunctionService;
use Log;

class TopupsService
{
      /**
     * @var App\Services\PartnersService
     */
    protected $partner_codes;

     /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;  

     /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;
    
      /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    public function __construct(
        PartnersService $partner_codes,
        MembershipsService $memberships,
        TransactionsService $transactions,
        PublicFunctionService $public_functions
    )
    {
        $this->partner_codes = $partner_codes;
        $this->memberships = $memberships;
        $this->transactions = $transactions;
        $this->public_functions = $public_functions;
    }

    public function updateTopupMomoMembership($token, $data){

        $timestamp = $data['timestamp'] ?? null;
        $partner_code = $data['partnerCode'] ?? null;
        $hash = $data['hash'] ?? null;

        Log::info('$$MOMO-data: '.json_encode($data));

        if(empty($timestamp)) {  
            return ['status'=> 404, 'message'=> 'Chuổi timestamp không tìm thấy', 'data' => []];
        }
        if(empty($partner_code)) {  
            return ['status'=> 404, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []];
        }
        if(empty($hash)) {  
            return ['status'=> 404, 'message'=> 'Mã hash không tìm thấy', 'data' => []]; 
        }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ 
            return ['status'=> 404, 'message'=> 'Mã đối tác không tồn tại', 'data' => []]; 
        }

        Log::info('$$MOMO-partner: '.json_encode($partner));

        $app_key = $partner->app_key;
    
        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;

        if(empty($app_key_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        
        $hash_convert_str = $this->public_functions->deCrypto($hash, $app_key);
        $hash_result_json = json_decode($hash_convert_str);

        Log::info('$$MOMO-hash_result_json: '.json_encode($hash_result_json));

        $amount_momo = $hash_result_json->amount ?? null;
        $partner_code_momo = $hash_result_json->partnerCode ?? null;
        $qrcode_momo = $hash_result_json->qrcode ?? null;
        $transaction_code_momo = $hash_result_json->transactionCode ?? null;

        if(round($amount_momo) <= 0){ 
            return ['status'=> 404, 'message'=> 'Số tiền không hợp lệ', 'data' => []]; 
        }else{
            $amount_momo = (int) $amount_momo;
            if($amount_momo < 50000){
                return ['status'=> 404, 'message'=> 'Số tiền tối thiểu là 50,000 VNĐ', 'data' => []]; 
            }
        }

        if(empty($amount_momo) || empty($partner_code_momo) || empty($qrcode_momo) || empty($transaction_code_momo)) { 
            return ['status'=> 404, 'message'=> 'Giải mã hash thất bại', 'data' => []]; 
        }      

        if(!empty($hash_result_json)){

            $qrcode_momo = strtoupper($qrcode_momo);
            $membership =  $this->memberships->getMembershipByBarcodeForApp($qrcode_momo);

            Log::info('$$MOMO-membership: '.json_encode($membership));

            if($membership['status'] != 200){
                return  $membership;              
            }

            if($membership['status'] == 200){

                if($membership['data'][0]->membershipType->code == 0){
                    
                    if(!empty($partner->group_company)){

                        Log::info('$$MOMO-group_company: '.$partner->group_company); 
    
                        $group_company = json_decode($partner->group_company);
                        if(count($group_company) > 0){
    
                            if(!in_array($membership['data'][0]->company_id ,$group_company)){
    
                                return ['status'=> 404, 'message'=> 'Chưa hổ trợ tích hợp Momo. Xin vui lòng kiểm tra lại!', 'data' => []]; 
                            }else{
    
                                $data = [
                                    "membership" => $membership['data'][0],
                                    "amount_momo" => $amount_momo,
                                    "transaction_code_momo" => $transaction_code_momo,
                                    "timestamp" => $timestamp,
                                    "type_momo" => "topup_momo"
                                ];
                                $transaction = $this->transactions->createTransactionForMomo($data);
                
                                if($transaction == false) {
                                    return ['status'=> 404, 'message'=> 'Giao dịch nạp tiền thất bại. Xin vui lòng kiểm tra lại!', 'data' => []]; 
                                }
                
                                //update balance
                                $membership_result  =  $membership['data'][0];
                                $membership_update = $this->memberships->getByMembershipId($membership_result['id']);
                                $membership_update->balance += $amount_momo;
                
                                if($membership_update->save()){
                
                                    $qrcode_arr = $this->public_functions->mb_count_chars($qrcode_momo);
                                    $qrcode_str_result = '';
                                    foreach($qrcode_arr as $key => $value){
                                        if($key <= 2){ $qrcode_str_result .= $value;  }
                                        if($key > 2 && $key < 10){ $qrcode_str_result .= "*"; }
                                        if($key >= 10){ $qrcode_str_result .= $value; }
                                    }
                                    return ['status'=> 200, 'message'=> 'Thành công', 'data' => [[
                                       "info" => $qrcode_str_result,
                                       "balance" => $membership_update->balance
                                    ]]]; 
                                }
                                return ['status'=> 404, 'message'=> 'Giao dịch nạp tiền thất bại. Xin vui lòng kiểm tra lại!', 'data' => []];
                            }
                        }
                        return ['status'=> 404, 'message'=> 'Chưa hổ trợ tích hợp Momo. Xin vui lòng kiểm tra lại!', 'data' => []]; 
                    }

                    Log::info('$$MOMO-group_company not: []'); 
                    return ['status'=> 404, 'message'=> 'Chưa hổ trợ tích hợp Momo. Xin vui lòng kiểm tra lại!', 'data' => []];
                }else{

                    return ['status'=> 404, 'message'=> 'Chỉ áp dụng cho đối tượng thẻ trả trước', 'data' => []];
                }
            }
        }
    }
}
