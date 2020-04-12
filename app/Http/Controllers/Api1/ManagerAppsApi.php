<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\AppsService;

class ManagerAppsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\AppsService
     */
    protected $applications;

    /**
     * Constructor
     */
    public function __construct(Request $request, AppsService $applications)
    {
        $this->request = $request;
        $this->applications = $applications;
    }

    /**
     * Operation managerAppsCreate
     *
     * create.
     *
     *
     * @return Http response
     */
    public function managerAppsCreate()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_name' => 'required'
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->applications->createApp($input);             
    }
    /**
     * Operation managerAppsList
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerAppsList()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->applications->listApps($input);        
    }
    /**
     * Operation managerAppsUpdate
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerAppsUpdate()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'company_name' => 'required'
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->applications->updateApp($input);       
    }
    /**
     * Operation managerAppsDelete
     *
     * Delete.
     *
     * @param int $app_id  (required)
     *
     * @return Http response
     */
    public function managerAppsDelete($app_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check vehicle id
        if (empty($app_id) || (int)$app_id < 0) 
            return response('Invalid ID supplied', 404);
        
        return $this->applications->deleteApp($app_id);   
    }
    /**
     * Operation managerAppsGetById
     *
     * Find by ID.
     *
     * @param int $app_id  (required)
     *
     * @return Http response
     */
    public function managerAppsGetById($app_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check vehicle id
        if (empty($app_id) || (int)$app_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Application
        $app = $this->applications->getAppById($app_id);

        if (empty($app))
            return response('Application not found', 404);

        return $app;
    }

    /**
     * Operation managerAppsChangeApiKeyById
     *
     * change api key.
     *
     * @param int $app_id  (required)
     *
     * @return Http response
     */
    public function managerAppsChangeApiKeyById($app_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check vehicle id
        if (empty($app_id) || (int)$app_id < 0) 
            return response('Invalid ID supplied', 404);

        return $this->applications->changeApiKeyById($app_id, $user->company_id);        
    }    
}
