<?php
namespace App\Services;

use App\Models\Denomination;
use App\Models\ModuleCompany;
use App\Services\PushLogsService;

class DenominationsService
{

       /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs)
    {
        $this->push_logs = $push_logs;
    }

    public function createDenomination($data)
    {

        $company_id = $data['company_id'];
        $price = floatval($data['price']);
        $type = $data['type'] ?? null;
        $color = $data['color'] ?? null;

        $denomination = new Denomination();
        $denomination->price = $price;
        $denomination->type = $type;
        $denomination->company_id = $company_id;
        $denomination->color = $color;

        $isModuleCar = false;
        $module_app_arr = ModuleCompany::where('company_id', $data['company_id'])
            ->pluck('key_module')
            ->toArray();
        if (in_array('module_xe_khach', $module_app_arr)) {$isModuleCar = true;}

        if ($denomination->save()) {

            if ($type == 'goods') {

                $where = [
                    ['company_id', $company_id],
                    ['action', 'create'],
                    ['subject_type', 'denomination_goods']
                ];
                $check_push_log = $this->push_logs->getPushLogByOptions($where);
                $push_log_v = [];

                if ($isModuleCar == false) {
                    $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $type]])->pluck('price')->toArray();
                } else {
                    $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $type]])->select('price', 'color')->get();
                }
                $push_log_v['action'] = 'create';
                $push_log_v['company_id'] =  $company_id;
                $push_log_v['subject_id'] = null;
                $push_log_v['subject_type'] = 'denomination_goods';

                if (count($check_push_log) > 0) {
                    foreach ($check_push_log as $vl) {
                        $vl->delete();
                    }
                }

                $this->push_logs->createPushLog($push_log_v);
            }

            if ($type == 'service') {

                $where = [
                    ['company_id', $company_id],
                    ['action', 'create'],
                    ['subject_type', 'denomination_service']
                ];
                $check_push_log = $this->push_logs->getPushLogByOptions($where);

                $push_log_v = [];
                $push_log_v['action'] = 'create';
                $push_log_v['company_id'] =  $company_id;
                $push_log_v['subject_id'] = null;
                $push_log_v['subject_type'] = 'denomination_service';
                $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $type]])->pluck('price')->toArray();

                if (count($check_push_log) > 0) {
                    foreach ($check_push_log as $vl) {
                        $vl->delete();
                    }
                }

                $this->push_logs->createPushLog($push_log_v);
            }

            if ($type == 'nfc') {

                $where = [
                    ['company_id', $company_id],
                    ['action', 'create'],
                    ['subject_type', 'denomination']
                ];
                $check_push_log = $this->push_logs->getPushLogByOptions($where);

                $push_log_v = [];
                $push_log_v['action'] = 'create';
                $push_log_v['company_id'] =  $company_id;
                $push_log_v['subject_id'] = null;
                $push_log_v['subject_type'] = 'denomination';
                $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $type]])->pluck('price')->toArray();

                if (count($check_push_log) > 0) {
                    foreach ($check_push_log as $vl) {
                        $vl->delete();
                    }
                }

                $this->push_logs->createPushLog($push_log_v);
            }
        }
        return $denomination;
    }

    public function listDenomination($data){

        return Denomination::where('company_id', $data['company_id'])->where('type', $data['type_str'])->orderBy('price')->get();
    }

    public function deleteDenominationById($id, $company_id)
    {

        $denomination = Denomination::find($id);

        if ($denomination) {

            if ($denomination->type == 'goods') {

                if ($denomination->delete()) {

                    $where = [
                        ['company_id', $company_id],
                        ['action', 'create'],
                        ['subject_type', 'denomination_goods']
                    ];
                    $check_push_log = $this->push_logs->getPushLogByOptions($where);

                    $push_log_v = [];
                    $push_log_v['action'] = 'create';
                    $push_log_v['company_id'] =  $company_id;
                    $push_log_v['subject_id'] = null;
                    $push_log_v['subject_type'] = 'denomination_goods';
                    $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $denomination->type]])->pluck('price')->toArray();

                    if (count($check_push_log) > 0) {
                        foreach ($check_push_log as $vl) {
                            $vl->delete();
                        }
                    }
                    $this->push_logs->createPushLog($push_log_v);
                    return response('Delete ok', 200);
                }
            }

            if ($denomination->type == 'service') {

                if ($denomination->delete()) {

                    $where = [
                        ['company_id', $company_id],
                        ['action', 'create'],
                        ['subject_type', 'denomination_service']
                    ];
                    $check_push_log = $this->push_logs->getPushLogByOptions($where);

                    $push_log_v = [];
                    $push_log_v['action'] = 'create';
                    $push_log_v['company_id'] =  $company_id;
                    $push_log_v['subject_id'] = null;
                    $push_log_v['subject_type'] = 'denomination_service';
                    $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $denomination->type]])->pluck('price')->toArray();

                    if (count($check_push_log) > 0) {
                        foreach ($check_push_log as $vl) {
                            $vl->delete();
                        }
                    }
                    $this->push_logs->createPushLog($push_log_v);
                    return response('Delete ok', 200);
                }
            }

            if ($denomination->type == 'nfc') {

                if ($denomination->delete()) {

                    $where = [
                        ['company_id', $company_id],
                        ['action', 'create'],
                        ['subject_type', 'denomination']
                    ];
                    $check_push_log = $this->push_logs->getPushLogByOptions($where);

                    $push_log_v = [];
                    $push_log_v['action'] = 'create';
                    $push_log_v['company_id'] =  $company_id;
                    $push_log_v['subject_id'] = null;
                    $push_log_v['subject_type'] = 'denomination';
                    $push_log_v['subject_data'] = Denomination::where([['company_id', $company_id], ['type', $denomination->type]])->pluck('price')->toArray();

                    if (count($check_push_log) > 0) {
                        foreach ($check_push_log as $vl) {
                            $vl->delete();
                        }
                    }
                    $this->push_logs->createPushLog($push_log_v);
                    return response('Delete ok', 200);
                }
            }
        }
    }
}
