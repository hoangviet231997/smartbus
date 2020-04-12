<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Partner;
use App\Services\PartnersService;

class AdminPartnersApi extends ApiController
{
     /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\PartnersService
     */
    protected $partners;

    /**
     * Constructor
     */
    public function __construct(Request $request, PartnersService $partners)
    {
        $this->request = $request;
        $this->partners = $partners;
    }

    /**
     * Operation createPartner
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createPartner()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       //path params validation
       $this->validate($this->request, [
           'company_name' => 'bail|required',
           'partner_code' => 'bail|required'
       ]);

       // save Partner
       $input = $this->request->all();

       return $this->partners->createPartner($input);
    }
    /**
     * Operation listPartners
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listPartners()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       return $this->partners->listPartners();
    }
    /**
     * Operation updatePartner
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updatePartner()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_name' => 'bail|required'
        ]);

        // save Partner
        $input = $this->request->all();

        return $this->partners->updatePartner($input);
    }
    /**
     * Operation deletePartner
     *
     * Delete a partner.
     *
     * @param int $partner_id  (required)
     *
     * @return Http response
     */
    public function deletePartner($partner_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($partner_id) || (int)$partner_id < 0)
            return response('Invalid ID supplied', 404);

        return $this->partners->deletePartner($partner_id);
    }

    /**
     * Operation getParnertById
     *
     * Find by ID.
     *
     * @param int $partner_id  (required)
     *
     * @return Http response
     */
    public function getParnertById($partner_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($partner_id) || (int)$partner_id < 0)
            return response('Invalid ID supplied', 404);

        // get Partner
        $partner = $this->partners->getPartnerById($partner_id);

        if (empty($partner)) return response('Partner Not found', 404);

        return $partner;
    }

    /**
     * Operation createPartnerAccount
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createPartnerAccount()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_id' => 'bail|required',
            'name' => 'bail|required',
            'partner_code' => 'bail|required',
            'url_api' => 'bail|required',
            'username_login' => 'bail|required',
            'password_login' => 'bail|required',
            'public_key' => 'bail|required'
        ]);

        // save Partner
        $input = $this->request->all();

        return $this->partners->createPartnerAccount($input);
    }
    /**
     * Operation listPartnerAccounts
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listPartnerAccounts()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->partners->listPartnerAccounts();
    }
    /**
     * Operation updatePartnerAccount
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updatePartnerAccount()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'company_id' => 'bail|required',
            'name' => 'bail|required',
            'partner_code' => 'bail|required',
            'url_api' => 'bail|required',
            'username_login' => 'bail|required',
            'password_login' => 'bail|required',
            'public_key' => 'bail|required'
        ]);

        // save Partner
        $input = $this->request->all();

        return $this->partners->updatePartnerAccount($input);
    }
    /**
     * Operation deletePartnerAccount
     *
     * Delete a partner.
     *
     * @param int $partner_account_id  (required)
     *
     * @return Http response
     */
    public function deletePartnerAccount($partner_account_id)
    {
      // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($partner_account_id) || (int)$partner_account_id < 0)
            return response('Invalid ID supplied', 404);

        return $this->partners->deletePartnerAccount($partner_account_id);
    }
    /**
     * Operation getParnertAccountById
     *
     * Find by ID.
     *
     * @param int $partner_account_id  (required)
     *
     * @return Http response
     */
    public function getParnertAccountById($partner_account_id)
    {
      // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($partner_account_id) || (int)$partner_account_id < 0)
            return response('Invalid ID supplied', 404);

        // get Partner
        $partner_account = $this->partners->getPartnerAccountById($partner_account_id);

        if (empty($partner_account)) return response('Partner Not found', 404);

        return $partner_account;
    }
}
