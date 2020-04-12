<?php
namespace App\Services;

use App\Models\MembershipType;
use App\Services\PushLogsService;
use Log;

class MembershipTypeService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs)
    {
        $this->push_logs = $push_logs;
    }

    public function getById($id)
    {
        return MembershipType::where('id', $id)
                    ->first();
    }

    public function getByIdAndCompanyId($id,$company_id)
    {
        return MembershipType::where('id',$id)
                    ->where('company_id',$company_id)
                    ->first();
    }

    public function getDataByCompanyId($company_id){

        $membership_type = MembershipType::where('company_id',$company_id)->where('code', 0)->orderBy('deduction')->first();
        return $membership_type;
    }

    public function getList($company_id = null) {

        if($company_id){
            return MembershipType::where('company_id', $company_id)
                            ->orderBy('code')
                            ->orderBy('deduction')
                            ->with('company')
                            ->get();
        }
        return MembershipType::with('company')
                            ->orderBy('code')
                            ->orderBy('deduction')
                            ->get();
    }

    public function created($data){

        $company_id = (int)$data['company_id'];

        if(isset($data['name'])){

            $membershipType = new MembershipType();
            $membershipType->name = $data['name'];
            $membershipType->deduction = $data['deduction'];
            $membershipType->code = $data['code'];
            $membershipType->company_id = $data['company_id'];

            if($membershipType->save()){

                // $push_log_v = [];
                // $push_log_v['action'] = 'create';
                // $push_log_v['company_id'] =  $company_id;
                // $push_log_v['subject_id'] =  $membershipType->id ;
                // $push_log_v['subject_type'] = 'membership_type';
                // $push_log_v['subject_data'] =  $membershipType;
                // $this->push_logs->createPushLog($push_log_v);

                return $this->getById($membershipType['id']);
            }
        }
    }

    public function updated($data){

        $membershipType_id = (int)$data['id'];
        $company_id = (int)$data['company_id'];

        if(!$membershipType_id || !$company_id){
            return response('Membership Type Not failed', 404);
        }

        $membershipType = $this->getById($membershipType_id);
        if(empty($membershipType)){ return response('Membership Type Not Found', 404);}

        $membershipType->name = $data['name'];
        $membershipType->deduction = $data['deduction'];
        $membershipType->code = $data['code'];
        $membershipType->company_id = $data['company_id'];

        if($membershipType->save()){

            //update membership type
            $push_log_update = [];
            $push_log_update['action'] = 'update';
            $push_log_update['company_id'] =  $company_id;
            $push_log_update['subject_id'] =  $membershipType->id ;
            $push_log_update['subject_type'] = 'membership_type';
            $push_log_update['subject_data'] =  $membershipType;
            $this->push_logs->createPushLog($push_log_update);

            $wheres = [
                ['company_id', $company_id],
                ['action', 'update'],
                ['subject_type', 'rfidcard'],
                ['subject_data', 'like', '%membership_type_id\":'.$membershipType_id.'%'],
            ];
            $push_log_arr = $this->push_logs->getPushLogByOptions($wheres);

            foreach($push_log_arr as $value){

                //update push log rfidcard membership

                $subject_data = json_decode($value->subject_data, true);
                $subject_data['membership_type_deduction'] = $data['deduction'];
                $subject_data['membership_type_name'] = $data['name'];

                $push_log_v = [];
                $push_log_v['action'] = 'update';
                $push_log_v['company_id'] =  $company_id;
                $push_log_v['subject_id'] =  $value->subject_id ;
                $push_log_v['subject_type'] = 'rfidcard';
                $push_log_v['subject_data'] =  $subject_data;
                $this->push_logs->createPushLog($push_log_v);
            }

            return  $membershipType;
        }
    }

    public function getMembershiptypeIdByKeyWord($key_word, $company_id)
    {
        return MembershipType::where('name', 'like', "%$key_word%")
                        ->where('company_id', '=', $company_id)
                        ->pluck('id')
                        ->toArray();
    }

    public function getMembershiptypeIdByType($type, $company_id)
    {
        return MembershipType::where('code', $type)
                        ->where('company_id', $company_id)
                        ->pluck('id')
                        ->toArray();
    }

    // public function deleted($data){

    //     $membershipType_id = $data['id'];

    //     if(!$membershipType_id){
    //         return response('Membership Type Not failed', 404);
    //     }

    //     $membershipType = $this->getById($membershipType_id);
    //     if(empty($membershipType)){
    //         return response('Membership Type Not found', 404);
    //     }

    //     // get push_log
    //     $push_logs = $this->push_logs->getPushLogByOptions([
    //         ['subject_id', $membershipType_id],
    //         ['company_id', $membershipType->company_id],
    //         ['subject_type', 'membership_type_card']
    //     ]);
    //     if (count($push_logs) > 0) {
    //         $push_log = $push_logs[0];
    //         $this->push_logs->deletePushLog($push_log->id);

    //         $push_log = [];
    //         $push_log['action'] = 'delete';
    //         $push_log['company_id'] = $membershipType->company_id;
    //         $push_log['subject_id'] = $membershipType_id;
    //         $push_log['subject_type'] = 'membership_type_card';
    //         $push_log['subject_data'] = NULL;
    //         $this->push_logs->createPushLog($push_log);
    //     }

    //     if($this->memberships->deleteMembershipByMenbershipTypeId($membershipType_id)){

    //         if($membershipType->delete())return response('OK', 200);
    //     }

    //     return response('Delete Error', 404);

    // }

    public function getMembershipTypeByDeductionAndCompanyId($company_id)
    {
        return MembershipType::where('deduction', '>', 0)
                        ->where('company_id', $company_id)
                        ->orderBy('deduction')
                        ->get();
    }

    public function getMembershipTypePrepaidByCompanyId($company_id){

        $membership_type = MembershipType::where('company_id',$company_id)->where('code', 0)->orderBy('deduction')->first();
        return $membership_type;
    }
}
