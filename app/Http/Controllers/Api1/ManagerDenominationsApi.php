<?php

namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\DenominationsService;

class ManagerDenominationsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\DenominationsService
     */
    protected $denominations;

    /**
     * Constructor
     */
    public function __construct(
        Request $request, 
        DenominationsService $denominations
     )
    {
        $this->request = $request;
        $this->denominations = $denominations;
    }

    /**
     * Operation managerCreateDenomination
     *
     * Create Denomination .
     *
     *
     * @return Http response
     */
    public function managerCreateDenomination()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->denominations->createDenomination($input);
    }
    /**
     * Operation managerListDenomination
     *
     * List of denomination .
     *
     *
     * @return Http response
     */
    public function managerListDenomination($type_str)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['type_str'] = $type_str;

        return $this->denominations->listDenomination($input);
    }
    /**
     * Operation managerDeleteDenominationById
     *
     * Delete a Denomination .
     *
     * @param float $denomination_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteDenominationById($denomination_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->denominations->deleteDenominationById($denomination_id,  $user->company_id);
    }
}
