<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\PrepaidCard;
use App\Services\PrepaidCardsService;
use App\Services\RolesService;

class ManagerPrepaidcardsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipsService
     */
    protected $prepaidcards;

    /**
     * Constructor
     */
    public function __construct(Request $request, PrepaidCardsService $prepaidcards, RolesService $roles)
    {
        $this->request = $request;
        $this->prepaidcards = $prepaidcards;
        $this->roles = $roles;        
    }

    /**
     * Operation managerUpdatePrepaidcard
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdatePrepaidcard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        if (!$this->roles->isAuthorized($role_id, 'ManagerPrepaidcards.update')) {
            return response('Permission denied', 404);
        }        
    }
    /**
     * Operation managerlistPrepaidcards
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistPrepaidcards()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        if (!$this->roles->isAuthorized($role_id, 'ManagerPrepaidcards.access')) {
            return response('Permission denied', 404);
        }        
    }
    /**
     * Operation manmagerCreatePrepaidcard
     *
     * create.
     *
     *
     * @return Http response
     */
    public function managerCreatePrepaidcard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerPrepaidcards.create')) {
        //     return response('Permission denied', 404);
        // }

        //path params validation
        $this->validate($this->request, [
            'rfid' => 'required',
            'balance' => 'required|numeric|min:0',
            'barcode' => 'required|max:50'
        ]);
        
        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->prepaidcards->createPrepaidCard($input);
    }
    /**
     * Operation managerDeletePrepaidcard
     *
     * Delete a vehicle.
     *
     * @param int $prepaidcard_id  (required)
     *
     * @return Http response
     */
    public function managerDeletePrepaidcard($prepaidcard_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        if (!$this->roles->isAuthorized($role_id, 'ManagerPrepaidcards.delete')) {
            return response('Permission denied', 404);
        } 
    }
    /**
     * Operation managerGetPrepaidcardsById
     *
     * Find by ID.
     *
     * @param int $prepaidcard_id  (required)
     *
     * @return Http response
     */
    public function managerGetPrepaidcardsById($prepaidcard_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        if (!$this->roles->isAuthorized($role_id, 'ManagerPrepaidcards.get')) {
            return response('Permission denied', 404);
        }           
    }    
}