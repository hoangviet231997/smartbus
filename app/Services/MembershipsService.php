<?php
namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\MembershipTmp;
use App\Models\RfidCard;
use App\Models\Notify;
use App\Models\NotifyType;
use App\Models\RoutesBusStation;
use App\Services\RfidCardsService;
use App\Services\PushLogsService;
use App\Services\TransactionsServiceVersion2;
use App\Services\UsersService;
use App\Services\MembershipTypeService;
use App\Services\PublicFunctionService;
use App\Services\PartnersService;
use App\Services\BusStationsService;
use App\Services\TicketPricesService;
use App\Services\TicketTypesService;
use DB;
use Log;
use Carbon\Carbon;

use Intervention\Image\ImageManagerStatic as Image;
use phpDocumentor\Reflection\Types\Null_;

class MembershipsService
{
    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

     /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\TransactionsServiceVersion2
     */
    protected $transaction;

    /**
     * @var App\Services\UsersService
     */
    protected $user;

    /**
     * @var App\Services\MembershipTypeService
     */
    protected $membership_types;

    /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    /**
     * @var App\Services\PartnersService
     */
    protected $partner_codes;

      /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

    /**
     * @var App\Services\TicketPricesService
    */
    protected $ticket_prices;

    /**
     * @var App\Services\TicketTypesService
    */
    protected $ticket_types;

    public function __construct(
        RfidCardsService $rfidcards,
        PushLogsService $push_logs,
        TransactionsServiceVersion2 $transaction,
        UsersService $user,
        MembershipTypeService $membership_types,
        PublicFunctionService $public_functions,
        PartnersService $partner_codes,
        BusStationsService $bus_stations,
        TicketPricesService $ticket_prices,
        TicketTypesService $ticket_types
        )
    {
        $this->rfidcards = $rfidcards;
        $this->push_logs = $push_logs;
        $this->transaction = $transaction;
        $this->user = $user;
        $this->membership_types = $membership_types;
        $this->public_functions = $public_functions;
        $this->partner_codes = $partner_codes;
        $this->bus_stations = $bus_stations;
        $this->ticket_prices = $ticket_prices;
        $this->ticket_types = $ticket_types;
    }

    // fucntion for web
    public function insertCard($device, $data){

        $result = ["status"=>false, "message"=>null];

        foreach ($data as $value) {
            $timestamp = date("Y-m-d H:i:s", $value['timestamp']);
            $action = $value['action'];
            $subject_type = $value['subject_type'];
            $subject_data = json_decode($value['subject_data']);
            $user_id = $value['user_id'];

            if($user_id){

                $user = $this->user->getUserByKey('id', $user_id);
                $company_id = $user['company_id'];

                //get membership type prepaid
                $membership_type = $this->membership_types->getDataByCompanyId($company_id);

                //$result["message"] = $user;
                switch ($action) {
                    case 'create_card':
                        // get data device
                        $row = [];
                        $row['rfid'] = $subject_data->rfid;

                        if(empty($membership_type)){

                            $membership_type_params = [];
                            $membership_type_params['name'] = "Thẻ 0%";
                            $membership_type_params['deduction'] = 0;
                            $membership_type_params['code'] = 0;
                            $membership_type_params['company_id'] = $company_id;
                            $membership_type = $this->membership_types->created($membership_type_params);
                        }

                        $row['membershiptype_id'] = $membership_type->id;
                        $row['company_id'] = $company_id;
                        $row['user_id'] = $user_id;
                        $row['created'] = $timestamp;

                        $isNull = false;

                        if(!$row['membershiptype_id']){
                            $result["message"] = "membership type is empty.";
                            $isNull = true;
                        }

                        if(!$row['rfid']){
                            $result["message"] = "rfid is empty.";
                            $isNull = true;
                        }

                        if(!$row['company_id']){
                            $result["message"] = "company is empty.";
                            $isNull = true;
                        }

                        if(!$isNull){

                            $rfId = null;
                            // chech exist rfid card
                            $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($row['rfid']);
                            if ($rfidcard) {
                                if (!$rfidcard->usage_type && !$rfidcard->target_id) {
                                    $rfId = $rfidcard->id;
                                } else {
                                    if (!$rfidcard->target_id) {
                                        if ($rfidcard->usage_type == 'membership') {
                                            $rfId = $rfidcard->id;
                                        }
                                    }
                                }
                            } else {
                                //insert Rfid card
                                $rfidcardAdd = $this->rfidcards->insertRfidCard($row);
                                if ($rfidcardAdd) {
                                    $rfId = $rfidcardAdd->id;
                                } else {
                                    $result["message"] = "Rfid create fail.";
                                }
                            }

                            if (!$rfId) {
                                $result["message"] = "Rfid has been used.";
                            } else {
                                // insert Member ship
                                $membership = new Membership();
                                $membership->company_id = $row['company_id'];
                                $membership->rfidcard_id = $rfId;
                                $membership->membershiptype_id = $row['membershiptype_id'];
                                $membership->user_id = $row['user_id'];
                                $membership->charge_count = 0;
                                $membership->created_at = $row['created'];
                                $membership->updated_at = $row['created'];
                                $membership->actived = 0;

                                if ($membership->save()) {
                                    //update Rfid card
                                    $dataUp = ["rfidcard"=>$rfId, "membership_id"=>$membership->id];
                                    $rfidcardUp = $this->rfidcards->editRfidCard($dataUp);
                                    if ($rfidcardUp) {
                                        $result["status"] = true;
                                        $result["message"] = "Card has been successfully created.";

                                        // $membership_pushlog = $membership->toArray();
                                        // unset($membership_pushlog['created_at']);
                                        // unset($membership_pushlog['updated_at']);

                                        // if($rfidcard){
                                        //     $membership_pushlog['rfid'] = $rfidcard->rfid;
                                        //     $membership_pushlog['barcode'] = $rfidcard->barcode ?? null;
                                        // }else{
                                        //     $membership_pushlog['rfid'] = $row['rfid'];
                                        //     $membership_pushlog['barcode'] = null;
                                        // }

                                        // $push_log = [];
                                        // $push_log['action'] = 'create';
                                        // $push_log['company_id'] = $membership_pushlog['company_id'];
                                        // $push_log['subject_id'] = $membership_pushlog['id'];
                                        // $push_log['subject_type'] = 'rfidcard';
                                        // $push_log['subject_data'] = $membership_pushlog;
                                        // $this->push_logs->createPushLog($push_log);
                                    }
                                }
                            }
                        }
                        break;
                }
            }else{
                $result["message"] = "user is empty.";
            }
        }
        return $result;
    }

    public function getMemberships($data, $company_id){

        $limit = $data['limit'];
        if (empty($limit) && $limit < 0)
            $limit = 10;
        return Membership::where('company_id', $company_id)
                        ->where('actived', '=', 0)
                        ->with('membershipType', 'rfidcard')
                        ->paginate($limit);
    }

    public function getMembership($id){
        return Membership::where('memberships.id', $id)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->with('membershipType', 'rfidcard', 'ticketPrice')
                    ->select('memberships.*', 'group_bus_stations.name as group_busstion_name')
                    ->first();
    }

