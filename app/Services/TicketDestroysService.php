<?php
namespace App\Services;

use App\Models\TicketDestroy;
use App\Services\ShiftsService;
use App\Services\TransactionsService;
use App\Services\TicketPricesService;
use App\Services\UsersService;
use App\Services\MembershipsService;
use Intervention\Image\ImageManagerStatic as Image;

class TicketDestroysService
{
    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;

    /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_prices;

    /**
     * @var App\Services\UsersService
     */

    protected $users;
      /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;
    

    public function __construct(
        ShiftsService $shifts,
        TransactionsService $transactions,
        TicketPricesService $ticket_prices,
        UsersService $users,
        MembershipsService $memberships
    )
    {
        $this->shifts = $shifts;
        $this->transactions = $transactions;
        $this->ticket_prices = $ticket_prices;
        $this->users = $users;
        $this->memberships = $memberships;
    }

    public function saveImgBase64($data)
    {
        $img = explode(';',  $data['image']);
        $img_result = explode('/',  $img[0] );
        $fileName = $data['transaction_id']."_".time().'.'.$img_result[1]; 
        $path = public_path()."/img/ticket-error/".$fileName;
        if( Image::make(file_get_contents($data['image']))->save($path)){ return $fileName; }
    }

    public function addTicketDestroyInTransaction($data){

        $company_id = $data['company_id'];
        $user_id = $data['user_id'];
        $subuser_id = $data['subuser_id'];
        $shift_id = $data['shift_id'];
        $transaction_id = $data['transaction_id'];
        $ticket_price_id = $data['ticket_price_id'];
        $amount = $data['amount'];
        $type = $data['type'];
        $ticket_number = $data['ticket_number'];
        $printed_at = $data['printed_at'];
        $description = $data['description'];
        $image = $data['image'];

        //  ---------------If build server new , show comment there ----------------------------
        // $fileName  = '';
        // if($image){ $fileName = $this->saveImgBase64($data);}

        if($data){

            $check_ticket_destroy = $this->checkTicketDestroyByOptionExists($data);
            if($check_ticket_destroy){
    
                $check_ticket_destroy->delete();
            }

            $ticket_destroy = new TicketDestroy();
            $ticket_destroy->company_id = $company_id;
            $ticket_destroy->user_id = $user_id;
            $ticket_destroy->shift_id = $shift_id;
            $ticket_destroy->amount = $amount;
            $ticket_destroy->type = $type;
            $ticket_destroy->transaction_id = $transaction_id;
            $ticket_destroy->ticket_price_id = $ticket_price_id;
            $ticket_destroy->ticket_number = $ticket_number;
            $ticket_destroy->printed_at = $printed_at;
            $ticket_destroy->description = $description;
            $ticket_destroy->accept = 0;
            $ticket_destroy->subuser_id = $subuser_id;
            $ticket_destroy->image = $image;

            //  ---------------If build server new , show comment there ----------------------------
            // $ticket_destroy->image = $fileName;
    
            if($ticket_destroy->save()){

                if($this->transactions->updateTicketDestroyByTransactionId($transaction_id, -1)){
                    $this->shifts->updateHiddenedByShitfId($shift_id, 1);
                    return $ticket_destroy;
                }
            }
        }
       
        return response('Send repuest not found', 404);
    }

    public function viewTicketDestroy($data){

        if($data){

            $company_id = $data['company_id'];
            $accept = $data['accept'] ?? null;
            $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
            $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));

            $ticket_destroys = [];
            $where_accept = [];
            
            switch($accept){
                case  null:
                    $where_accept = [0];
                    break;
                case 0:
                    $where_accept = [1, -1];
                    break;
                case 1:
                    $where_accept = [1];
                    break;
                case -1:
                    $where_accept = [-1];
                    break;
            }

            $ticket_destroys = TicketDestroy::where('company_id', $company_id)
                ->whereIn('accept', $where_accept)
                ->with('user', 'subuser')
                ->where('created_at', '>=', $from_date)
                ->where('created_at', '<=', $to_date)
                ->orderBy('shift_id')
                ->get();
        
