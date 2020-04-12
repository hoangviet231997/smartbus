<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogsService
{
    public function __construct(){}
    
    public function createActivityLog($data)
    {
        $company_id = (int)$data['company_id'];
        $user_id = (int)$data['user_id'];
        $user_down = $data['user_down'] ?? null;
        $action = $data['action'];
        $subject_type = $data['subject_type'];
        $subject_data = $data['subject_data'];

        $activity_log = new ActivityLog();
        $activity_log->company_id = $company_id;
        $activity_log->user_id = $user_id;
        $activity_log->user_down = $user_down ?? null;
        $activity_log->action = $action;
        $activity_log->subject_type = $subject_type;
        $activity_log->subject_data = $subject_data;

        if($activity_log->save()){
            return $activity_log; 
        }
        return response('Create Error', 404);
    }

    public function listActivityLog($data)
    {
        $limit = $data['limit'];
        if (empty($limit) && $limit < 0)
            $limit = 10;

        $pagination = ActivityLog::join('companies', 'companies.id', '=', 'activity_logs.company_id')
            ->join('users', 'users.id', '=', 'activity_logs.user_id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->orderBy('activity_logs.created_at', 'desc')
            ->select('activity_logs.*', 'roles.name as role_name', 'users.fullname', 'companies.name as company_name')
            ->paginate($limit)
            ->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function deleteActivityLog($id)
    {
        return ActivityLog::where('id', $id)->delete();
    }

    public function deleteActivityLogAll($data) {

        $id_array = json_decode($data['activity_arr'], true) ?? [];
        return ActivityLog::whereIn('id', $id_array)->delete();
    }

    public function getActivityLogById($id) {
        $activity_log = ActivityLog::where('id', $id)->first();
        if($activity_log){
            $activity_log->subject_data = json_decode($activity_log->subject_data, true);
            return $activity_log;
        }
    }

    public function searchActivityLog($data){

        $company_id = (isset($data['company_id'])) ? (int)$data['company_id'] : null;
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date'])) ?? null;
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date'])) ?? null;
        
        $query = ActivityLog::join('companies', 'companies.id', '=', 'activity_logs.company_id')
                        ->join('users', 'users.id', '=', 'activity_logs.user_id')
                        ->join('roles', 'roles.id', '=', 'users.role_id');

        if($company_id > 0) 
            $query = $query->where('activity_logs.company_id', '=', $company_id);

        if($from_date){
            $query = $query->where('activity_logs.created_at', '>=', $from_date)
                    ->where('activity_logs.created_at', '<=', $to_date);
        }

        $query = $query->orderBy('activity_logs.created_at', 'desc')
                    ->select('activity_logs.*', 'roles.name as role_name', 'users.fullname', 'companies.name as company_name')
                    ->get()
                    ->toArray();

        return $query;
    }

}
