<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\SettingGlobalsService;

class ManagerSettingGlobalApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\SettingGlobalsService
     */
    protected $setting_globals;

    /**
     * Constructor
     */
    public function __construct(Request $request, SettingGlobalsService $setting_globals)
    {
        $this->request = $request;
        $this->setting_globals = $setting_globals;
    }

    /**
     * Operation createSettingGlobal
     *
     * Create setting global.
     *
     *
     * @return Http response
     */
    public function createSettingGlobal()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        
        return $this->setting_globals->createSettingGlobal($input);
    }
    /**
     * Operation managerGetSettingGlobal
     *
     * List of setting global.
     *
     *
     * @return Http response
     */
    public function managerGetSettingGlobal()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       $input['company_id'] = $user->company_id;
       
       return $this->setting_globals->listSettingGlobals($input);
    }
    
    /**
     * Operation deleteSettingGlobal
     *
     * Delete a setting global.
     *
     * @param string $key  (required)
     *
     * @return Http response
     */
    public function deleteSettingGlobal($key,$value)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input['company_id'] = $user->company_id;
        $input['key'] = $key;
        $input['value'] = $value;
        
        return $this->setting_globals->deleteSettingGlobal($input);
    }
}
