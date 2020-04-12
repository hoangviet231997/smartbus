<?php
namespace App\Services;

use App\Models\ModuleApp;
use App\Services\ModuleCompanyService;

class ModuleAppService
{
    /**
     * @var App\Services\ModuleCompanyService
     */
    protected $module_companies;

    public function __construct(ModuleCompanyService $module_companies )
    {
        $this->module_companies = $module_companies;
    }

    public function getListModuleApp($company_id){

        if($company_id){

            $module_app_companys = $this->module_companies->getDataByCompanyId($company_id);

            $module_id_arr = [];

            foreach($module_app_companys as $value){
                array_push($module_id_arr, $value['module_id']);
            }

            $module_apps = ModuleApp::select('id','name','display_name')
                                ->whereNotIn('id', $module_id_arr)
                                ->get()
                                ->toArray();
            return $module_apps;

        }else{
            return ModuleApp::all()->toArray();
        }
    }

    public function createModuleApp($data){
    
        // check username exist
        if ($this->checkExistsByKey('name', $data['name']))
            return response(' Module app already exists', 404);            

        $module_app = new ModuleApp();
        $module_app->name = $data['name'];
        $module_app->display_name = $data['display_name'];
        $module_app->description = $data['description'] ?? null;

        if ($module_app->save()) {

            // create Push Log for company
            // $push_log = []; 
            // $push_log['action'] = 'create';
            // $push_log['subject_id'] = $module_app['id'];
            // $push_log['subject_type'] = 'module_app';
            // $push_log['company_id'] = null;
            // $push_log['subject_data'] = $module_app;
            // $this->push_logs->createPushLog($push_log);

            return $this->getDataById($module_app['id']);
        }

        return response('Create Error', 404);
    }

    public function updateModuleApp($data){

        $module_app = $this->getDataById($data['id']);

        if (empty($module_app)) 
            return response('Module app Not found', 404);

        $module_app->name = $data['name'];
        $module_app->display_name = $data['display_name'];
        $module_app->description = $data['description'] ?? null;     

        if ($module_app->save()) {

            // create Push Log for company
            // $push_log = []; 
            // $push_log['action'] = 'update';
            // $push_log['company_id'] = null;
            // $push_log['subject_id'] = $module_app->id;
            // $push_log['subject_type'] = 'module_app';
            // $push_log['subject_data'] = $module_app;
            // $this->push_logs->createPushLog($push_log);

            return  $module_app;
        }
        
        return response('Update Error', 404);
    }

    public function getDataById($id){
        return ModuleApp::where('id', $id)->first();
    }

    public function checkExistsByKey($key, $value){

        return ModuleApp::where($key, $value)->exists();
    }

    
}