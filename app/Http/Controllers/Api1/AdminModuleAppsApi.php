<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\ModuleAppService;

class AdminModuleAppsApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\ModuleAppService
     */
    protected $module_apps;

    /**
     * Constructor
     */
    public function __construct(Request $request, ModuleAppService $module_apps)
    {
        $this->request = $request;
        $this->module_apps = $module_apps;
    }

    /**
     * Operation createModuleApp
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createModuleApp()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       //path params validation
       $this->validate($this->request, [
        'name' => 'required',
        'display_name' => 'required',
        'description' => 'nullable'
       ]);

       // save device
       $input = $this->request->all();

       return $this->module_apps->createModuleApp($input);
    }
    /**
     * Operation listModuleApp
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listModuleApp()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
 
        // save device
        if($user->company_id){

            $company_id =  $user->company_id;
            return $this->module_apps->getListModuleApp($company_id);
        }else{
            return $this->module_apps->getListModuleApp($company_id = null);
        }  
    }
    /**
     * Operation getModuleAppById
     *
     * Find by ID.
     *
     * @param int $module_id  (required)
     *
     * @return Http response
     */
    public function getModuleAppById($module_id)
    {
          // check login
          $user = $this->requiredAuthUser();
          if (empty($user)) return response('token_invalid', 401);
   
          // save device
          return $this->module_apps->getDataById($module_id);
    }
    /**
     * Operation updateModuleApp
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateModuleApp()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'nullable'
        ]);

        // save device
        $input = $this->request->all();

        return $this->module_apps->updateModuleApp($input);
    }
}
