<?php

/**
 * SMARTBUS API
 * No description provided (generated by Swagger Codegen https://github.com/swagger-api/swagger-codegen)
 *
 * OpenAPI spec version: 1.0.0
 * 
 *
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen.git
 * Do not edit the class manually.
 */


namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;

use App\Services\ActivityLogsService;

class AdminActivityLogsApi extends ApiController
{
       /**
     * @var Illuminate\Http\Request
     */
    protected $request;

     /**
     * @var App\Services\ActivityLogsService
     */
    protected $activity_logs;

    /**
     * Constructor
     */
    public function __construct(Request $request, ActivityLogsService $activity_logs)
    {
        $this->request = $request;
        $this->activity_logs = $activity_logs;
    }

    /**
     * Operation createActivityLog
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createActivityLog()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'action' => 'required',
            'subject_type' => 'required',
            'subject_data' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['user_id'] = $user->id;

        return $this->activity_logs->createActivityLog($input);
    }

    /**
     * Operation listActivityLog
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listActivityLog()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       // save Company
       $input = $this->request->all();
        
       return $this->activity_logs->listActivityLog($input);
    }

    /**
     * Operation deleteActivityLog
     *
     * delete.
     *
     * @param int $activity_log_id  (required)
     *
     * @return Http response
     */
    public function deleteActivityLog($activity_log_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
            
        return $this->activity_logs->deleteActivityLog($activity_log_id);
    }

    /**
     * Operation deleteActivityLogAll
     *
     * delete all list.
     *
     *
     * @return Http response
     */
    public function deleteActivityLogAll()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save Company
        $input = $this->request->all();
        
        return $this->activity_logs->deleteActivityLogAll($input);
    }

    /**
     * Operation getActivityLogById
     *
     * Find by ID.
     *
     * @param int $activity_log_id  (required)
     *
     * @return Http response
     */
    public function getActivityLogById($activity_log_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
            
        return $this->activity_logs->getActivityLogById($activity_log_id);
    }

     /**
     * Operation searchActivityLog
     *
     * create.
     *
     *
     * @return Http response
     */
    public function searchActivityLog()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->activity_logs->searchActivityLog($input);
    }

}