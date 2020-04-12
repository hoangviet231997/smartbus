<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Services\ShiftsService;

class MachineShiftsApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\MembershipsService
     */
    protected $shifts;

    /**
     * Constructor
     */
    public function __construct(Request $request, ShiftsService $shifts)
    {
        $this->request = $request;
        $this->shifts = $shifts;
    }

    /**
     * Operation machineLogin
     *
     * Login for machine.
     *
     *
     * @return Http response
     */
    public function machineLogin()
    {
        //path params validation
        $this->validate($this->request, [
            'rfid_user' => 'required',
            'pin_code' => 'required'
        ]);

        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();
        $rfid_user = $input['rfid_user'];
        $pin_code = $input['pin_code']; 

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }
        
        return $this->shifts->login($imei, $rfid_user, $pin_code);
    }
    /**
     * Operation machineLogout
     *
     * Logout for machine.
     *
     *
     * @return Http response
     */
    public function machineLogout()
    {
        //path params validation
        $shift_token = $this->request->header('X-ShiftToken');

        if (empty($shift_token)) {
            return response('Invalid shift_token supplied', 404);
        }
        
        return $this->shifts->logout($shift_token);
    }

    /**
     * Operation machineUpdateRfidVehicle
     *
     * update rfid Vehicle for machine.
     *
     *
     * @return Http response
     */
    public function machineUpdateRfidVehicle()
    {
        //path params validation
        $this->validate($this->request, [
            'rfid_vehicle' => 'required',
            'rfid_user' => 'required'
        ]);

        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();
        $rfid_vehicle = $input['rfid_vehicle'];
        $rfid_user = $input['rfid_user'];        

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        return $this->shifts->updateRfidVehicle($imei, $rfid_user, $rfid_vehicle);
    }    
}