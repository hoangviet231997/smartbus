<?php
namespace App\Services;

use App\Models\Shift;
use App\Models\ShiftSupervisor;
use App\Models\Transaction;
use App\Services\UsersService;
use App\Services\DevicesService;
use App\Services\IssuedsService;
use App\Services\VehiclesService;
use App\Services\AttachmentsService;
use App\Services\RfidCardsService;
use App\Services\HistoryShiftService;
use App\Services\RoutesService;
use App\Services\CompaniesService;
use App\Services\PublicFunctionService;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Req;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use \Config as config;

class ShiftsService
{
    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    /**
     * @var App\Services\AttachmentsService
     */
    protected $attachments;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\HistoryShiftService
     */
    protected $historyShift;

     /**
     * @var App\Services\RoutesService
     */
    protected $routes;

     /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    public function __construct(
        UsersService $users,
        DevicesService $devices,
        IssuedsService $issueds,
        VehiclesService $vehicles,
        AttachmentsService $attachments,
        RfidCardsService $rfidcards,
        HistoryShiftService $historyShift,
        RoutesService $routes,
        CompaniesService $companies,
        PublicFunctionService $public_functions
        )
    {
        $this->users = $users;
        $this->devices = $devices;
        $this->issueds = $issueds;
        $this->vehicles = $vehicles;
        $this->attachments = $attachments;
        $this->rfidcards = $rfidcards;
        $this->historyShift = $historyShift;
        $this->routes = $routes;
        $this->companies = $companies;
        $this->public_functions = $public_functions;
    }

    public function checkShiftsExist($data) {
        return Shift::where('device_id', $data['device_id'])
                    ->where('user_id', $data['user_id'])
                    ->where('ended', null)
                    ->where('started', '!=', null)
                    ->exists();
    }

    public function checkShiftsExistByKey($key, $value) {
        return Shift::where($key, $value)
                    ->where('ended', null)
                    ->where('started', '!=', null)
                    ->exists();
    }

    public function login($imei, $rfid_user, $pin_code, $started = null, $station_id)  {
        $user = null;

        // get rfid user
        if (!empty($rfid_user)) {
            $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_user);

            if (empty($rfidcard)) {
                return response('Rfidcard not found', 404);
            }

            $rfidcard_id = $rfidcard->id;

            // get user by rfid
            $user = $this->users->getUserByKey('rfidcard_id', $rfidcard_id);
        } else {

            // get user by pin code
            $user = $this->users->getUserByKey('pin_code', $pin_code);
        }

        if (empty($user)) {
            return response('User not found', 404);
        }

