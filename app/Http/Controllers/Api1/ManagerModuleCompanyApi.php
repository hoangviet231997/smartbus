<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;

use App\Services\ModuleCompanyService;

class ManagerModuleCompanyApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

     /**
     * @var App\Services\ModuleCompanyService
     */
    protected $module_companies;

    /**
     * Constructor
     */
    public function __construct(Request $request, ModuleCompanyService $module_companies)
    {
        $this->request = $request;
        $this->module_companies = $module_companies;
    }

    /**
     * Operation listModuleCompany
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listModuleCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        return $this->module_companies->getDataModuleCompany($input);
    }

     /**
     * Operation createModuleCompany
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createModuleCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->module_companies->createModuleCompany($input, $user->company_id);
    }

    /**
     * Operation managerDeleteModuleCompany
     *
     * Delete.
     *
     * @param int $module_company_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteModuleCompany($module_company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['module_id'] = (int)$module_company_id;
        $input['company_id'] = $user->company_id;

        return $this->module_companies->deleteModuleCompany($input);
    }
}