            return  $ticket_destroys;
        }
    }

    public function getTicketDestroyById($id){

        return TicketDestroy::where('id', $id)->first();
    }

    public function checkTicketDestroyByShiftExist($shift_id){

        return TicketDestroy::where('shift_id', $shift_id)->where('accept', 0)->exists();
    }

    public function checkTicketDestroyByOptionExists($data){

        return TicketDestroy::where('company_id', $data['company_id'])
                ->where('shift_id', $data['shift_id'])
                ->where('transaction_id', $data['transaction_id'])
                ->where('ticket_price_id', $data['ticket_price_id'])
                ->where('ticket_number', $data['ticket_number'])
                ->where('type', $data['type'])
                ->where('amount', $data['amount'])
                ->where('printed_at', $data['printed_at'])
                ->first();
    }

    public function countTicketDestroyByShiftExist($shift_id){
        return TicketDestroy::where('shift_id', $shift_id)->where('accept', 0)->count();
    }

    public function acceptTicketDestroy($data){

        $ticket_destroy_id = $data['id'];
        $type = $data['type'];

        $ticket_destroy = $this->getTicketDestroyById($ticket_destroy_id);

        if($ticket_destroy){

            $transaction_id = $ticket_destroy->transaction_id;
            $shif_id_before = $ticket_destroy->shift_id;
            

            // accept ticket destory :  $ticket_destroy->accept = -1
            // normal  ticket destory :  $ticket_destroy->accept = 0
            // refuse ticket destory : $ticket_destroy->accept = 1
            
            if($type == 'accept'){

                $ticket_destroy->accept = -1; 

                if($ticket_destroy->save()){

                    $update_transaction_tkttroy = $this->transactions->updateTicketDestroyByTransactionId($transaction_id, 1);
                   
                    if($update_transaction_tkttroy){

                        $transaction = $this->transactions->getTransactionById($transaction_id);
                        if(!empty($transaction)) {

                            $data['rfid'] = $transaction->rfid;
                            $data['amount'] = $transaction->amount;
                            $data['type'] = $transaction->type;

                            if($data['type'] == 'deposit' || $data['type'] == 'charge' || $data['type'] == 'charge_taxi' || $data['type'] == 'charge_goods'){
                                $this->memberships->updateBackupBalance($data, $data['type']);
                            }

                            if($data['type'] == 'deposit_month'){
                                
                                $check_trans =  $this->transactions->getTransactionByOptions([
                                    ['type', 'deposit_month'],
                                    ['ticket_destroy', '!=', 1],
                                    ['rfid', $transaction->rfid],
                                    ['id', '>', $transaction->id]
                                ]);

                                if(count($check_trans) > 0){

                                    $data_up = '';
                                    
                                    foreach ($check_trans as $key => $value) {

                                        $value->created_at = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s", strtotime($value->created_at)) . " -1 month"));
                                        $value->save();

                                        if($key == (count($check_trans) - 1)){
                                            $date_explode = explode('-', $value->created_at);
                                            $data_up = $date_explode[0].'-'.$date_explode[1];
                                        }
                                    }
                                    $this->memberships->updateExpirationDateByCardMonth($data, $data_up);
                                } 

                                if(count($check_trans) == 0){

                                    $created_at = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s", strtotime($transaction->created_at)) . " -1 month"));
                                    $date_explode = explode('-', $created_at);
                                    $data_up = $date_explode[0].'-'.$date_explode[1];

                                    $this->memberships->updateExpirationDateByCardMonth($data,$data_up);
                                }
                            }

                            if($data['type'] == 'charge_month'){
                                $this->memberships->updateChargeLimitByValue($data, $transaction->type, -1);
                                $this->memberships->updateChargeCountByValue($data,  $transaction->type, -1);
                            }
                        }

                        $count_shift = $this->countTicketDestroyByShiftExist($shif_id_before);
                        
                        if($count_shift){
                            return ['msg' => 'Còn <strong>'.$count_shift.'</strong> vé thuộc ca <strong>'.$shif_id_before.' </strong> với vé <strong>'.$ticket_destroy->ticket_number.'</strong> đang chờ duyệt!<br> Vui lòng hoàn tất để thu tiền', 'status' => 200]; 
                        }else{
                            if($this->shifts->updateHiddenedByShitfId($shif_id_before, 0))
                            return ['msg' => 'Hoàn tất! Xin vui lòng thu tiền', 'status' => 200];  
                        }
                    }
                }
            }
    
            if($type == 'refuse'){

                $ticket_destroy->accept = 1; 
                if($ticket_destroy->save()){

                    $update_transaction_tkttroy = $this->transactions->updateTicketDestroyByTransactionId($transaction_id, 0);
                    if($update_transaction_tkttroy){

                        $count_shift = $this->countTicketDestroyByShiftExist( $shif_id_before);

                        if($count_shift){
                            return ['msg' => 'Còn <strong>'.$count_shift.'</strong> vé thuộc ca <strong>'.$shif_id_before.' </strong> với vé <strong>'.$ticket_destroy->ticket_number.'</strong> đang chờ thực hiện!<br> Vui lòng hoàn tất để thu tiền', 'status' => 200]; 
                        }else{

                            if($this->shifts->updateHiddenedByShitfId($shif_id_before, 0)){
                                return ['msg' => 'Hoàn tất! Xin vui lòng thu tiền', 'status' => 200];  
                            }
                        }
                    }
                } 
            }
        }

        return response('data not found', 404);
    }
}