        // set data
        $company_id = $user->company_id;
        $role_name = $user->role->name;
        $user_id = $user->id;

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }

        if (!$this->issueds->checkExistByDeviceAndCompany($device->id, $company_id)) {
            return response('The company does not have this device.', 404);
        }

        $device_id = $device->id;

        // if ($this->checkShiftsExistByKey('user_id', $user_id)) {
        //     return response('User have been logged!', 404);
        // }

        // if ($this->checkShiftsExistByKey('device_id', $device_id)) {
        //     return response('Device have been logged!', 404);
        // }


        $data = [];
        $data['user'] = $user;
        $data['device'] = $device;

        if ($role_name != 'driver' && $role_name != 'subdriver') {

            //check shift exist
            $check_shift = $this->getShiftsByDeviceIdAndUserId($device_id, $user_id);

            if ($check_shift) {

                $data['started'] = $check_shift->started;
                $data['shift_token'] = $check_shift->shift_token;
            } else {

                // create shift and return data
                $result = $this->createShifts($device_id, $user_id, null, $started, null, $station_id);

                if ($result) {
                    $data['started'] = $result->started;
                    $data['shift_token'] = $result->shift_token;
                }
            }
        }

        return $data;
    }

    public function updateRfidVehicle($imei, $rfid_user, $rfid_vehicle, $started = null, $station_id){
        $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_user);

        if (empty($rfidcard)) {
            return response('Rfidcard not found', 404);
        }

        $rfidcard_id = $rfidcard->id;

        // get user by rfid
        $user = $this->users->getUserByKey('rfidcard_id', $rfidcard_id);
        $role_name = $user->role->name;
        $company_id = $user->company_id;

        if ($role_name != 'driver' && $role_name != 'subdriver') {
            return response('Permission denied', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found.', 404);
        }

        // check rfid
        $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_vehicle);

        if (empty($rfidcard)) {
            return response('Rfidcard not found.', 404);
        }

        $rfidcard_id = $rfidcard->id;

        // get vehicle
        $vehicle = $this->vehicles->getVehicleByKey('rfidcard_id', $rfidcard_id, $company_id);

        if (empty($vehicle)) {
            return response('Vehicle not found', 404);
        }

        $route_id = $vehicle->route_id;

        // create Shift and return data
        $data = [];
        $data['user'] = $user;
        $data['device'] = $device;
        $data['vehicle'] = $vehicle;
        $data['route_id'] = $route_id;

        $device_id = $device->id;
        $user_id = $user->id;
        $vehicle_id = $vehicle->id;

        // if ($this->checkShiftsExistByKey('user_id', $user_id)) {
        //     return response('User have been logged!', 404);
        // }

        // if ($this->checkShiftsExistByKey('device_id', $device_id)) {
        //     return response('Device have been logged!', 404);
        // }

        // if ($this->checkShiftsExistByKey('vehicle_id', $vehicle_id)) {
        //     return response('Vehicle have been logged!', 404);
        //}

        //check shift exist
        $check_shift = $this->getShiftsByDeviceIdAndUserIdAndVehicleId($device_id, $user_id, $vehicle_id);

        if ($check_shift) {

            $data['started'] = $check_shift->started;
            $data['shift_token'] = $check_shift->shift_token;
        } else {

            // create shift
            $result = $this->createShifts($device_id, $user_id, $vehicle_id, $started, $route_id, $station_id);

            if ($result) {
                $data['started'] = $result->started;
                $data['shift_token'] = $result->shift_token;
            }
        }

        // create Attachment and update vehicle
        $this->vehicles->updateIsRunning($vehicle_id, 1);
        $this->attachments->createAttachment($device_id, $vehicle_id);

        return $data;
    }

    public function logout($shift_token, $ended = null, $total_amount = null){

        // check and get shift
        if (!$this->checkShiftsExistByKey('shift_token', $shift_token)) {
            return response('Shift not found', 404);
        }

        $shift = $this->getShiftsByShiftToken($shift_token);
        $device_id = $shift->device_id;
        $user_id = $shift->user_id;
        $vehicle_id = $shift->vehicle_id;

        if (!empty($vehicle_id) && $vehicle_id > 0) {
            // delete attachment
            $this->attachments->deleteAttachment($device_id, $vehicle_id);

            // update vehicle not action
            $this->vehicles->updateIsRunning($vehicle_id, 0);
        }

        // update device not action
        $this->devices->updateIsRunning($device_id, 0);

        $shift->ended = empty($ended) ? date("Y-m-d H:i:s") : $ended;
        $shift->shift_token = null;
        $shift->total_amount = $total_amount;

        if ($shift->save()){

            //call api to server socket update vehicle stop
            $vehicle = $this->vehicles->getVehicleById($vehicle_id);

            if($vehicle){

                $timestamp = strtotime(date('Y-m-d H:i:s'))*1000;
                $url_socket = config::get('constants.URL_SOCKET').'vehicle/update/isRunning';
                $partner_code = config::get('constants.PARTNER_CODE');

                $obj_data = [
                    'company_id' => $vehicle->company_id,
                    'vehicle_id' => $vehicle->id,
                    'timestamp' => $timestamp,
                ];
                $data_encode = json_encode($obj_data);
                $hash_data = $this->public_functions->enCrypto($data_encode, $partner_code);
                try {

                    $client = new Client();
                    // $headers = [
                    //     'authorization' => 'NKbothkvfMetW8WUrmVN7MtVdtsy6tCo6mm7ZX9Y',
                    //     'content-type' => 'application/json'
                    // ];
                    // $body = json_encode(['hashData' => $hash_data]);
                    // $request = new Req('POST', 'https://preprod.busmap.com.vn:2300/api/vehicle/update/isRunning', $headers, $body);
                    // $promise = $client->send($request, ['timeout' => 3]);

                    $response = $client->request('POST', $url_socket, [
                        'headers' => [
                            'authorization' => config::get('constants.AUTH_SOCKET'),
                            'content-type' => 'application/json'
                        ],
                        'body' => json_encode(['hashData' => $hash_data])
                    ]);
                    Log::info('hash: '.$hash_data);
                    Log::info('response: '.json_encode($response));
                    Log::info('response: '.json_encode($response->getStatusCode()));

                } catch(ClientException $ce){
                    $message = $ce->getMessage();
                    Log::info('error 1: '.($message));
                } catch(RequestException $re){
                    $message = $re->getMessage();
                    Log::info('error 2: '.($message));
                } catch (Exception $e) {
                    Log::info('error 3: '.($message));
                }
            }

            return ['status' => true, 'message' => 'Ok'];
        }

        return ['status' => false, 'message' => 'Logout fail.'];
    }

    public function getShiftsById($id) {

        $shift =  Shift::where('id',$id)
                        ->first();
        return $shift;
    }

    public function getShiftsByShiftToken($shift_token){

        return Shift::where('shift_token', $shift_token)
                    ->where('ended', null)
                    ->where('started', '!=', null)
                    ->first();
    }

    public function getShiftsByDeviceIdAndUserId($device_id, $user_id) {
        return Shift::where('device_id', $device_id)
                    ->where(function ($query) use ($user_id) {
                        $query->where('user_id', $user_id)
                              ->orWhere('subdriver_id', $user_id);
                    })
                    ->where('ended', null)
                    ->where('started', '!=', null)
                    ->first();
    }

    public function getShiftsByDeviceIdAndUserIdAndVehicleId($device_id, $user_id, $vehicle_id) {
        return Shift::where('device_id', $device_id)
                    ->where('vehicle_id', $vehicle_id)
                    ->where(function ($query) use ($user_id) {
                        $query->where('user_id', $user_id)
                              ->orWhere('subdriver_id', $user_id);
                    })
                    ->where('ended', null)
                    ->where('started', '!=', null)
                    ->first();
    }

    public function createShifts($device_id, $user_id, $vehicle_id = null, $started = null, $route_id = null, $station_id){

        // get user by id
        $user = $this->users->getUserByKey('id', $user_id);
        $role_name = $user->role->name;

        // create new
        $shift = new Shift();
        $shift->device_id = $device_id;
        $shift->started = empty($started) ? date("Y-m-d H:i:s") : $started;
        $shift->vehicle_id = $vehicle_id;
        $shift->route_id = $route_id;
        $shift->shift_token = uniqid();
        $shift->collected = 0;
        $shift->hidden = 0;
        $shift->station_id = $station_id;

        if ($role_name == 'subdriver')
            $shift->subdriver_id = $user_id;
        else
            $shift->user_id = $user_id;

        if ($shift->save()) {
            // update device action
            $this->devices->updateIsRunning($device_id, 1);
            return $this->getShiftsById($shift['id']);
        }

        return false;
    }

    public function getShiftById($id){

        if($id){
            return Shift::where('id', $id)->first();
        }
        return response('Shifts Not found', 404);
    }

    public function getShiftsByOptions($options = []){
        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }
           return Shift::where($options)->get();
        }

        return response('Shifts Not found', 404);
    }

    public function getShiftsByOptionAndCompanyId($options, $company_id){
        if (count($options) > 0) {

            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }
            return Shift::where($options)
                        ->join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->where('routes.company_id', $company_id)
                        ->join('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
                        ->select('shifts.*', 'routes.name as route_name', 'routes.number as route_number', 'vehicles.license_plates as license_plates')
                        ->get();
        }

        return response('Shifts Not found', 404);
    }

    public function getShiftsByOptionsOrEnded($options = [], $orWheres){
        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }
           return Shift::where($orWheres)->orWhere($options)->get();
        }

        return response('Shifts Not found', 404);
    }

    public function updateDriverAndSubDriverByShiftId($shift_id, $driver_id = null, $subdriver_id = null) {
        $shift = $this->getShiftsById($shift_id);

        if (empty($shift)) {
            return response('Shift not found', 404);
        }

        $shift->user_id = $driver_id;
        $shift->subdriver_id = $subdriver_id;

        if ($shift->save()) {
            return $shift;
        }
        return response('Update not found', 404);
    }

    public function updateCollected($rows = array()){
        $shifts = $rows['shifts'];
        if (count($shifts) > 0) {

            foreach ($shifts as $shift) {

                $data = $this->getShiftsById( (int) $shift['shift_id']);

                if ($data) {

                    $data->collected = 1;

                    if($data->save()){

                        //insert history
                        $this->historyShift->created($shift, $rows['user']->id);
                    }
                }
            }
            return ['status' => true, 'message' => 'Ok'];
        }
        return response('No data', 404);
    }

    public function updateHiddenedByShitfId($shift_id,  $value){

        $shift = $this->getShiftsById((int)$shift_id);

        if (empty($shift)) {
            return response('Shift not found', 404);
        }

        $shift->hidden = $value;

        if ($shift->save()) {
            return $shift;
        }
        return response('Update not found', 404);
    }

    public function getTotalBillsByDeviceId($data){

        $date_from = date('Y-m-d 00:00:00', $data['timestamp']);
        $date_to = date('Y-m-d 23:00:00', $data['timestamp']);
        $company_id = $data['company_id'];

        $orWheres = [
            ['ended', '>=' , $date_from],
            ['ended', '<=' , $date_to],
            ['device_id', $data['device_id']],
            ['ended', '!=' , NULL],
            ['shift_destroy', '!=' , 1]
        ];

        $shifts = $this->getShiftsByOptions($orWheres);

        $result = [];

        if(count($shifts) > 0){

            $company = $this->companies->getCompanyById($company_id);

            $result = [
                'company' => $company,
                'license_plates' => '',
                'route_name' => '',
                'route_number' => 0,
                'driver_name' => '',
                'subdriver_name' => '',
                'count_charge_free' => 0,
                'count_charge' => 0,
                'count_pos' => 0,
                'count_charge_month' => 0,
                'count_online' => 0,
                'total_charge' => 0,
                'total_pos' => 0,
                'total_deposit' => 0,
                'total_deposit_month' => 0,
                'total_online' => 0,
                'total_pos_goods' => 0,
                'total_charge_goods' => 0,
                'shifts' => []
            ];

            foreach ($shifts as $shift) {

                //get vehicle
                if(!empty($shift->vehicle_id)){
                    $vehicle = $this->vehicles->getVehicleById($shift->vehicle_id);
                    $result['license_plates'] = $vehicle ? $vehicle->license_plates : '';
                }

                //get route
                if(!empty($shift->route_id)){
                    $route = $this->routes->getRouteById($shift->route_id);
                    $result['route_name'] = $route ? $route->name : '';
                    $result['route_number'] = $route ? $route->number : 0;
                }

                //get driver
                if (!empty($shift->user_id)) {

                    $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $result['driver_name'] = $driver ? $driver->fullname : "";
                }

                // get subdriver
                if (!empty($shift->subdriver_id)) {
                    $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $result['subdriver_name'] = $subdriver ? $subdriver->fullname : "";
                }

                $obj = new \stdClass;
                $obj->work_time = date("H:i:s",strtotime($shift->started))." - ".date("H:i:s",strtotime($shift->ended));
                $obj->total_pos = 0;
                $obj->total_deposit = 0;
                $obj->total_deposit_month = 0;
                $obj->total_pos_goods = 0;

                $transactions = Transaction::where('shift_id', $shift->id)
                                        ->where('company_id', $company_id)
                                        ->where('ticket_destroy','!=', 1)
                                        ->get();

                if(count($transactions) > 0){

                    foreach ($transactions as $transaction) {

                        switch($transaction->type){

                            case "pos":
                                $result['count_pos'] += 1;
                                $result['total_pos'] += (float)$transaction->amount;
                                $obj->total_pos += (float)$transaction->amount;
                                break;

                            case "charge":
                                $result['count_charge'] += 1;
                                $result['total_charge'] += (float)$transaction->amount;
                                break;

                            case "charge_month":
                                $result['count_charge_month'] += 1;
                                break;

                            case "deposit":
                                $result['total_deposit'] += (float)$transaction->amount;
                                $obj->total_deposit += (float)$transaction->amount;
                                break;

                            case "deposit_month":
                                $result['total_deposit_month'] += (float)$transaction->amount;
                                $obj->total_deposit_month += (float)$transaction->amount;
                                break;

                            case "qrcode":
                                $result['count_online'] += 1;
                                $result['total_online'] += (float)$transaction->amount;
                                break;

                            case "charge_free":
                                $result['count_charge_free'] += 1;
                                break;

                            case "pos_goods":
                                $result['total_pos_goods'] += (float)$transaction->amount;
                                $obj->total_pos_goods += (float)$transaction->amount;
                                break;

                            case "charge_goods":
                                $result['total_charge_goods'] += (float)$transaction->amount;
                                break;
                        }
                    }
                }

                $result['shifts'][] = $obj;
            }
        }
        return [$result];
    }

    //----------------------- shift supervisor --------------------------------------
    public function loginShiftSupervisor($data){

        // get rfid user
        if (!empty($data['supervisor_rfid'])) {
            $rfidcard = $this->rfidcards->getRfidCardByRfid($data['supervisor_rfid']);
            if (empty($rfidcard)) return response('Rfidcard not found', 404);
        }

        $check_shift_supervisor = ShiftSupervisor::where([
          ['shift_id', '=', $data['shift_id']],
          ['user_id', '=', $data['supervisor_id']],
          ['started', '=', $data['started']],
          ['shift_supervisor_token', '!=', NULL]
        ])->exists();

        if (!$check_shift_supervisor) {
            // create shift and return data
            $result = $this->createShiftSupervisor($data);
            if($result) return $result;
        }
        return false;
    }

    public function logoutShiftSupervisor($data){

        // get rfid user
        if (!empty($data['supervisor_rfid'])) {
            $rfidcard = $this->rfidcards->getRfidCardByRfid($data['supervisor_rfid']);
            if (empty($rfidcard)) return response('Rfidcard not found', 404);
        }

        $shift_supervisor = ShiftSupervisor::where([
          ['shift_id', '=', $data['shift_id']],
          ['user_id', '=', $data['supervisor_id']],
          ['ended', '=', NULL],
          ['shift_supervisor_token', '!=', NULL]
        ])->orderBy('id')
        ->get();

        if (count($shift_supervisor) > 0) {

            foreach ($shift_supervisor as $key => $value) {

                $shift_supervisor_up = $value;
                $shift_supervisor_up->ended = $data['ended'];
                $shift_supervisor_up->station_down_id = $data['station_down_id'];
                $shift_supervisor_up->shift_supervisor_token = null;
                $shift_supervisor_up->save();
                Log::info('update_shift_supervisor '. json_encode($shift_supervisor_up));
            }

            return true;
        }
    }

    public function updateSupervisorIdByShiftId($shift_id, $supervisor_id){

        Log::info('Update supervisor '. $supervisor_id+' for shift: '+$shift_id);

        $shift = $this->getShiftsById($shift_id);

        if (empty($shift)) return false;

        $shift->supervisor_id = $supervisor_id;

        if ($shift->save()) return $shift;

        return false;
    }

    public function createShiftSupervisor($data){

        // create new
        $shift_supervisor = new ShiftSupervisor();
        $shift_supervisor->shift_id = $data['shift_id'];
        $shift_supervisor->user_id = $data['supervisor_id'];
        $shift_supervisor->started = $data['started'];
        $shift_supervisor->station_up_id = $data['station_up_id'];
        $shift_supervisor->shift_supervisor_token = uniqid();

        if ($shift_supervisor->save()) {
          Log::info('Create supervisor '. json_encode($shift_supervisor));
          return $shift_supervisor;
        }

        return false;
    }
}