    public function editMemberShip($data){

        $station_data = $data['station_data'] ?? null;
        $gr_bus_station_id = (int)$data['gr_bus_station_id'] ?? null;

        // active card
        if ($data['type_edit'] == 1){

            if ($data['cmnd']) {
                $check_exists = Membership::where('cmnd',$data['cmnd'])->where('company_id',$data['company_id'])->first();
                if (!empty($check_exists)) return response('CMND is exists', 404);
            }

            if ($data['phone']) {
                $check_exists = Membership::where('phone',$data['phone'])->where('company_id',$data['company_id'])->first();
                if (!empty($check_exists)) return response('Phone is exists', 404);
            }

            $rfidcard = $this->rfidcards->getRfidCardByRfid($data['rfid']);

            if(!empty($rfidcard)){

                if(empty($rfidcard->barcode)){
                    $digits = 13;
                    $barcode_flag = false;
                    $barcode = '';
                    while(!$barcode_flag) {
                        $random = rand(0, pow(10, 10)-1);
                        $barcode = strtoupper(str_pad($random, $digits, 'MBS', STR_PAD_LEFT));
                        if (!$this->rfidcards->getExistsByBarcode($barcode)) { $barcode_flag = true; }
                    }
                    $rfidcard->barcode = $barcode;
                }

                if($rfidcard->save()){

                    $membership = $this->getMembership($data['id']);
                    $membership->actived = 1;
                    $membership_type = $this->membership_types->getByIdAndCompanyId((int)$data['membershiptype_id'], (int)$data['company_id']);
                    //type card month
                    if($membership_type->code == 1){

                        $membership->actived = -2;

                        $membership->ticket_price_id = (int)$data['ticket_price_id'] ??  null;
                        $ticket_price = $this->ticket_prices->getPriceById($membership->ticket_price_id);
                        if(!empty($ticket_price)) if(!empty($ticket_price->charge_limit)) $membership->charge_limit = 0;

                        $membership->gr_bus_station_id = null;
                        $membership->station_data = null;

                        //check mbs ap dung chang
                        if($gr_bus_station_id > 0 && $gr_bus_station_id != null){

                            $group_bus_station = $this->bus_stations->getGroupBusStationById($gr_bus_station_id);

                            if(!empty($group_bus_station)){

                                $route_bus_station = RoutesBusStation::whereIn('bus_station_id', json_decode($group_bus_station->bus_stations))->with('busStation')->get();
                                if(count($route_bus_station) > 0){

                                    $station_result = [];

                                    foreach($route_bus_station as $key => $value){

                                        $station_obj = new \stdClass;
                                        $station_obj->route_id = (int)$value->route_id;
                                        $station_obj->id = (int)$value['busStation']->id;
                                        $station_obj->name = $value['busStation']->name;
                                        $station_obj->station_order = (int) $value['busStation']->station_order;
                                        $station_obj->direction =  $value['busStation']->direction;
                                        $station_obj->station_relative =  $value['busStation']->station_relative;
                                        $station_obj->lat = $value['busStation']->position->getLat();
                                        $station_obj->lng = $value['busStation']->position->getLng();

                                        $station_result[] =  $station_obj;
                                    }

                                    $membership->station_data = json_encode($station_result, JSON_UNESCAPED_UNICODE) ?? null;
                                    $membership->gr_bus_station_id = $gr_bus_station_id;
                                }
                            }
                        }

                        //check mbs ap dung cho tuyen co dinh
                        if($gr_bus_station_id == 0){

                            if(!empty($station_data)){

                                if(count($station_data) > 0){

                                    $tmp_station_data_save = [];

                                    foreach ($station_data as $v_station_data) {

                                        $v_station_data = (object)$v_station_data;

                                        $station_obj = new \stdClass;
                                        $station_obj->route_id = (int)$v_station_data->route_id;
                                        $station_obj->id = (int)$v_station_data->id;
                                        $station_obj->name = $v_station_data->name;
                                        $station_obj->station_order = (int) $v_station_data->station_order;
                                        $station_obj->direction =  $v_station_data->direction;
                                        $station_obj->lat = $v_station_data->lat;
                                        $station_obj->lng = $v_station_data->lng;
                                        $station_obj->station_relative =  $v_station_data->station_relative;
                                        $tmp_station_data_save[] = $station_obj;

                                        if($v_station_data->station_relative != null){

                                            $bus_stations = $this->bus_stations->getBusStationByNotIDAndInArray(json_decode($v_station_data->station_relative), $v_station_data->id);

                                            if(count($bus_stations)){

                                                foreach ($bus_stations as $v_bus_stations) {

                                                    $v_bus_stations = (object)$v_bus_stations;

                                                    $station_obj = new \stdClass;
                                                    $station_obj->route_id = (int)$v_bus_stations->route_id;
                                                    $station_obj->id = (int)$v_bus_stations->id;
                                                    $station_obj->name = $v_bus_stations->name;
                                                    $station_obj->station_order = (int) $v_bus_stations->station_order;
                                                    $station_obj->direction =  $v_bus_stations->direction;
                                                    $station_obj->station_relative =  $v_bus_stations->station_relative;
                                                    $station_obj->lat = $v_bus_stations->position->getLat();
                                                    $station_obj->lng = $v_bus_stations->position->getLng();
                                                    $tmp_station_data_save[] = $station_obj;
                                                }
                                            }
                                        }

                                    }

                                    $groups = collect($tmp_station_data_save)->groupBy('route_id')->toArray();
                                    $station_data_save = [];
                                    foreach ($groups as $k_groups => $val_groups) {
                                        if(count($val_groups) == 2){
                                            foreach ($val_groups as $k1 => $v1) {
                                                $station_data_save[] = $v1;
                                            }
                                        }
                                    }

                                    usort($station_data_save, array($this, "cmp_route_id"));
                                    $membership->station_data = json_encode($station_data_save, JSON_UNESCAPED_UNICODE);
                                    $membership->gr_bus_station_id = $gr_bus_station_id;
                                }
                            }
                        }

                    //type card prepaid
                    }else{

                        $membership->charge_limit_prepaid = (isset($data['charge_limit_prepaid']) && $data['charge_limit_prepaid'] != null) ? (int)$data['charge_limit_prepaid'] :  null;
                        $membership->gr_bus_station_id = null;
                        $membership->station_data = null;

                        //check mbs ap dung chang
                        if($gr_bus_station_id > 0 && $gr_bus_station_id != null){

                          $group_bus_station = $this->bus_stations->getGroupBusStationById($gr_bus_station_id);

                          if(!empty($group_bus_station)){

                              $route_bus_station = RoutesBusStation::whereIn('bus_station_id', json_decode($group_bus_station->bus_stations))->with('busStation')->get();
                              if(count($route_bus_station) > 0){

                                  $station_result = [];

                                  foreach($route_bus_station as $key => $value){

                                      $station_obj = new \stdClass;
                                      $station_obj->route_id = (int)$value->route_id;
                                      $station_obj->id = (int)$value['busStation']->id;
                                      $station_obj->name = $value['busStation']->name;
                                      $station_obj->station_order = (int) $value['busStation']->station_order;
                                      $station_obj->direction =  $value['busStation']->direction;
                                      $station_obj->station_relative =  $value['busStation']->station_relative;
                                      $station_obj->lat = $value['busStation']->position->getLat();
                                      $station_obj->lng = $value['busStation']->position->getLng();

                                      $station_result[] =  $station_obj;
                                  }

                                  $membership->station_data = json_encode($station_result, JSON_UNESCAPED_UNICODE) ?? null;
                                  $membership->gr_bus_station_id = $gr_bus_station_id;
                              }
                          }
                      }
                    }

                    $membership->membershiptype_id = (int)$data['membershiptype_id'];
                    $membership->expiration_date = $data['expiration_date'];
                    $membership->start_expiration_date = $data['start_expiration_date'];
                    $membership->duration = $data['duration'];
                    $membership->address = $data['address'] ?? null;
                    $membership->birthday = $data['birthday'] ? date("Y-m-d", strtotime($data['birthday'])) : null;
                    $membership->email = $data['email'] ?? null;
                    $membership->fullname = $data['fullname'] ?? null;
                    $membership->phone = $data['phone'] ?? null;
                    $membership->cmnd = $data['cmnd'] ?? null;
                    $membership->gender = (int)$data['gender'] ?? null;
                    $membership->charge_count = 0;
                    if($data['avatar']) $membership->avatar = $this->saveImgBase64($data['avatar'], $rfidcard->barcode);

                    if ($membership->save()) return $membership;

                    return response("Update MBS faild", 404);
                }
            }
        }

        // extend card
        if ($data['type_edit'] == 2){

            if ($data['cmnd']) {
                $check_exists = Membership::where('cmnd',$data['cmnd'])->where('company_id',$data['company_id'])->where('id','!=',$data['id'])->first();
                if (!empty($check_exists)) return response('CMND is exists', 404);
            }

            if ($data['phone']) {
                $check_exists = Membership::where('phone',$data['phone'])->where('company_id',$data['company_id'])->where('id','!=',$data['id'])->first();
                if (!empty($check_exists)) return response('Phone is exists', 404);
            }

            $rfidcard = $this->rfidcards->getRfidCardByRfid($data['rfid']);
            if(!empty( $rfidcard)){

                $membership = $this->getMembership($data['id']);
                $membership->membershiptype_id = $data['membershiptype_id'];
                $membership->duration = $data['duration'];

                if($membership->membershipType['code'] == 1){

                    if((int)$membership->ticket_price_id != (int)$data['ticket_price_id']){
                        $membership->actived = -2;
                        $ticket_price = $this->ticket_prices->getPriceById((int)$data['ticket_price_id']);
                        if(!empty($ticket_price)){
                            if(!empty($ticket_price->charge_limit)) $membership->charge_limit = 0;
                        }
                    }
                    $membership->ticket_price_id = $data['ticket_price_id'] ??  null;
                    $membership->gr_bus_station_id = null;
                    $membership->station_data = null;

                    //check mbs ap dung chang
                    if($gr_bus_station_id > 0 && $gr_bus_station_id != null){

                        $group_bus_station = $this->bus_stations->getGroupBusStationById($gr_bus_station_id);

                        if(!empty($group_bus_station)){

                            $route_bus_station = RoutesBusStation::whereIn('bus_station_id', json_decode($group_bus_station->bus_stations))->with('busStation')->get();

                            if(count($route_bus_station) > 0){

                                $station_result = [];

                                foreach($route_bus_station as $value){

                                    $station_obj = new \stdClass;
                                    $station_obj->route_id = (int)$value->route_id;
                                    $station_obj->id = (int)$value['busStation']->id;
                                    $station_obj->name = $value['busStation']->name;
                                    $station_obj->station_order = (int) $value['busStation']->station_order;
                                    $station_obj->direction =  $value['busStation']->direction;
                                    $station_obj->station_relative =  $value['busStation']->station_relative;
                                    $station_obj->lat = $value['busStation']->position->getLat();
                                    $station_obj->lng = $value['busStation']->position->getLng();
                                    $station_result[] =  $station_obj;
                                }

                                $membership->station_data = json_encode($station_result, JSON_UNESCAPED_UNICODE) ?? null;
                                $membership->gr_bus_station_id = $gr_bus_station_id;
                            }
                        }
                    }

                    //check mbs ap dung cho tuyen co dinh
                    if($gr_bus_station_id == 0){

                        if(!empty($station_data)){

                            if(count($station_data) > 0){

                                $tmp_station_data_save = [];

                                foreach ($station_data as $v_station_data) {

                                    $v_station_data = (object)$v_station_data;

                                    $station_obj = new \stdClass;
                                    $station_obj->route_id = (int)$v_station_data->route_id;
                                    $station_obj->id = (int)$v_station_data->id;
                                    $station_obj->name = $v_station_data->name;
                                    $station_obj->station_order = (int) $v_station_data->station_order;
                                    $station_obj->direction =  $v_station_data->direction;
                                    $station_obj->lat = $v_station_data->lat;
                                    $station_obj->lng = $v_station_data->lng;
                                    $station_obj->station_relative =  $v_station_data->station_relative;
                                    $tmp_station_data_save[] = $station_obj;

                                    if($v_station_data->station_relative != null){

                                        $bus_stations = $this->bus_stations->getBusStationByNotIDAndInArray(json_decode($v_station_data->station_relative), $v_station_data->id);

                                        if(count($bus_stations)){

                                            foreach ($bus_stations as $v_bus_stations) {

                                                $v_bus_stations = (object)$v_bus_stations;

                                                $station_obj = new \stdClass;
                                                $station_obj->route_id = (int)$v_bus_stations->route_id;
                                                $station_obj->id = (int)$v_bus_stations->id;
                                                $station_obj->name = $v_bus_stations->name;
                                                $station_obj->station_order = (int) $v_bus_stations->station_order;
                                                $station_obj->direction =  $v_bus_stations->direction;
                                                $station_obj->station_relative =  $v_bus_stations->station_relative;
                                                $station_obj->lat = $v_bus_stations->position->getLat();
                                                $station_obj->lng = $v_bus_stations->position->getLng();
                                                $tmp_station_data_save[] = $station_obj;
                                            }
                                        }
                                    }

                                }

                                $groups = collect($tmp_station_data_save)->groupBy('route_id')->toArray();
                                $station_data_save = [];
                                foreach ($groups as $k_groups => $val_groups) {
                                    if(count($val_groups) == 2){
                                        foreach ($val_groups as $k1 => $v1) {
                                            $station_data_save[] = $v1;
                                        }
                                    }
                                }

                                usort($station_data_save, array($this, "cmp_route_id"));
                                $membership->station_data = json_encode($station_data_save, JSON_UNESCAPED_UNICODE);
                                $membership->gr_bus_station_id = $gr_bus_station_id;
                            }
                        }
                    }

                }else{

                    $membership->start_expiration_date = $data['start_expiration_date'];
                    $membership->expiration_date = $data['expiration_date'];
                    $membership->charge_limit_prepaid = (isset($data['charge_limit_prepaid']) && $data['charge_limit_prepaid'] != null) ? (int)$data['charge_limit_prepaid'] :  null;

                    //check mbs ap dung chang
                    if($gr_bus_station_id > 0 && $gr_bus_station_id != null){

                        $group_bus_station = $this->bus_stations->getGroupBusStationById($gr_bus_station_id);

                        if(!empty($group_bus_station)){

                            $route_bus_station = RoutesBusStation::whereIn('bus_station_id', json_decode($group_bus_station->bus_stations))->with('busStation')->get();

                            if(count($route_bus_station) > 0){

                                $station_result = [];

                                foreach($route_bus_station as $value){

                                    $station_obj = new \stdClass;
                                    $station_obj->route_id = (int)$value->route_id;
                                    $station_obj->id = (int)$value['busStation']->id;
                                    $station_obj->name = $value['busStation']->name;
                                    $station_obj->station_order = (int) $value['busStation']->station_order;
                                    $station_obj->direction =  $value['busStation']->direction;
                                    $station_obj->station_relative =  $value['busStation']->station_relative;
                                    $station_obj->lat = $value['busStation']->position->getLat();
                                    $station_obj->lng = $value['busStation']->position->getLng();
                                    $station_result[] =  $station_obj;
                                }

                                $membership->station_data = json_encode($station_result, JSON_UNESCAPED_UNICODE) ?? null;
                                $membership->gr_bus_station_id = $gr_bus_station_id;
                            }
                        }
                    }

                    if($gr_bus_station_id == null){
                        $membership->station_data = null;
                        $membership->gr_bus_station_id = null;
                    }
                }

                $membership->address = $data['address'] ?? null;
                $membership->email = $data['email'] ?? null;
                $membership->fullname = $data['fullname'] ?? null;
                $membership->phone = $data['phone'] ?? null;
                $membership->cmnd = $data['cmnd'] ?? null;
                $membership->gender = (int)$data['gender'] ?? null;
                $membership->birthday = $data['birthday'] ? date("Y-m-d", strtotime($data['birthday'])) : null;
                $img_tmp = $membership->avatar;
                if($data['avatar']){
                    $membership->avatar = $this->saveImgBase64($data['avatar'], $rfidcard->barcode);
                    if($membership->avatar) $this->removeImageBase64($img_tmp);
                }

                if ($membership->save()){

                    //delete notifies
                    $notify = Notify::join('notify_types', 'notify_types.id', '=', 'notifies.notify_type_id')
                                ->where('notify_types.key', '=', 'mbs_expired')
                                ->where('notifies.subject_id', '=', $membership->id)
                                ->delete();

                    return $membership;
                }

                return response("Update MBS faild", 404);
            }
        }
    }

