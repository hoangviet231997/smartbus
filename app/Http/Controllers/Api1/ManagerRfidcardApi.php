<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\RfidCard;
use App\Services\RfidCardsService;
use App\Services\RolesService;
use App\Services\RfidCardsTestServices;

class ManagerRfidcardApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\RolesService
     */
    protected $roles;

    /**
     * @var App\Services\RfidCardsTestServices
     */
    protected $rfid_cards;

    /**
     * Constructor
     */
    public function __construct(
        Request $request, 
        RfidCardsService $rfidcards, 
        RolesService $roles,
        RfidCardsTestServices  $rfid_cards )
    {
        $this->request = $request;
        $this->rfidcards = $rfidcards;
        $this->roles = $roles;
        $this->rfid_cards = $rfid_cards;
    }

    /**
     * Operation managerCreateRfidcard
     *
     * Create Rfid card.
     *
     *
     * @return Http response
     */
    public function managerCreateRfidcard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerRfidcard.create')) {
        //     return response('Permission denied', 404);
        // }

        //path params validation
        $this->validate($this->request, [
            'rfid' => 'required',
            'barcode' => 'required',
        ]);

        //
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        //check Used
        if ($this->rfidcards->checkRfidCardUsed($input['rfid'], $input['barcode']))
            return response('The rfid card has been used.', 404);

        // save
        return $this->rfidcards->createRfidCard($input);
    }

    /**
     * Operation managerUpdateRfidcard
     *
     * Update.
     *
     *
     * @return Http response
     */
    public function managerUpdateRfidcard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerRfidcard.update')) {
        //     return response('Permission denied', 404);
        // }

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'rfid' => 'required',
            'barcode' => 'required',
        ]);

        //
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        
        // save
        return $this->rfidcards->updateRfidCard($input);
    }

    /**
     * Operation managerlistRfidcards
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistRfidcards()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save Company
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        
        return $this->rfid_cards->getListRfidCards($input);

    }

    /**
     * Operation managerDeleteRfidcard
     *
     * Delete.
     *
     * @param int $rfidcard_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteRfidcard($rfidcard_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check rfidcard id
        if (empty($rfidcard_id) || (int)$rfidcard_id < 0) 
            return response('Invalid ID supplied', 404);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerRfidcard.delete')) {
        //     return response('Permission denied', 404);
        // }

        // delete
        return $this->rfidcards->deleteRfidCard($rfidcard_id);
    }

    /**
     * Operation managerGetRfidcardById
     *
     * Find by ID.
     *
     * @param int $rfidcard_id  (required)
     *
     * @return Http response
     */
    public function managerGetRfidcardById($rfidcard_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check rfidcard id
        if (empty($rfidcard_id) || (int)$rfidcard_id < 0)
            return response('Invalid ID supplied', 404);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerRfidcard.get')) {
        //     return response('Permission denied', 404);
        // }

        $rfidcard = $this->rfidcards->getRfidCardById($rfidcard_id);
        
        if (empty($rfidcard)) 
            return response('Rfid card Not found', 404);

        return $rfidcard;
    } 

    /**
     * Operation managerSearchRfidcard
     *
     * Search.
     *
     *
     * @return Http response
     */
    public function managerSearchRfidcard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check is Authorized
        $role_id = $user->role_id;

        // if (!$this->roles->isAuthorized($role_id, 'ManagerRfidcard.search')) {
        //     return response('Permission denied', 404);
        // }

        //
        $input = $this->request->all();
        
        return $this->rfidcards->searchRfidCardByOptions([
                    ['rfid', $input['rfid']],
                    ['barcode', $input['barcode']]
                ]);

    }

    // /**
    //  * Operation createDenomination
    //  *
    //  * list by input rfid.
    //  *
    //  * @param string $key_word  (required)
    //  *
    //  * @return Http response
    //  */
    // public function createDenomination()
    // {
    //     // check login
    //     $user = $this->requiredAuthUser();
    //     if (empty($user)) return response('token_invalid', 401);

    //     $input = $this->request->all();
    //     $input['company_id'] = $user->company_id;
        
    //     return $this->rfidcards->createDenomination($input);
    // }

    // /**
    //  * Operation listdenominations
    //  *
    //  * list by input rfid.
    //  *
    //  * @param string $key_word  (required)
    //  *
    //  * @return Http response
    //  */
    // public function listdenominations()
    // {
    //     // check login
    //     $user = $this->requiredAuthUser();
    //     if (empty($user)) return response('token_invalid', 401);

    //     $input = $this->request->all();
    //     $input['company_id'] = $user->company_id;
        
    //     return $this->rfidcards->listdenominations($input);
    // }

    // /**
    //  * Operation deleteDenomination
    //  *
    //  * list by input rfid.
    //  *
    //  * @param string $key_word  (required)
    //  *
    //  * @return Http response
    //  */
    // public function deleteDenomination($price)
    // {
    //     // check login
    //     $user = $this->requiredAuthUser();
    //     if (empty($user)) return response('token_invalid', 401);

    //     $input = $this->request->all();
    //     $input['company_id'] = $user->company_id;
    //     $input['price'] = $price;
    
    //     return $this->rfidcards->deleteDenomination($input);
    // }
}