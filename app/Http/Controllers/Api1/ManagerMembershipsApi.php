<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use App\Models\CompanyGroupKey;
use Illuminate\Http\Request;
use App\Models\Membership;
use App\Models\SubscriptionType;
use App\Services\MembershipsService;
use App\Services\DevicesService;
use App\Services\RfidCardsService;
use App\Services\PublicFunctionService;
use App\Services\PartnersService;
use App\Services\GroupKeyCompaniesService;
use Log;

class ManagerMembershipsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

     /**
     * @var App\Services\RfidCardsService
     */
    protected $public_functions;

     /**
     * @var App\Services\PartnersService
     */
    protected $partner_codes;

    /**
     * @var App\Services\GroupKeyCompaniesService
     */
    protected $group_key_companies;

    /**
     * Constructor
     */
    public function __construct(
        Request $request,
        MembershipsService $memberships,
        DevicesService $devices,
        RfidCardsService $rfidcards,
        PublicFunctionService $public_functions,
        PartnersService $partner_codes,
        GroupKeyCompaniesService $group_key_companies
    )
    {
        $this->request = $request;
        $this->memberships = $memberships;
        $this->devices = $devices;
        $this->rfidcards = $rfidcards;
        $this->public_functions = $public_functions;
        $this->partner_codes = $partner_codes;
        $this->group_key_companies = $group_key_companies;
    }

    public function managerlistMemberships()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->memberships->getMemberships($input, $user->company_id);
    }

    /**
     * Operation managerGetMembershipById
     *
     * Find by ID.
     *
     * @param int $membership_id  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipById($membership_id)
    {

        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($membership_id) || (int)$membership_id < 0)
            return response('Invalid ID supplied', 404);

        $membership = $this->memberships->getMembership($membership_id);

        if (empty($membership)) {
            return response('User not found', 404);
        }

        return $membership;
    }

     /**
     * Operation managerGetMembershipByRfid
     *
     * Find by ID.
     *
     * @param string $rfid  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipByRfid($rfid)
    {

        $imei = $this->request->header('X-IMEI');
        $auth_key_group_company = $this->request->header('AUTH-KEY-GROUP-COMPANY');

        if (empty($imei))  return response('Invalid imei supplied', 404);

        if (empty($rfid)) return response('Invalid RFID supplied', 404);

        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device))  return response('Device not found', 404);

        $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid);
        if(empty($rfidcard)) return response('Rfid card not found', 404);

        $company_id = null;

        foreach($device['issueds'] as $vl){
            $company_id = $vl->company_id;
        }

        $membership = $this->memberships->getMembershipByRfidcardIdForMechine($rfidcard->id, $rfid);
        if(empty($membership)) return response('Membership not data', 404);

        //check ket group = null => not
        if(empty($auth_key_group_company)) {

            if($membership->company_id != $company_id){
                return response('Device and membership not general data', 404);
                // Log::info('Device and membership not general data');
            }
        }else{

            //check kr group is value => check card & device
            if($membership->company_id != $company_id) {

                $link_company = $this->group_key_companies->getGroupKeyCompaniesByKeyAndCompanyId($auth_key_group_company,$membership->company_id);

                if (empty($link_company)) {
                    return response('Subjects not group companies', 404);
                    // Log::info('Subjects not group companies');
                }else{
                    if($membership->membershipType->code == 1){
                        return response('Applicable for prepaid cards only', 404);
                        // Log::info('Applicable for prepaid cards only');
                    }
                }
            }
        }

        try {
            // Log::info('Co roi:'.json_encode($membership));
            return $membership;
        } catch (Exception $e) {
            return response('oki', 200);
        }
    }

    /**
     * Operation managerUpdateMembership
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateMembership()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'membershiptype_id' => 'required|integer|min:1',
            'expiration_date'=> 'required',
            'actived' => 'required'
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] =   $user->company_id;
        return $this->memberships->editMemberShip($input);
    }

    public function managerlistMembershipActive()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->memberships->getMembershipAct($input, $user->company_id);
    }

    /**
     * Operation managerTransactionGetMembershipDetailByRfid
     *
     * Find by ID.
     *
     * @param string $rfid  (required)
     *
     * @return Http response
     */
    public function managerTransactionGetMembershipDetailByRfid($rfid, $transaction_type)
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['rfid'] = $rfid;
        $input['transaction_type'] = $transaction_type;
        $input['company_id'] = $user->company_id;

        return $this->memberships->viewDetailTransaction($input);

    }

     /**
     * Operation managerTransactionGetMembershipCardDetailByRfid
     *
     * Find by ID.
     *
     * @param string $rfid  (required)
     *
     * @return Http response
     */
    // public function managerTransactionGetMembershipCardDetailByRfid($rfid, $transaction_type, $datefrom, $dateto)
    // {
    //     $user = $this->requiredAuthUser();
    //     if (empty($user)) return response('token_invalid', 401);

    //     $input['rfid'] = $rfid;
    //     $input['transaction_type'] = $transaction_type;
    //     $input['company_id'] = $user->company_id;
    //     $input['datefrom'] = $datefrom;
    //     $input['dateto'] = $dateto;

    //     return $this->memberships->viewDetailTransactionCard($input);
    // }

     /**
     * Operation managerListMembershipsByInputAndBySearch
     *
     * List of Memberships.
     *
     * @param string $key_input  (required)
     * @param string $key_search  (required)
     *
     * @return Http response
     */
    public function managerListMembershipsByInputAndBySearch()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->memberships->getMembershipByInputAndBySearch($input);
    }

    /**
     * Operation searchMembershipsDetail
     *
     * List of Memberships.
     *
     * @param string $key_input  (required)
     * @param string $key_search  (required)
     *
     * @return Http response
     */
    public function searchMembershipsDetail()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->memberships->searchMembershipsDetail($input);
    }

    /**
     * Operation managerUpdateActivedMembershipById
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateActivedMembershipById()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->memberships->UpdateActivedMembershipById($input);
    }

    /**
     * Operation managerUpdateRfidMembershipById
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateRfidMembershipById()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->memberships->UpdateRfidMembershipById($input);
    }

    /**
     * Operation managerGetMembershipByBarcode
     *
     * Find by ID.
     *
     * @param string $barcode  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipByBarcodeForApp($barcode)
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');

        if(empty($token)) {  return ['status'=> false, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($partner_code)) {  return ['status'=> false, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []]; }
        if(empty($barcode)){ return ['status'=> false, 'message'=> 'Mã barcode không tim thấy', 'data'=> []]; }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ return ['status'=> false, 'message'=> 'Mã đối tác không tồn tại', 'data' => []];  }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;
        $timestamp = $token_de->timestamp ?? null;

        if(empty($app_key_de) || empty($timestamp)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $diff_time = $this->public_functions->s_datediff('i',date('d-m-y H:i:s'),date('d-m-y H:i:s',$timestamp));
        if ($diff_time > 10) return ['status'=> false, 'message'=> 'Hết thời gian gọi API', 'data' => []];

        $barcode = strtoupper($barcode);
        $membership =  $this->memberships->getMembershipByBarcodeForApp($barcode);

        if($membership){ return $membership; }
    }

     /**
     * Operation managerGetMembershipTransactionByBarcode
     *
     * Find by ID.
     *
     * @param string $barcode  (required)
     *
     * @return Http response
     */
    public function managerGetMembershipTransactionByBarcodeForApp($barcode)
    {
        $token = $this->request->header('Token');
        $partner_code = $this->request->header('partnerCode');

        if(empty($token)) {  return ['status'=> false, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($partner_code)) {  return ['status'=> false, 'message'=> 'Mã đối tác không tìm thấy', 'data' => []]; }
        if(empty($barcode)){ return ['status'=> false, 'message'=> 'Mã barcode không tim thấy', 'data'=> []]; }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){ return ['status'=> false, 'message'=> 'Mã đối tác không tồn tại', 'data' => []];  }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;
        $timestamp = $token_de->timestamp ?? null;

        if(empty($app_key_de) || empty($timestamp)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $diff_time = $this->public_functions->s_datediff('i',date('d-m-y H:i:s'),date('d-m-y H:i:s',$timestamp));
        if ($diff_time > 10) return ['status'=> false, 'message'=> 'Hết thời gian gọi API', 'data' => []];

        $barcode = strtoupper($barcode);
        $membership =  $this->memberships->getMembershipTransactionsByBarcodeForApp($barcode);

        if($membership){return $membership; }
    }

    /**
     * Operation managerEditMembershipByBarcodeToFormApp
     *
     * edit memberships by form app and barcode.
     *
     *
     * @return Http response
     */
    public function managerEditMembershipByBarcodeToForApp()
    {
        $token = $this->request->header('Token');
        $input = $this->request->all();

        if(empty($token)) {  return ['status'=> false, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($input)) {  return ['status'=> false, 'message'=> 'Không tìm thấy tham số', 'data' => []]; }

        try {
            return $this->memberships->editMembershipByBarcodeToForApp($token, $input);
        } catch (Exception $e) {
            return response('ok', 200);
        }
    }

    /**
     * Operation managerRegisterMembershipForApp
     *
     * register memberships by app.
     *
     *
     * @return Http response
     */
    public function managerRegisterMembershipForApp()
    {
        $token = $this->request->header('Token');
        $input = $this->request->all();

        if(empty($token)) {  return ['status'=> false, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($input)) {  return ['status'=> false, 'message'=> 'Không tìm thấy tham số', 'data' => []]; }

        try {
            return $this->memberships->registerMembershipForApp($token, $input);
        } catch (Exception $e) {
            return response('ok', 200);
        }
    }
}
