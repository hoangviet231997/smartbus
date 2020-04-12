<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Services\RolesService;
use App\Services\PrepaidCardsService;
use App\Services\UsersService;
use App\Services\MembershipsService;
use App\Services\DevicesService;
use Log;

class MachinePrepaidcardsApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\RolesService
     */
    protected $roles;

    /**
     * @var App\Services\PrepaidCardsService
     */
    protected $prepaidcards;

    /**
     * @var App\Services\UsersService
     */
    protected $users;   
    
    /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;   

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    /**
     * Constructor
     */
    public function __construct(Request $request, RolesService $roles, PrepaidCardsService $prepaidcards, UsersService $users, MembershipsService $memberships, DevicesService $devices)
    {
        $this->request = $request;
        $this->roles = $roles;
        $this->prepaidcards = $prepaidcards;
        $this->users = $users;
        $this->memberships = $memberships;
        $this->devices = $devices;
    }

    /**
     * Operation machineCreatePrepaidcard
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineCreatePrepaidcard()
    {
        $user_rfid = $this->request->header('user_rfid');
        $device_identity = $this->request->header('device_identity');

        // get user
        $user = $this->users->getUserByRfid($user_rfid);
        $role_id = $user->role_id;

        // check role
        // if (!$this->roles->isAuthorized($role_id, 'MachinePrepaidcards.create')) {
        //     return response('Permission denied', 404);
        // }
        
        //path params validation
        $this->validate($this->request, [
            'rfid' => 'required',
            'balance' => 'required|numeric|min:0',
            'barcode' => 'required|max:50'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->prepaidcards->create($input);
    }

    /**
     * Operation machineCreateMembership
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineCreateMembership()
    {
        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if (count($input) <= 0) {
            return response('The given data was invalid.', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }
        
        try {
            Log::info('data logs:'.json_encode($input));
            return $this->memberships->insertCard($device, $input);
        } catch (Exception $e) {
            return response('oki', 200);
        }
        //return $this->request->all();
        //$user_rfid = $this->request->header('user_rfid');
        //$device_identity = $this->request->header('device_identity');
        
        //path params validation
        // $this->validate($this->request, [
        //     'rfid' => 'required',
        //     'company_id' => 'bail|required|integer|min:1',
        //     'membershiptype_id' => 'bail|required|integer|min:1'
        // ]);

        // $input = $this->request->all();
        
        // return $this->memberships->insertCard($input);
    }

}