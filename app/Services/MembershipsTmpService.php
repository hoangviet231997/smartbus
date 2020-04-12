<?php
namespace App\Services;

use App\Models\MembershipTmp;
use App\Models\Membership;
use App\Models\Notify;
use App\Services\RfidCardsService;
use App\Services\PublicFunctionService;

class MembershipsTmpService
{

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    public function __construct(
        RfidCardsService $rfidcards,
        PublicFunctionService $public_functions
    ){
        $this->rfidcards = $rfidcards;
        $this->public_functions = $public_functions;
    }

    public function getMembershipsTmp($data, $company_id){

        $limit = $data['limit'];
        if (empty($limit) && $limit < 0) $limit = 10;

        $pagination =  MembershipTmp::where('company_id', $company_id)
                        ->where('accept', '=', 0)
                        ->paginate($limit)
                        ->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function getListMembershipsTmpByInputAndByTypeSearch($data)
    {
        $company_id = $data['company_id'];
        $key_input = $data['key_input'];
        $style_search = $data['style_search'];

        $memberships_tmp = MembershipTmp::where('company_id', $company_id)
                ->orderBy('fullname', 'ASC');

        if ($style_search == 'name') $memberships_tmp->where('fullname', 'like', "%$key_input%");

        if ($style_search == 'cmnd') $memberships_tmp->where('cmnd', 'like', "%$key_input%");

        if ($style_search == 'phone') $memberships_tmp->where('phone', 'like', "%$key_input%");

        if ($style_search == 'id') $memberships_tmp->where('id', '=', (int)$key_input);

        return $memberships_tmp->get()->toArray();
    }

    public function deleteMembershipTmpById($membership_tmp_id)
    {
        $memberships_tmp = MembershipTmp::find($membership_tmp_id);

        if($memberships_tmp){

            if($memberships_tmp->delete()){

                //delete notifies
                $notify = Notify::join('notify_types', 'notify_types.id', '=', 'notifies.notify_type_id')
                    ->where('notify_types.key', '=', 'mbs_register')
                    ->where('notifies.subject_id', '=', $membership_tmp_id)
                    ->delete();

                return response('Delete OK', 200);
            }

            return response('Delete faild', 401);
        }
        return response('Data not found', 401);
    }

    public function getMembershipsTmpById($membership_tmp_id)
    {
        return MembershipTmp::find($membership_tmp_id);
    }

    public function acceptMembershipsTmp($data)
    {

        $station_data = $data['station_data'] ?? null;
        $gr_bus_station_id = (int)$data['gr_bus_station_id'] ?? null;

        $rfidcard = $this->rfidcards->getRfidCardByRfid($data['rfid']);

        if(empty($rfidcard))  return response('RFID is not in the system', 404);

        if($rfidcard->target_id != null || $rfidcard->usage_type != null ) return response("RFID has been used", 404);

        if ($data['cmnd']) {
            $check_exists = Membership::where('cmnd',$data['cmnd'])->where('company_id',$data['company_id'])->first();
            if (!empty($check_exists)) return response('CMND is exists', 404);
        }

        if ($data['phone']) {
            $check_exists = Membership::where('phone',$data['phone'])->where('company_id',$data['company_id'])->first();
            if (!empty($check_exists)) return response('Phone is exists', 404);
        }

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

        $membership = new Membership();
        $membership->fullname = $data['fullname'] ?? null;
        $membership->birthday = $data['birthday'] ? date("Y-m-d", strtotime($data['birthday'])) : null;
        $membership->email = $data['email'] ?? null;
        $membership->address = $data['address'] ?? null;
        $membership->phone = $data['phone'] ?? null;
        $membership->cmnd = $data['cmnd'] ?? null;
        $membership->gender = (int)$data['gender'] ?? null;
        $membership->membershiptype_id = (int)$data['membershiptype_id'];
        $membership->company_id = (int)$data['company_id'];
        $membership->user_id = (int)$data['user_id'];
        $membership->expiration_date = $data['expiration_date'];
        $membership->start_expiration_date = $data['start_expiration_date'];
        $membership->duration = $data['duration'];
        $membership->rfidcard_id = $rfidcard->id;
        $membership->charge_limit_prepaid = (isset($data['charge_limit_prepaid']) && $data['charge_limit_prepaid'] != null) ? (int)$data['charge_limit_prepaid'] :  null;
        $membership->charge_count = 0;
        $membership->actived = 1;
        $membership->balance = 0;
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

        //hendle image
        $memberships_tmp = MembershipTmp::find($data['id']);

        if($data['avatar']){

            $membership->avatar = $this->public_functions->saveImgBase64($data['avatar'], $rfidcard->barcode, '/img/avatar-membership/', 113.38582677, 151.18110236);

            if($memberships_tmp && $memberships_tmp->avatar != null) $this->public_functions->removeImageBase64($memberships_tmp->avatar,'/img/avatar-membership/');
        }else{

            if($memberships_tmp && $memberships_tmp->avatar != null){

                $path_parts = pathinfo($memberships_tmp->avatar);
                $membership->avatar = $rfidcard->barcode.'_'.time().'.'.$path_parts['extension'];
                rename( public_path().'/img/avatar-membership/'.$memberships_tmp->avatar, public_path().'/img/avatar-membership/'.$membership->avatar);
            }
        }

        if ($membership->save()){

            $rfidcard->target_id = $membership->id;
            $rfidcard->usage_type = 'membership';

            if($rfidcard->save()){
                $memberships_tmp_id = $memberships_tmp->id;
                if($memberships_tmp->delete()){

                    //delete notifies
                    $notify = Notify::join('notify_types', 'notify_types.id', '=', 'notifies.notify_type_id')
                                ->where('notify_types.key', '=', 'mbs_register')
                                ->where('notifies.subject_id', '=', $memberships_tmp_id)
                                ->delete();

                    return $membership;
                }
            }
        }
        return response("Accept MBS faild", 404);
    }
}
