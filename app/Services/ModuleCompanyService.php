<?php

namespace App\Services;

use App\Models\ModuleCompany;
use App\Models\ModuleApp;
use App\Services\PushLogsService;
use App\Services\RoutesService;

class ModuleCompanyService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\RoutesService
     */
    protected $routes;

    public function __construct(PushLogsService $push_logs, RoutesService  $routes)
    {
        $this->push_logs = $push_logs;
        $this->routes = $routes;
    }

    public function getDataByCompanyId($company_id){

        return  ModuleCompany::where('company_id', $company_id)
                            ->get()
                            ->toArray();
    }

    public function getModuleKeyByCompanyId($company_id){

        return  ModuleCompany::where('company_id', $company_id)
                            ->pluck('key_module')
                            ->toArray();
    }

    public function getDataById($id){
        return  ModuleCompany::where('id', $id)
                        ->first();
    }

    public function getDataModuleCompany($data){

        if($data['company_id']){

            $module_companys = $this->getDataByCompanyId($data['company_id']);

            $module_id_arr = [];

            foreach($module_companys as $module_company){
                array_push($module_id_arr, $module_company['module_id']);
            }

            if(count($module_id_arr) > 0){

                $module_app = ModuleApp::whereIn('id', $module_id_arr)
                                ->get()
                                ->toArray();

                return $module_app;
            }
        }
    }

    public function checkExistDataByCompanyIdAndModuleId($module_id, $company_id){

        return  ModuleCompany::where('module_id', $module_id)
                        ->where('company_id', $company_id)
                        ->exists();
    }

    public function getDataByCompanyIdAndModuleId($module_id, $company_id){

        return  ModuleCompany::where('module_id', $module_id)
                        ->where('company_id', $company_id)
                        ->first();
    }

    public function createModuleCompany($data, $company_id){

        //set isSave = false
        $isSave = false;

        if(count($data['modules']) > 0 && $company_id){

            foreach($data['modules'] as $module_id){

                //set isSave = false
                $isSave = false;

                $module_company = new ModuleCompany();
                $module_company->module_id = $module_id;
                $module_company->company_id = $company_id;

                switch($module_id){
                    case 1:
                        $module_company->key_module = 've_luot'; //module ban ve luot
                        break;
                    case 2:
                        $module_company->key_module = 'the_tra_truoc'; //module co the the la doi tuong the tra truoc
                        break;
                    case 3:
                        $module_company->key_module = 'the_km'; //module ap dung thanh toan the tra truoc tinh theo km
                        break;
                    case 4:
                        $module_company->key_module = 'the_dong_gia'; //module ap dung thanh toan cho the dong gia
                        break;
                    case 5:
                        $module_company->key_module = 'qr_code'; //module quet qrcode
                        break;
                    case 6:
                        $module_company->key_module = 'module_taxi'; //module ap dung ban ve tren taxi
                        break;
                    case 7:
                        $module_company->key_module = 'module_in_ve_the'; // module in ve cho the thang
                        break;
                    case 8:
                        $module_company->key_module = 'module_tt_km'; // module the thang theo km
                        break;
                    case 9:
                        $module_company->key_module = 'module_tt_sl_quet'; // module cho the thang theo so lan quet
                        break;
                    case 10:
                        $module_company->key_module = 'module_vc_hang_hoa'; // module ap dung van chuyen hang hoa
                        break;
                    case 11:
                        $module_company->key_module = 'module_in_ve_momo'; // module ap dung in ve khi thanh toan bang momo
                        break;
                    case 12:
                        $module_company->key_module = 'module_xe_khach'; // module ap dung cho du an xe khach
                        break;
                    case 13:
                        $module_company->key_module = 'module_ttt_km'; // module the tra truoc theo km
                        break;
                    case 14:
                        $module_company->key_module = 'module_ttt_sl_quet'; // module cho the tra truoc theo so lan quet
                        break;
                }

                if(!$this->checkExistDataByCompanyIdAndModuleId($module_id, $company_id)){
                    if($module_company->save()) $isSave = true;
                }else{
                    return response('Module company is exist',404);
                }
            }

            if($isSave){

                $this->createPushLogModuleAppByCompany($company_id);
            }
        }
    }

    public function createPushLogModuleAppByCompany($company_id) {

        $data = $this->getDataByCompanyId($company_id);
        $subject_data = [];

        foreach($data as $v) {
            $subject_data[] = $v['key_module'];
        }

        // delete push_log
        $wheres = [
            ['company_id', $company_id],
            ['subject_type', '=', 'module_company'],
            ['action', 'create'],
        ];
        $this->push_logs->getPushLogByOptionsDelete($wheres);

        // create push log for app module
        $push_log = [];
        $push_log['action'] = 'create';
        $push_log['subject_id'] = null;
        $push_log['subject_type'] = 'module_company';
        $push_log['company_id'] = $company_id;
        $push_log['subject_data'] = $subject_data;

        $this->push_logs->createPushLog($push_log);

    }

    public function deleteModuleCompany($data){

        if(!empty($data)){

            $module_id = $data['module_id'];
            $company_id = $data['company_id'];

            $module_company = $this->getDataByCompanyIdAndModuleId($module_id,$company_id);

            if(!empty($module_company)){

                //update module_data for routes
                $this->routes->updateModuleData($module_id, $company_id);
            }

            if ($module_company->delete()) {

                $this->createPushLogModuleAppByCompany($company_id);

                return response('OK', 200);
            }

            return response('Delete Error', 404);
        }
    }
}
