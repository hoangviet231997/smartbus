<?php
namespace App\Services;

use App\Models\PushLogs;
use App\Services\DevicesService;
use App\Services\IssuedsService;
use App\Services\ShiftsService;
use Carbon\Carbon;

class PushLogsService
{

    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    public function __construct(IssuedsService $issueds)
    {
        $this->issueds = $issueds;
    }

    public function getAllPushLogs($device, $query) {
        
        $device_id = $device->id;

        // get issued by key
        $issued = $this->issueds->getIssuedByKey('device_id', $device_id);

        if (empty($issued)) {
            return response('Company not found', 404);
        }

        $company_id = $issued->company_id;
        $from = round($query['from']);
        $wheres = [
            ['company_id', $company_id],
            ['id', '>', $from]
        ];

        if(!empty($query['subject_type'])) array_push($wheres,['subject_type',$query['subject_type']]);
        if(!empty($query['action'])) array_push($wheres,['action',$query['action']]);

        $push_logs = $this->getPushLogByOptions($wheres);

        if (count($push_logs) <= 0) {
            return response('No data', 404);
        }
        //if($company_id == 8) $this->deletePushLogVoucher($company_id);
        $this->deleteVoucherByExpiration();
        return $push_logs;
    }

    public function deleteVoucherByExpiration() {

        $push_logs = $this->getPushLogByOptions([
                            ['subject_type', 'voucher'],
                            ['expiration', '!=', NULL],
                            ['expiration', '<=', Carbon::now()->subDays(3)]
                        ]);
        if (count($push_logs) > 0) {

            foreach ($push_logs as $push_log) {

                PushLogs::where('subject_id', $push_log->subject_id)->delete();
            }
        }
    }

    public function deletePushLogVoucher($company_id){

        $push_logs = $this->getPushLogByOptions([
                            ['subject_type', 'voucher'],
                            ['created_at', '<=', Carbon::now()->subDays(4)]
                        ]);

        if (count($push_logs) > 0) {

            foreach ($push_logs as $push_log) {
                
                $push_log->delete();
            }
        }
    }

    public function createPushLog($data) {
        // remove action update
        if ($data['action'] == 'update') {

            // get all push log by subject_id and subject_type
            $push_logs = $this->getPushLogByOptions([
                                ['subject_id', $data['subject_id']],
                                ['subject_type', $data['subject_type']],
                                ['action', 'update']
                            ]);
            if (count($push_logs) > 0) {
                foreach ($push_logs as $push_log) {
                    $push_log->delete();
                }
            }
        }

        if ($data['action'] == 'delete') {

            // get all push log by subject_id and subject_type
            $push_logs = $this->getPushLogByOptions([
                                ['subject_id', $data['subject_id']],
                                ['subject_type', $data['subject_type']]
                            ]);
            if (count($push_logs) > 0) {
                foreach ($push_logs as $push_log) {
                    $push_log->delete();
                }
            }
        }

        // create Push Logs
        $push_log = new PushLogs();
        $push_log->company_id = $data['company_id'];
        $push_log->action = $data['action'];
        $push_log->subject_id = $data['subject_id'];
        $push_log->subject_type = $data['subject_type'];
        $push_log->subject_data = json_encode($data['subject_data'], JSON_UNESCAPED_UNICODE);
        $push_log->created_at = date("Y-m-d H:i:s");
        $push_log->expiration = $data['expiration'] ?? null;

        if ($push_log->save()) {
            return PushLogs::find($push_log['id']);
        }

        return response('Create error', 404);
    }

    public function getPushLogByOptions($options = []) {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return PushLogs::where($options)->get();
        }

        return response('Push Log Not found', 404);
    }

    public function deletePushLog($id){
        // get push logs
        $push_log = PushLogs::where('id', $id)->first();

        if (empty($push_log))
            return response('Push log Not found', 404);

        if ($push_log->delete()) return response('OK', 200);

        return response('Delete Error', 404);
    }

    public function getPushLogByOptionsDelete($options = []){

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return PushLogs::where($options)->delete();
        }

        return response('Push Log Not found', 404);
    }
}
