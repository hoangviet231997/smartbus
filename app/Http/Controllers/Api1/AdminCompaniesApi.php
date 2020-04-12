<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\CompaniesService;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;

class AdminCompaniesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * Constructor
     */
    public function __construct(Request $request, CompaniesService $companies)
    {
        $this->request = $request;
        $this->companies = $companies;
    }

    /**
     * Operation createCompany
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'bail|required',
            'fullname' => 'bail|required',
            'username' => 'required',
            'password' => 'required',
            'address' => 'nullable',
            'subname' => 'nullable',
            'print_at' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email|max:255'
        ]);

        // save Company
        $input = $this->request->all();

        return $this->companies->createCompany($input);
    }

    /**
     * Operation listCompanies
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listCompanies()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->companies->listCompanies($input);
    }

    /**
     * Operation updateCompany
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateCompany()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'name' => 'required',
            'fullname' => 'required',
            'subname' => 'nullable',
            'address' => 'nullable',
            'print_at' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email|max:255'
        ]);

        // save Company
        $input = $this->request->all();

        return $this->companies->updateCompany($input);
    }

    /**
     * Operation deleteCompany
     *
     * Delete a company.
     *
     * @param int $company_id  (required)
     *
     * @return Http response
     */
    public function deleteCompany($company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($company_id) || (int)$company_id < 0)
            return response('Invalid ID supplied', 404);

        return $this->companies->deleteCompany($company_id);
    }

    /**
     * Operation getCompanyById
     *
     * Find by ID.
     *
     * @param int $company_id  (required)
     *
     * @return Http response
     */
    public function getCompanyById($company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($company_id) || (int)$company_id < 0)
            return response('Invalid ID supplied', 404);

        // get Company
        $company = $this->companies->getCompanyById($company_id);

        if (empty($company)) return response('Company Not found', 404);

        return $company;
    }

     /**
     * Operation uploadFile
     *
     * upload.
     *
     *
     * @return Http response
     */
    public function uploadFile()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save Company
        $input = $this->request->all();
        return $this->companies->uploadFileCompany($input);
    }

    /**
     * Operation managerListCompanyByInputAndByTypeSearch
     *
     * Search company by input.
     *
     *
     * @return Http response
     */
    public function managerListCompanyByInputAndByTypeSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save Company
        $input = $this->request->all();
        return $this->companies->getListCompanyByInputAndByTypeSearch($input);
    }
}