    public function getMembershipAct($data, $company_id){

        $limit = $data['limit'];
        if (empty($limit) && $limit < 0)
            $limit = 10;

        return Membership::where('memberships.company_id', $company_id)
                        ->where('memberships.actived', '!=', 0)
                        ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                        ->select(
                            'memberships.*',
                            DB::raw('
                            (CASE
                                WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                                THEN  PERIOD_DIFF(
                                    CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                    CAST("'.date('Ym').'" as unsigned)
                                )
                                ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                            END) AS duration_date') ,
                            'group_bus_stations.name as group_busstion_name'
                        )
                        ->with('membershipType', 'rfidcard')
                        ->paginate($limit);
    }

    public function viewDetailTransaction($data){

        $limit = $data['limit'];
        if (empty($limit) && $limit < 0) $limit = 10;

        $rfid = $data['rfid'];
        $transaction_type = $data['transaction_type'];
        $company_id = $data['company_id'];

        return $this->transaction->getTransactionByRfid($rfid, $transaction_type, $company_id, $limit);
    }

    public function getMembershipByRfidcardId($rfidcard_id){

        $membership =  Membership::where('rfidcard_id', $rfidcard_id)
                        ->with('membershipType')
                        ->with('rfidcard')
                        ->with('ticketPrice')
                        ->first();

        if($membership){
            if(!empty($membership->station_data)){

                $station_data = [];
                $subject_station = json_decode($membership->station_data);
                $subject_station = collect($subject_station)->groupBy('route_id')->toArray();
                foreach ($subject_station as $key => $value) {
                    $station_obj = new \stdClass;
                    $station_obj->route_id = $key;
                    $station_obj->stations = [];
                    usort($value, array($this, "cmp_station_order"));
                    foreach ($value as $v) {
                        $obj = new \stdClass;
                        $obj->id = (int)$v->id;
                        $obj->name = $v->name;
                        $obj->station_order = (int)$v->station_order;
                        $obj->lat = $v->lat;
                        $obj->lng = $v->lng;
                        $station_obj->stations[] = $obj;
                    }
                    $station_data[] =  $station_obj;
                }
                $membership['station_data'] = json_encode($station_data,JSON_UNESCAPED_UNICODE);
            }

            if($membership['membershipType']->code == 1){

                if(isset($membership['ticketPrice'])){

                    $ticket_type = $this->ticket_types->getTicketTypeById($membership['ticketPrice']->ticket_type_id);
                    $membership['ticketPrice']->order_code = $ticket_type ? $ticket_type->order_code : '';
                    $membership['ticketPrice']->sign = $ticket_type ? $ticket_type->sign : '';
                }
            }

            return $membership;
        }
        return null;
    }

    public function getMembershipByRfidcardIdForApp($rfidcard_id){
        $membership =  Membership::where('rfidcard_id', $rfidcard_id)
                        ->with('membershipType')
                        ->with('rfidcard')
                        ->with('ticketPrice')
                        ->first();
        if($membership) return $membership;

        return null;
    }

    public function getMembershipByRfidcardIdAndActivated($rfidcard_id, $actived){

        return Membership::where('rfidcard_id', $rfidcard_id)
                        ->where('actived', $actived)
                        ->with('membershipType')->first();
    }

    public function updateBalance($data, $type){

        $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($data['rfid']);

        if($rfidcard) {

            $memberShip = $this->getMembershipByRfidcardIdAndActivated( $rfidcard->id, 1);

            if($memberShip){
                if ($type == 'deposit') {
                    $memberShip->balance += $data['amount'];
                }

                if ($type == 'charge' || $type == 'charge_taxi' || $type == 'charge_goods') {
                    if($memberShip->balance < $data['amount']){
                        return  response('Balance is not enough', 404);
                    }
                    //update balance
                    $memberShip->balance -=  $data['amount'];
                }

                if ($memberShip->save()) {
                    return $memberShip;
                }
                return  response('Update balance failed', 404);
            }
            return  response('Membership not found', 404);
        }
    }

    public function updateBackupBalance($data, $type){

        $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($data['rfid']);

        if($rfidcard) {

            $memberShip = $this->getMembershipByRfidcardIdAndActivated( $rfidcard->id, 1);

            if($memberShip){

                if ($type == 'deposit')  $memberShip->balance -= $data['amount'];

                if ($type == 'charge' || $type == 'charge_taxi' || $type == 'charge_goods')  $memberShip->balance +=  $data['amount'];

                if ($memberShip->save())  return $memberShip;

                return  response('Update balance failed', 404);
            }
            return  response('Membership not found', 404);
        }
    }

    public function updateExpirationDateByCardMonth($data, $expiration_date){

        $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($data['rfid']);

        if($rfidcard) {

            $memberShip = $this->getMembershipByRfidcardIdAndActivated($rfidcard->id, 1);

            if($memberShip){

                if ($data['type'] == 'deposit_month') {

                    $memberShip->expiration_date = $expiration_date;
                    if ($expiration_date.'-01' < date('Y-m-01') )  $memberShip->actived = -2;
                }

                if ($memberShip->save()) return $memberShip;

                return  response('Update expiration_date failed', 404);
            }
            return  response('Membership not found', 404);
        }
    }

    public function updateChargeLimitByValue($data, $type, $vl_charge){

        $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($data['rfid']);

        if($rfidcard) {

            $memberShip =  Membership::where('rfidcard_id', $rfidcard->id)
                        ->with('membershipType')
                        ->with('rfidcard')
                        ->with('ticketPrice')
                        ->first();

            if($memberShip){

                if ($type == 'charge_month') {

                    if(!empty($memberShip->ticket_price_id)){

                        $ticket_price = $this->ticket_prices->getPriceById($memberShip->ticket_price_id);

                        if(!empty($ticket_price)){

                            if(!empty($ticket_price->charge_limit)){

                                if($ticket_price->charge_limit <=  $memberShip->charge_limit){
                                    return response('Number of scan the card no longer', 404);
                                }

                                $memberShip->charge_limit +=  ($vl_charge);

                                if ($memberShip->save()) {
                                    return $memberShip;
                                }
                                return  response('Update charge limit for membership failed', 404);
                            }
                        }
                        return  response('Price card month not found', 404);
                    }
                }
            }
        }
    }

    public function updateChargeCountByValue($data, $type, $vl_charge){

        $rfidcard = $this->rfidcards->rfidCardByRfidNotJoin($data['rfid']);

        if($rfidcard) {

            $memberShip = Membership::where('rfidcard_id', $rfidcard->id)
                        ->with('membershipType')
                        ->with('rfidcard')
                        ->with('ticketPrice')
                        ->first();

            if($memberShip){

                if ($type == 'charge' || $type == 'charge_taxi' || $type == 'charge_month' || $type = "charge_free") {
                    $memberShip->charge_count += ($vl_charge);
                }

                if ($memberShip->save()) {
                    return $memberShip;
                }
                return  response('Update charge count failed', 404);
            }
        }
    }

    public function getMembershipByInputAndBySearch($data){

        $company_id = $data['company_id'];
        $key_input = $data['key_input'];
        $key_search = $data['key_search'];
        $key_case = $data['key_case'];
        $actived = [];
        if ($key_case == 'activated') $actived = [1, -2, -1];
        if ($key_case == 'not_activated') $actived = [0];

        $membership = [];
        switch ($key_search) {
            case 'name_card':

                $membership = Membership::where('memberships.company_id', $company_id)
                    ->where('memberships.fullname', 'like', '%' . $key_input . '%')
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("'.date('Ym').'" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                        END) AS duration_date') ,
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'phone':
                $membership = Membership::where('memberships.company_id', $company_id)
                    ->where('memberships.phone', 'like', '%' . $key_input . '%')
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("'.date('Ym').'" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                        END) AS duration_date') ,
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'seri':
                $membership = Membership::where('memberships.company_id', $company_id)
                    ->where('memberships.phone', 'like', '%' . $key_input . '%')
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("'.date('Ym').'" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                        END) AS duration_date') ,
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'barcode':
                $rfidcard_id_arr =  $this->rfidcards->getRfidCardByLikeBarcode($key_input, 'id');
                $membership = Membership::where('memberships.company_id', $company_id)
                    ->whereIn('memberships.rfidcard_id', $rfidcard_id_arr)
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("'.date('Ym').'" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                        END) AS duration_date') ,
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'rfid':
                $rfidcard_id_arr =  $this->rfidcards->getRfidCardByLikeRfid($key_input, 'id');
                $membership = Membership::where('memberships.company_id', $company_id)
                    ->whereIn('memberships.rfidcard_id', $rfidcard_id_arr)
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("'.date('Ym').'" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                        END) AS duration_date') ,
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'type':
                $membership_type_id_arr = $this->membership_types->getMembershiptypeIdByKeyWord($key_input, $company_id);
                $membership = Membership::where('memberships.company_id', $company_id)
                    ->whereIn('memberships.membershiptype_id', $membership_type_id_arr)
                    ->whereIn('memberships.actived', $actived)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("' . date('Ym') . '" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"' . date('Y-m-d') . '")
                        END) AS duration_date'),
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();
                break;

            case 'expiration':

                $type = null;
                $membership_type_id_arr = [];
                if($key_input  == 'prepaid'){
                    $type = 0;
                    $membership_type_id_arr = $this->membership_types->getMembershiptypeIdByType($type, $company_id);
                }elseif($key_input  == 'month') {
                    $type = 1;
                    $membership_type_id_arr = $this->membership_types->getMembershiptypeIdByType($type, $company_id);
                }

                if(($type !== null) && count($membership_type_id_arr) > 0){

                    if($type == 1){
                        $membership = Membership::where('memberships.company_id', $company_id)
                            ->whereIn('memberships.actived', $actived)
                            ->whereIn('memberships.membershiptype_id', $membership_type_id_arr)
                            ->whereRaw('
                                (CASE
                                    WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                                    THEN  PERIOD_DIFF(
                                        CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                        CAST("' . date('Ym') . '" as unsigned)
                                    )
                                END) < 0
                            ')
                            ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                            ->select(
                                'memberships.*',
                                DB::raw('
                                (CASE
                                    WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                                    THEN  PERIOD_DIFF(
                                        CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                        CAST("' . date('Ym') . '" as unsigned)
                                    )
                                END) AS duration_date') ,
                                'group_bus_stations.name as group_busstion_name'
                            )
                            ->with('membershipType', 'rfidcard')
                            ->get()
                            ->toArray();
                    }elseif($type == 0){

                        $membership = Membership::where('memberships.company_id', $company_id)
                            ->whereIn('memberships.actived', $actived)
                            ->whereIn('memberships.membershiptype_id', $membership_type_id_arr)
                            ->whereRaw('
                                (CASE
                                    WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) > 7
                                    THEN  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                END) <= 7
                            ')
                            ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                            ->select(
                                'memberships.*',
                                DB::raw('
                                (CASE
                                    WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) > 7
                                    THEN  DATEDIFF(memberships.expiration_date,"'.date('Y-m-d').'")
                                END) AS duration_date') ,
                                'group_bus_stations.name as group_busstion_name'
                            )
                            ->with('membershipType', 'rfidcard')
                            ->get()
                            ->toArray();
                    }
                }

                break;

            case 'id':

                $membership = Membership::where('memberships.id', '=', (int)$key_input)
                    ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                    ->select(
                        'memberships.*',
                        DB::raw('
                        (CASE
                            WHEN CHAR_LENGTH(CONVERT(memberships.expiration_date USING utf8)) = 7
                            THEN  PERIOD_DIFF(
                                CAST(REPLACE(memberships.expiration_date, "-", "") as unsigned),
                                CAST("' . date('Ym') . '" as unsigned)
                            )
                            ELSE  DATEDIFF(memberships.expiration_date,"' . date('Y-m-d') . '")
                        END) AS duration_date'),
                        'group_bus_stations.name as group_busstion_name'
                    )
                    ->with('membershipType', 'rfidcard')
                    ->get()
                    ->toArray();

                break;
        }

        return $membership;
    }

    public function getByMembershipId($id) {
        return Membership::where('id', $id)
                    ->first();
    }

    public function searchMembershipsDetail($data){

        return $this->transaction->searchMembershipsDetailTransactionByRfid($data);
    }

    public function UpdateRfidMembershipById($data){

        $id = $data['id'] ?? null;
        $rfid = $data['rfid'] ?? null;
        // $barcode = $data['barcode'] ?? null;
        // $type_choose = $data['type_choose'] ?? null;

        if(empty($id) || empty($rfid)){
            return response('Data repuest faile', 400);
        }

        //get mbs by id
        $membership = $this->getByMembershipId($id);
        if(empty($membership)){ return response('Membership not found', 404); }

        $check_rfidcard =  $this->rfidcards->getRfidCardByRfidAndTargetIdAndUsageType($rfid, $id, 'membership');
        if($check_rfidcard){ return $membership; }

        //get nfc new
        $rfidcard_new = $this->rfidcards->getRfidCardByRfid($rfid);

        if(!empty($rfidcard_new)){

            if(empty($rfidcard_new->target_id) && empty($rfidcard_new->usage_type)){

                $rfidcard_new->target_id = $membership->id;
                $rfidcard_new->usage_type = 'membership';

                if(empty($rfidcard_new->barcode)){

                    $digits = 13;
                    $barcode_flag = false;
                    $barcode = '';
                    while(!$barcode_flag) {
                        $random = rand(0, pow(10, 10)-1);
                        $barcode = strtoupper(str_pad($random, $digits, 'MBS', STR_PAD_LEFT));
                        if (!$this->rfidcards->getExistsByBarcode($barcode)) { $barcode_flag = true; }
                    }
                    $rfidcard_new->barcode = $barcode;
                }

                //update rfidcard new
                if($rfidcard_new->save()){

                    //get and remove nfc old
                    $rfidcard_old = $this->rfidcards->getRfidCardById($membership->rfidcard_id);
                    $tmp_rfid_old = '';

                    if($rfidcard_old) {

                        $tmp_rfid_old = $rfidcard_old->rfid;
                        $rfidcard_old->delete();
                    }

                    //set and save MBS
                    $membership->rfidcard_id = $rfidcard_new->id;

                    if($membership->save()){

                        //set and save Transaction
                        if($tmp_rfid_old){

                            $transactions = $this->transaction->updateTransactionFroRfidByRfid($tmp_rfid_old, $rfidcard_new->rfid);

                            return $membership;
                        }
                    }
                }
            }else{
                return response('This RFID code is already in use. Please check again', 404);
            }
        }else{

            $rfidcard_new = new RfidCard();
            $rfidcard_new->rfid = $rfid;
            $rfidcard_new->usage_type = "membership";
            $rfidcard_new->target_id = $membership->id;

            $digits = 13;
            $barcode_flag = false;
            $barcode = '';
            while(!$barcode_flag) {
                $random = rand(0, pow(10, 10)-1);
                $barcode = strtoupper(str_pad($random, $digits, 'MBS', STR_PAD_LEFT));
                if (!$this->rfidcards->getExistsByBarcode($barcode)) { $barcode_flag = true; }
            }
            $rfidcard_new->barcode = $barcode;

            if($rfidcard_new->save()){

                //get and remove nfc old
                $rfidcard_old = $this->rfidcards->getRfidCardById($membership->rfidcard_id);
                $tmp_rfid_old = '';

                if($rfidcard_old) {

                    $tmp_rfid_old = $rfidcard_old->rfid;
                    $rfidcard_old->delete();
                }

                //set and save MBS
                $membership->rfidcard_id = $rfidcard_new->id;

                if($membership->save()){

                    //set and save Transaction
                    if($tmp_rfid_old){

                        $transactions = $this->transaction->updateTransactionFroRfidByRfid($tmp_rfid_old, $rfidcard_new->rfid);

                        return $membership;
                    }
                }
            }
        }

        // //get nfc old
        // $rfidcard_old = $this->rfidcards->getRfidCardById($membership->rfidcard_id);

        // if(!empty($rfidcard_new) && !empty($rfidcard_old)){
        //     if($rfidcard_old->id == $rfidcard_new->id ){ return $membership; }
        // }

        // if(!empty($rfidcard_new)){
        //     if(empty($rfidcard_new->usage_type) && empty($rfidcard_new->target_id)){
        //         //update nfc new
        //         $rfidcard_new->usage_type = 'membership';
        //         $rfidcard_new->target_id = $membership->id;
        //         if($rfidcard_new->save()){
        //             //delete nfc old
        //             $membership->rfidcard_id = $rfidcard_new->id;
        //             if($membership->save()){
        //                 $transactions = $this->transaction->updateTransactionFroRfidByRfid($rfidcard_old->rfid, $rfidcard_new->rfid);
        //                 if($rfidcard_old->delete()){
        //                     return $membership;
        //                 }
        //             }
        //         }
        //     }else{
        //         return response('Card used for other subjects', 404);
        //     }
        // }
        // else{

        //     $rfid_old = $rfidcard_old->rfid;
        //     $rfidcard_old->rfid = $rfid;
        //     if($type_choose == 2){ $rfidcard_old->barcode = $barcode;}
        //     if($rfidcard_old->save()){
        //         $transactions = $this->transaction->updateTransactionFroRfidByRfid($rfid_old, $rfid);
        //         return $membership;
        //     }
        // }

        // $membership = $this->getByMembershipId($id);

        // if(!empty($membership)){

        //     if(!empty($membership->rfidcard_id) && !empty($rfid)){

        //         $rfidcard = $this->rfidcards->getRfidCardById($membership->rfidcard_id);
        //         $rfidcard_where = $this->rfidcards->getDataByBarcode($barcode);

        //         if(!empty($rfidcard) && !empty($rfidcard_where)){

        //             if($rfidcard->id == $rfidcard_where->id){

        //                 $rfidcard_exists =  $this->rfidcards->getRfidCardByRfid($rfid);

        //                 if(!empty($rfidcard_exists)){
        //                     if($rfidcard_exists->rfid != $rfidcard->rfid) {return response('Rfid card code is exists', 404); }
        //                 }

        //                 $rfidcard->rfid = $rfid;
        //                 if( $rfidcard->save()){

        //                     $transactions = $this->transaction->updateTransactionFroRfidByRfid($data['rfid']);
        //                     return $membership;
        //                 }
        //                 return response('Update faile', 404);
        //             }
        //         } return  response('Rfid card not found', 404);
        //     }
        // } return  response('Membership not found', 404);
    }

    public function UpdateActivedMembershipById($data){

        $id = (int)$data['id'];
        $actived = (int)$data['actived'];

        $membership = $this->getByMembershipId($id);

        if(!empty($membership)){

            $membership->actived = $actived;

            if($membership->save()){

                return $membership;
            }
            return response('Update faile', 404);
        }
        return  response('Membership not found', 404);
    }

    public function updateAutoMembershipForProperties(){

        $date_current = date("Y-m-d");
        $month_current = date("m");
        $count_mbs = Membership::whereNotNull('charge_limit')
                        ->where('updated_at', '<', $date_current )
                        ->where('updated_at', 'not like', "%-$month_current-%" )
                        ->count();
        if($count_mbs > 0){

            $log_re1 = Membership::whereNotNull('charge_limit')
                        ->where('updated_at', '<', $date_current )
                        ->where('updated_at', 'not like', "%-$month_current-%" )->get();

            $a = Membership::whereNotNull('charge_limit')
                        ->where('updated_at', '<', $date_current )
                        ->where('updated_at', 'not like', "%-$month_current-%" )
                        ->update(['charge_limit' => 0, 'updated_at' => date("Y-m-d H:m:s")]);

            $log_re2 = Membership::whereNotNull('charge_limit')
                        ->where('updated_at', '<', $date_current )
                        ->where('updated_at', 'not like', "%-$month_current-%" )->get();

            Log::info('$$$Log-update_auto: '."log_re1: ".json_encode($log_re1,JSON_UNESCAPED_UNICODE)." - log_re2: ".json_encode($log_re2,JSON_UNESCAPED_UNICODE));
            return response("OK", 200);
        }
    }

    public function list($company_id){
        return Membership::where('company_id', $company_id)
                    ->get();
    }
    //end fucntion for web


    //fucntion for Mechine
    public function getMembershipByRfidcardIdForMechine($rfidcard_id, $rfid){

        $membership =  Membership::where('rfidcard_id', $rfidcard_id)
                        ->with('membershipType')
                        ->with('rfidcard')
                        ->with('ticketPrice')
                        ->first();

        if($membership){
            if(!empty($membership->station_data)){

                $station_data = [];
                $subject_station = json_decode($membership->station_data);
                $subject_station = collect($subject_station)->groupBy('route_id')->toArray();
                foreach ($subject_station as $key => $value) {
                    $station_obj = new \stdClass;
                    $station_obj->route_id = $key;
                    $station_obj->stations = [];
                    usort($value, array($this, "cmp_station_order"));
                    foreach ($value as $v) {
                        $obj = new \stdClass;
                        $obj->id = (int)$v->id;
                        $obj->name = $v->name;
                        $obj->station_order = (int)$v->station_order;
                        $obj->lat = $v->lat;
                        $obj->lng = $v->lng;
                        $station_obj->stations[] = $obj;
                    }
                    $station_data[] =  $station_obj;
                }
                $membership['station_data'] = json_encode($station_data,JSON_UNESCAPED_UNICODE);
            }

            if($membership['membershipType']->code == 1){

                if(isset($membership['ticketPrice'])){

                    $ticket_type = $this->ticket_types->getTicketTypeById($membership['ticketPrice']->ticket_type_id);
                    $membership['ticketPrice']->order_code = $ticket_type ? $ticket_type->order_code : '';
                    $membership['ticketPrice']->sign = $ticket_type ? $ticket_type->sign : '';
                }
            }

            //check card prepaid
            if($membership['membershipType']->code == 0){
                if ($membership->charge_limit_prepaid != null) {
                    $count_transaction = $this->transaction->countTransactionByrfidAndCompanyIdInDay($rfid, $membership->company_id);
                    $check_limit = (int)$membership->charge_limit_prepaid - $count_transaction;
                    $membership->charge_limit_prepaid = $check_limit;
                }
            }
            return $membership;
        }
        return null;
    }
    //end function for Mechine


    //function for app --------------------------------
    public function getMembershipByBarcodeForApp($barcode){

        if(!empty($barcode)){

            $rfidcard = $this->rfidcards->getDataByBarcode($barcode);

            if($rfidcard){

                $membership = $this->getMembershipByRfidcardIdForApp($rfidcard->id);

                if( $membership){

                    if(!empty($membership->station_data)){

                        $station_data = [];
                        $subject_station = json_decode($membership->station_data);
                        $subject_station = collect($subject_station)->groupBy('route_id')->toArray();
                        foreach ($subject_station as $key => $value) {
                            $station_obj = new \stdClass;
                            $station_obj->route_id = $key;
                            $station_obj->stations = [];
                            usort($value, array($this, "cmp_station_order"));
                            foreach ($value as $v) {
                                $obj = new \stdClass;
                                $obj->id = (int)$v->id;
                                $obj->name = $v->name;
                                $obj->station_order = (int)$v->station_order;
                                $obj->lat = $v->lat;
                                $obj->lng = $v->lng;
                                $station_obj->stations[] = $obj;
                            }
                            $station_data[] =  $station_obj;
                        }
                        $membership->station_data = json_encode($station_data,JSON_UNESCAPED_UNICODE);
                    }

                    //check card prepaid
                    if($membership['membershipType']->code == 0){
                        if ($membership->charge_limit_prepaid != null) {
                            $count_transaction = $this->transaction->countTransactionByrfidAndCompanyIdInDay($membership->rfidcard->rfid, $membership->company_id);
                            $membership->count_transaction_in_day = $count_transaction;
                        }
                    }

                    unset($membership->created_at);
                    unset($membership->updated_at);
                    unset($membership->rfidcard_id);
                    unset($membership->membershiptype_id);
                    unset($membership->duration);
                    unset($membership->actived);
                    // unset($membership->avatar);
                    unset($membership->user_id);
                    unset($membership->membershipType->id);
                    unset($membership->membershipType->company_id);
                    unset($membership->membershipType->created_at);
                    unset($membership->membershipType->updated_at);
                    unset($membership->rfidcard->id);
                    unset($membership->rfidcard->created_at);
                    unset($membership->rfidcard->updated_at);
                    unset($membership->rfidcard->usage_type);
                    unset($membership->rfidcard->target_id);

                    return ['status' => true, 'message' => 'Thành công', 'data' => [$membership] ];
                }
                return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
            }
            return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
        }
    }

    public function getMembershipTransactionsByBarcodeForApp($barcode){

        if(!empty($barcode)){

            $rfidcard = $this->rfidcards->getDataByBarcode($barcode);

            if($rfidcard){

                $membership = $this->getMembershipByRfidcardId($rfidcard->id);

                if($membership){
                    $transaction_mbs = $this->transaction->getTransactionByBarcodeOfRfid($rfidcard->rfid, 10);
                    if($transaction_mbs)
                    {
                        return ['status' => true, 'message' => 'Thành công', 'data' => $transaction_mbs];
                    }
                    return ['status' => false, 'message' => 'Không tìm thấy dữ liệu', 'data' => []];
                }
                return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => []];
            }
            return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => []];
        }
    }

    public function editMembershipByBarcodeToForApp($token, $data){

        $partner_code = $data['partnerCode'] ?? null;
        $data_hash = $data['dataHash'] ?? null;

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){return ['status'=> false, 'message'=> 'Mã đối tác không tồn tại', 'data' => []]; }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;

        if(empty($app_key_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $data_de = $this->public_functions->deCrypto($data_hash, $app_key);
        $data_json = json_decode($data_de, true);

        if(empty($data_json)){ return ['status' => false, 'message' => 'Giải mã hash thất bại', 'data' => []]; }

        $fullname = $data_json['fullname'] ?? null;
        $address = $data_json['address'] ?? null;
        $phone = $data_json['phone'] ?? null;
        $email = $data_json['email'] ?? null;
        $birthday = $data_json['birthday'] ?? null;
        $barcode = $data_json['qrcode'] ?? null;
        $partner_code = $data_json['partnerCode'] ?? null;
        $timestamp = $data_json['timestamp'] ?? null;

        // $avatar_app = $data_json['avatarApp'] ?? null;

        if(empty($fullname) || empty($address) || empty($phone) || empty($email) || empty($birthday) || empty($barcode) || empty($partner_code) || empty($timestamp)){
            return ['status' => false, 'message' => 'Giải mã hash thất bại', 'data' => []];
        }

        //check API time out
        $check_minute = $this->public_functions->s_datediff('i', date('Y-m-d H:i:s', $timestamp), date('Y-m-d H:i:s'), true);
        if($check_minute > 10) return ['status' => false, 'message' => 'Hết thời gian gọi API', 'data' => []];

        if(!empty($barcode)){

            $rfidcard = $this->rfidcards->getDataByBarcode($barcode);

            if($rfidcard){

                $membership = $this->getMembershipByRfidcardId($rfidcard->id);

                if( $membership){

                    $membership->fullname = $fullname;
                    $membership->address = $address;
                    $membership->email = $email;
                    $membership->birthday = date("Y-m-d", strtotime($birthday));
                    $membership->phone = $phone;
                    $membership->updated_at = date("Y-m-d H:i:s", $timestamp);

                    // $img_tmp = $membership->avatar_app;
                    // if(!empty($avatar_app)){
                    //     $membership->avatar_app = $this->saveImgBase64($avatar_app, $barcode);
                    //     if(!empty($membership->avatar_app)){
                    //         $this->removeImageBase64($img_tmp);
                    //     }
                    // }

                    if($membership->save()){

                        unset($membership->created_at);
                        unset($membership->updated_at);
                        unset($membership->rfidcard_id);
                        unset($membership->membershiptype_id);
                        unset($membership->duration);
                        unset($membership->actived);
                        unset($membership->user_id);
                        // unset($membership->avatar);
                        unset($membership->membershipType->id);
                        unset($membership->membershipType->company_id);
                        unset($membership->membershipType->created_at);
                        unset($membership->membershipType->updated_at);
                        unset($membership->rfidcard->id);
                        unset($membership->rfidcard->created_at);
                        unset($membership->rfidcard->updated_at);
                        unset($membership->rfidcard->usage_type);
                        unset($membership->rfidcard->target_id);

                        return ['status' => true, 'message' => 'Thành công', 'data' => [$membership] ];
                    }
                }
                return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
            }
            return ['status' => false, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
        }
    }

    public function registerMembershipForApp($token, $data){

        $partner_code = $data['partnerCode'] ?? null;
        $data_hash = $data['dataHash'] ?? null;
        $avatar = $data['avatar'] ?? null;

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if(empty($partner)){return ['status'=> false, 'message'=> 'Mã đối tác không tồn tại', 'data' => []]; }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if(empty($token_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;

        if(empty($app_key_de)){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> false, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $data_de = $this->public_functions->deCrypto($data_hash, $app_key);
        $data_json = json_decode($data_de, true);

        if(empty($data_json)){ return ['status' => false, 'message' => 'Giải mã hash thất bại', 'data' => []]; }

        $fullname = $data_json['fullname'] ?? null;
        $gender = $data_json['gender'] ?? null;
        $birthday = $data_json['birthday'] ?? null;
        $phone = $data_json['phone'] ?? null;
        $cmnd = $data_json['cmnd'] ?? null;
        $email = $data_json['email'] ?? null;
        $address = $data_json['address'] ?? null;
        $company_id = $data_json['company_id'] ?? null;
        $membershiptype_id = $data_json['membershiptype_id'] ?? null;
        $timestamp = $data_json['timestamp'] ?? null;
        $accept = 0;

        //check API time out
        $check_minute = $this->public_functions->s_datediff('i', date('Y-m-d H:i:s', $timestamp), date('Y-m-d H:i:s'), true);
        if($check_minute > 10) return ['status' => false, 'message' => 'Hết thời gian gọi API', 'data' => []];

        if ((int)$company_id == 0) return ['status' => false, 'message' => "Không có dữ liệu ID của công ty", 'data' => []];
        if ($fullname == null)  return ['status' => false, 'message' => 'Vui lòng nhập họ tên', 'data' => []];
        if ($cmnd == null)  return ['status' => false, 'message' => 'Vui lòng nhập CMND', 'data' => []];
        if ($phone == null)  return ['status' => false, 'message' => 'Vui lòng nhập số điện thoại', 'data' => []];
        if (empty($timestamp) || $timestamp == null)  return ['status' => false, 'message' => 'Không có dữ liệu thời gian', 'data' => []];

        if ($cmnd != null) {

            $check_exists = Membership::where('cmnd', '=', $cmnd)->where('company_id',$company_id)->exists();
            if ($check_exists) return ['status' => false, 'message' => 'CMND đã được sử dụng', 'data' => []];

            $check_tmp_exists = MembershipTmp::where('cmnd', '=', $cmnd)->where('company_id',$company_id)->exists();
            if ($check_tmp_exists) return ['status' => false, 'message' => 'CMND đã được sử dụng', 'data' => []];
        }

        if ($phone != null) {

            $check_exists = Membership::where('phone', '=', $phone)->where('company_id',$company_id)->exists();
            if ($check_exists) return ['status' => false, 'message' => 'Số điện thoại đã được sử dụng', 'data' => []];

            $check_tmp_exists = MembershipTmp::where('phone', '=', $phone)->where('company_id',$company_id)->exists();
            if ($check_tmp_exists) return ['status' => false, 'message' => 'Số điện thoại đã được sử dụng', 'data' => []];
        }

        $membership_tmp = new MembershipTmp();
        $membership_tmp->fullname = $fullname;
        $membership_tmp->gender = $gender;
        $membership_tmp->birthday = $birthday ? date('Y-m-d', strtotime($birthday)) : null;
        $membership_tmp->phone = $phone;
        $membership_tmp->cmnd = $cmnd;
        $membership_tmp->email = $email;
        $membership_tmp->address = $address;
        $membership_tmp->company_id = $company_id;
        $membership_tmp->accept = $accept;
        $membership_tmp->created_at = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
        $membership_tmp->updated_at = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

        // if(empty($membershiptype_id)){
        $membership_type = $this->membership_types->getMembershipTypePrepaidByCompanyId($company_id);
        $membershiptype_id = $membership_type->id;
        // }

        if($avatar){
            $image = explode('.', $avatar->getClientOriginalName());
            $file_name = $company_id.'_'.time().'.'.$image[1];
            $path = public_path()."/img/avatar-membership/".$file_name;
            $img = Image::make($avatar->getRealPath());
            $img->resize(113.38582677, 151.18110236);
            $img->save($path);
            $membership_tmp->avatar = $file_name ?? null;
        }

        if($membership_tmp->save()) {

            //create notifies
            //"mbs_expired" - The het han
            //"mbs_register" - Dang ky the qua app
            $notify_type = NotifyType::where('key', '=', 'mbs_register')->first();
            $notify_return = null;
            if(!empty($notify_type)){

                $title = "";
                if($membership_tmp->fullname != null){
                    $title .= "<strong>".$membership_tmp->fullname."</strong> đã đăng ký thẻ qua ứng dụng điện thoại vào lúc <strong>".$membership_tmp->created_at."</strong>";
                }else{
                    $title .= "Có một khách hàng đã đăng ký thẻ qua ứng dụng điện thoại vào lúc <strong>".$membership_tmp->created_at."</strong>";
                }

                $notify = new Notify();
                $notify->title = $title;
                $notify->company_id = $membership_tmp->company_id;
                $notify->subject_id = $membership_tmp->id;
                $notify->notify_type_id = $notify_type->id;
                $notify->readed = 0;

                unset($membership_tmp->created_at);
                unset($membership_tmp->updated_at);
                $notify->subject_data = json_encode($membership_tmp, JSON_UNESCAPED_UNICODE);
                $notify->save();

                $notify_return = $notify;
                $notify_return->key = $notify_type->key;
                $notify_return->url_img = $notify_type->url_img;
                $notify_return->route_link = $notify_type->route_link;
            }

            if($notify_return != null){
                return ['status' => true, 'message' => 'Đăng ký thành công. Vui lòng chờ thông tin nhận thẻ', 'data' => [$notify_return] ];
            }else{
                return ['status' => true, 'message' => 'Đăng ký thành công. Vui lòng chờ thông tin nhận thẻ', 'data' => [$membership_tmp] ];
            }
        }

        return ['status' => false, 'message' => 'Đăng ký không thành công. Vui lòng thử lại', 'data' => [] ];
    }

    public function MembershipForApp($data,$token,$partner_code) {

    }
    //end function for app ----------------------------


    //function for momo
    public function getMembershipByMomo($token, $partner_code_momo, $barcode){

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code_momo);

        if(empty($partner)){return ['status'=> 404, 'message'=> 'Mã đối tác không tồn tại', 'data' => []]; }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code_momo));

        if(empty($token_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        $app_key_de = $token_de->appKey ?? null;

        if(empty($app_key_de)){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }
        if($app_key_de != $app_key){ return ['status'=> 404, 'message'=> 'Giải mã token thất bại', 'data' => []]; }

        if(!empty($barcode)){

            $rfidcard = $this->rfidcards->getDataByBarcode($barcode);

            if($rfidcard){

                $membership = $this->getMembershipByRfidcardId($rfidcard->id);

                if( $membership){

                    $data_result = [
                        'fullname' => $membership->fullname ?? "",
                        'phone' => $membership->phone ?? "",
                        'email' => $membership->email ?? "",
                        'birthday' => $membership->birthday ?? "",
                        'address' => $membership->address ?? "",
                        'balance' => $membership->balance ?? 0,
                        'qrcode' => $membership->rfidcard->barcode
                    ];

                    return ['status' => 200, 'message' => 'Thành công', 'data' => [$data_result] ];
                }
                return ['status' => 404, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
            }
            return ['status' => 404, 'message' => 'Thẻ thành viên không tồn tại', 'data' => [] ];
        }
    }
    //end function for momo


    //function orther
    public function cmp_station_order($a, $b){
        $a = (object) $a;
        $b = (object) $b;
        return ($a->station_order < $b->station_order) ? -1 : 1;
    }

    public function cmp_route_id($a, $b){
        $a = (object) $a;
        $b = (object) $b;
        return ($a->route_id < $b->route_id) ? -1 : 1;
    }

    // private function s_datediff( $str_interval, $dt_menor, $dt_maior, $relative=false){
    //
    //    if( is_string( $dt_menor)) $dt_menor = date_create( $dt_menor);
    //    if( is_string( $dt_maior)) $dt_maior = date_create( $dt_maior);
    //    $diff = date_diff( $dt_menor, $dt_maior, !$relative);
    //
    //    $number_day_in_month = 28;
    //    $month = (int)date('m');
    //    $year = (int)date('Y');
    //    if (in_array($month, [1,3,5,7,8,10,12])) $number_day_in_month = 31;
    //    if (in_array($month, [4,6,9,11])) $number_day_in_month = 30;
    //    if (in_array($month, [2])) if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) $number_day_in_month = 29;
    //
    //    $total = 0;
    //
    //    switch( $str_interval){
    //        case "y":
    //            $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
    //            break;
    //        case "m":
    //            $total= $diff->y * 12 + $diff->m + $diff->d/$number_day_in_month + $diff->h / 24;
    //            break;
    //        case "d":
    //            $total = $diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d + $diff->h/24 + $diff->i / 60;
    //            break;
    //        case "h":
    //            $total = ($diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d) * 24 + $diff->h + $diff->i/60;
    //            break;
    //        case "i":
    //            $total = (($diff->y * 365.25 + $diff->m * $number_day_in_month + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
    //            break;
    //        case "s":
    //            $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
    //            break;
    //       }
    //    if( $diff->invert) return -1 * $total;
    //    else return $total;
    // }

    public function saveImgBase64($data, $middle){

        if($data){
            $image = explode(';', $data);

            //get file name
            $img_name = explode('/',  $image[0] );
            $file_name = $middle.'_'.time().'.'.$img_name[1];

            $path = public_path()."/img/avatar-membership/".$file_name;

            $img = Image::make(file_get_contents($data));
            $img->resize(113.38582677, 151.18110236);
            if( $img->save($path)){
                return $file_name;
            }
        }
    }

    public function removeImageBase64($file_name){

        if($file_name){
            $path = public_path()."/img/avatar-membership/".$file_name;
            if(file_exists($path)){
                if(unlink($path)) return true;
            }
        }
    }
    //end function orther
}
