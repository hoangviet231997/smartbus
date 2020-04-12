<?php
namespace App\Services;

use App\Models\Vehicle;
use App\Models\ShiftSupervisor;
use App\Services\RfidCardsService;
use App\Services\PushLogsService;
use App\Services\RoutesService;
use App\Services\UsersService;
use App\Services\BusStationsService;
use Illuminate\Support\Facades\DB;

class VehiclesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\RoutesService
     */
    protected $routes;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

      /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

    public function __construct(PushLogsService $push_logs, RfidCardsService $rfidcards, RoutesService $routes, UsersService $users, BusStationsService $bus_stations)
    {
        $this->push_logs = $push_logs;
        $this->rfidcards = $rfidcards;
        $this->routes = $routes;
        $this->users = $users;
        $this->bus_stations = $bus_stations;
    }

    public function getVehicleById($id)
    {
        return Vehicle::where('id', $id)
                    ->with('route', 'rfidcard')
                    ->first();
    }

    public function getVehicleByKey($key, $value, $company_id = null)
    {
        if (!empty($company_id)) {
            $vehicle = Vehicle::where($key, $value)
                        ->where('company_id', $company_id)
                        ->with('route', 'rfidcard')
                        ->first();
        } else {
            $vehicle = Vehicle::where($key, $value)
                        ->with('route', 'rfidcard')
                        ->first();
        }

        return $vehicle;
    }

    public function updateIsRunning($vehicle_id, $is_running)
    {
        $vehicle = $this->getVehicleById($vehicle_id);

        if ($vehicle) {

            $vehicle->is_running = $is_running;
            if ($vehicle->save()) {
                return true;
            }
        }

        return false;
    }

    public function createVehicle($data)
    {
        $company_id = $data['company_id'];
        $license_plates = $data['license_plates'];
        $rfid = $data['rfid'];
        $bluetooth_mac_add = $data['bluetooth_mac_add'] ?? null;
        $bluetooth_pass = $data['bluetooth_pass'] ?? null;

        // check rfid
        $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid);

        if (empty($rfidcard)) {
            return response('Rfid card not found.', 404);
        }

        if ($this->rfidcards->checkRfidCardUsed($rfidcard->rfid, $rfidcard->barcode)) {
            return response('The rfid card has been used.', 404);
        }

        $rfidcard_id = $rfidcard->id;

        $vehicle = new Vehicle();
        $vehicle->is_running = 0;
        $vehicle->company_id = $company_id;
        $vehicle->license_plates = $license_plates;
        $vehicle->rfidcard_id = $rfidcard_id;
        $vehicle->bluetooth_mac_add = $bluetooth_mac_add;
        $vehicle->bluetooth_pass = $bluetooth_pass;

        if ($vehicle->save()) {

            $vehicle = $vehicle->toArray();
            $vehicle['rfid'] = $rfidcard->rfid;
            unset($vehicle['rfidcard']);
            unset($vehicle['created_at']);
            unset($vehicle['updated_at']);

            // create log
            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $vehicle['id'];
            $push_log['subject_type'] = 'vehicle';
            $push_log['subject_data'] = $vehicle;
            $this->push_logs->createPushLog($push_log);

            //
            $this->rfidcards->updateTargetAndUsage($rfidcard_id, $company_id, $vehicle['id'], 'vehicle');

            return $this->getVehicleById($vehicle['id']);
        }

        return response('Create Error', 404);
    }

    public function updateVehicle($data)
    {

        $id = $data['id'];
        $company_id = $data['company_id'];
        $license_plates = $data['license_plates'];
        $rfid = $data['rfid'];
        $route_id = $data['route_id'];
        $device_imei = $data['device_imei']?? null;
        $bluetooth_mac_add = $data['bluetooth_mac_add'] ?? null;
        $bluetooth_pass = $data['bluetooth_pass'] ?? null;

        // check route
        $route = $this->routes->getRouteById($route_id, $company_id);

        if (empty($route)) {
            return response('Route not found', 404);
        }

        // get Vehicle
        $vehicle = $this->getVehicleByKey('id', $id, $company_id);

        if (empty($vehicle))
            return response('Vehicle not found', 404);

        // get current rfid
        $data_rfidcard = $this->rfidcards->getRfidCardById($vehicle->rfidcard_id);
        $current_rfid = $data_rfidcard->rfid;

        //check change rfid
        if ($rfid != $current_rfid) {

            // check rfidcard
            $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid);

            if (empty($rfidcard)) {
                return response('Rfid card not found.', 404);
            }

            if ($this->rfidcards->checkRfidCardUsed($rfidcard->rfid, $rfidcard->barcode)) {
                return response('The rfid card has been used.', 404);
            }

            // set into user
            $vehicle->rfidcard_id = $rfidcard->id;
            $rfid = $rfidcard->rfid;
        }

        $vehicle->route_id = round($route_id);
        $vehicle->license_plates = $license_plates;
        $vehicle->device_imei = $device_imei;
        $vehicle->bluetooth_mac_add = $bluetooth_mac_add;
        $vehicle->bluetooth_pass = $bluetooth_pass;

        if ($vehicle->save()) {

            $vehicle->rfid = $rfid;
            $vehicle = $vehicle->toArray();
            $vehicle['route'] = $route;
            unset($vehicle['created_at']);
            unset($vehicle['rfidcard']);
            unset($vehicle['updated_at']);


            // create log
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $vehicle['id'];
            $push_log['subject_type'] = 'vehicle';
            $push_log['subject_data'] = $vehicle;
            $this->push_logs->createPushLog($push_log);

            // update taget for rfid card
            if ($rfid != $current_rfid) {

                // update new card
                $this->rfidcards->updateTargetAndUsage($rfidcard->id, $company_id, $vehicle['id'], 'vehicle');

                //remove old card
                $this->rfidcards->updateTargetAndUsage($data_rfidcard->id, $company_id, null, null);
            }

            return $this->getVehicleById($vehicle['id']);
        }

        return response('Update Error', 404);
    }

    public function deleteVehicle($id, $company_id)
    {
        // get Vehicle
        $vehicle = $this->getVehicleByKey('id', $id, $company_id);

        if (empty($vehicle))
            return response('Vehicle not found', 404);

        //remove use rfidcard
        if (!empty($vehicle->rfidcard_id)) {
            $this->rfidcards->updateTargetAndUsage($vehicle->rfidcard_id, $company_id, null, null);

            //  create log
            $push_logs = $this->push_logs->getPushLogByOptions([
                                ['subject_id', $vehicle->rfidcard_id],
                                ['subject_type', 'rfidcard']
                            ]);
            if (count($push_logs) > 0) {
                foreach ($push_logs as $push_log) {
                    $push_log->delete();
                }
            }

        }

        //  create log
        $push_log = [];
        $push_log['action'] = 'delete';
        $push_log['company_id'] = $company_id;
        $push_log['subject_id'] = $vehicle->id;
        $push_log['subject_type'] = 'vehicle';
        $push_log['subject_data'] = null;
        $this->push_logs->createPushLog($push_log);

        if ($vehicle->delete())
            return response('OK', 200);

        return response('Delete Error', 404);
    }

    public function listVehicles($data)
    {
        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        if(!empty($company_id)){

            $pagination = Vehicle::where('company_id', $company_id)
                        ->with('route', 'rfidcard')
                        ->leftJoin('shifts', function ($join) {
                            $join->on('vehicles.id', '=', 'shifts.vehicle_id')
                                ->whereNull('shifts.ended');
                        })
                        ->leftJoin('devices', 'devices.id', '=', 'shifts.device_id')
                        ->orderBy('shifts.device_id', 'desc')
                        ->orderBy('vehicles.id', 'desc')
                        ->select(
                            'vehicles.*',
                            'shifts.id as shift_id',
                            'shifts.started',
                            'shifts.device_id',
                            'shifts.user_id',
                            'shifts.subdriver_id',
                            'shifts.supervisor_id',
                            'devices.identity',
                            'shifts.station_id'
                        )
                        ->paginate($limit)
                        ->toArray();

            $vehicle_arr = [];

            foreach ($pagination['data'] as $vehicle) {

                $vehicle['driver_name'] = '';
                $vehicle['subdriver_name'] = '';
                $vehicle['station_from'] = '';
                $vehicle['supervisor'] = '';

                if (!empty($vehicle['user_id'])) {
                    $user = $this->users->getUserByKey('id', $vehicle['user_id'], $company_id);

                    if ($user) $vehicle['driver_name'] = $user->fullname;
                }

                if (!empty($vehicle['subdriver_id'])) {
                    $sub_user = $this->users->getUserByKey('id', $vehicle['subdriver_id'], $company_id);

                    if ($sub_user) $vehicle['subdriver_name'] = $sub_user->fullname;
                }

                if (!empty($vehicle['station_id'])) {

                    $bus_station = $this->bus_stations->getDataBusStationById($vehicle['station_id']);
                    if ($bus_station) $vehicle['station_from'] = $bus_station->name;
                }

                if($vehicle['shift_id']){

                    if (!empty($vehicle['supervisor_id'])) {

                        $supervisor_user = $this->users->getUserByKey('id', $vehicle['supervisor_id'], $company_id);

                        if ($supervisor_user){

                            $shift_superviser = ShiftSupervisor::where('shift_id', '=', $vehicle['shift_id'])
                                              ->where('user_id', '=', $supervisor_user->id)
                                              ->where('ended', '=', NULL)
                                              ->where('shift_supervisor_token', '!=', NULL)
                                              ->first();

                            if($shift_superviser){

                                $vehicle['supervisor'] = [
                                  "supervisor_name" => $supervisor_user->fullname,
                                  "supervisor_station_from" => '',
                                  "supervisor_started" => $shift_superviser->started,
                                ];
                                $supervisor_bus_station = $this->bus_stations->getDataBusStationById($shift_superviser->station_up_id);
                                $vehicle['supervisor']['supervisor_station_from'] = $bus_station ? $bus_station->name : '' ;
                            }
                        }
                    }
                }

                unset($vehicle['user_id']);
                unset($vehicle['subdriver_id']);
                array_push($vehicle_arr, $vehicle);
            }

            header("pagination-total: ".$pagination['total']);
            header("pagination-current: ".$pagination['current_page']);
            header("pagination-last: ".$pagination['last_page']);

            return $vehicle_arr;
        }else{

            return  Vehicle::all();
        }
    }

    public function assignRoute($data)
    {
        $vehicle_id = $data['id'];
        $route_id = $data['route_id'];
        $company_id = $data['company_id'];

        // check route
        $route = $this->routes->getRouteById($route_id, $company_id);

        if (empty($route)) {
            return response('Route not found', 404);
        }

        // check vehicle
        $vehicle = $this->getVehicleByKey('id', $vehicle_id, $company_id);

        if (empty($vehicle)) {
            return response('Vehicle not found', 404);
        }

        $vehicle->route_id = round($route_id);

        if ($vehicle->save()) {

            $vehicle->rfid = $vehicle->rfidcard->rfid;
            $vehicle = $vehicle->toArray();
            unset($vehicle['created_at']);
            unset($vehicle['updated_at']);
            // create log
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $vehicle['id'];
            $push_log['subject_type'] = 'vehicle';
            $push_log['subject_data'] = $vehicle;
            $this->push_logs->createPushLog($push_log);

            return $this->getVehicleById($vehicle['id']);
        }

        return response('Assign route error', 404);
    }

    public function getVehiclesByOptions($options = [])
    {
        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return Vehicle::where($options)
                        ->orderBy('is_running', 'DESC')
                        ->get();
        }

        return response('Error', 404);
    }

    public function vehicleSearch($data)
    {
        $license = $data['license'];
        $company_id = $data['company_id'];

        $vehicles = Vehicle::where('vehicles.company_id', $company_id)
                    ->where('vehicles.license_plates', 'like', '%'.$license.'%')
                    ->leftJoin('shifts', function ($join) {
                        $join->on('vehicles.id', '=', 'shifts.vehicle_id')
                            ->whereNull('shifts.ended');
                    })
                    ->leftJoin('devices', 'devices.id', '=', 'shifts.device_id')
                    ->with('route', 'rfidcard')
                    ->orderBy('shifts.device_id', 'desc')
                    ->orderBy('vehicles.id', 'desc')
                    ->select(
                        'vehicles.*',
                        'shifts.id as shift_id',
                        'shifts.started',
                        'shifts.station_id',
                        'shifts.device_id',
                        'shifts.user_id',
                        'shifts.subdriver_id',
                        'shifts.supervisor_id',
                        'devices.identity'
                    )
                    ->get()
                    ->toArray();

        $vehicle_arr = [];

        foreach ($vehicles as $vehicle) {

            $vehicle['driver_name'] = '';
            $vehicle['subdriver_name'] = '';
            $vehicle['station_from'] = '';
            $vehicle['supervisor'] = '';

            if (!empty($vehicle['user_id'])) {
                $user = $this->users->getUserByKey('id', $vehicle['user_id'], $company_id);

                if ($user) $vehicle['driver_name'] = $user->fullname;
            }

            if (!empty($vehicle['subdriver_id'])) {
                $sub_user = $this->users->getUserByKey('id', $vehicle['subdriver_id'], $company_id);

                if ($sub_user) $vehicle['subdriver_name'] = $sub_user->fullname;
            }

            if (!empty($vehicle['station_id'])) {

                $bus_station = $this->bus_stations->getDataBusStationById($vehicle['station_id']);
                if ($bus_station) $vehicle['station_from'] = $bus_station->name;
            }

            if($vehicle['shift_id']){

                if (!empty($vehicle['supervisor_id'])) {

                    $supervisor_user = $this->users->getUserByKey('id', $vehicle['supervisor_id'], $company_id);

                    if ($supervisor_user){

                        $shift_superviser = ShiftSupervisor::where('shift_id', '=', $vehicle['shift_id'])
                                          ->where('user_id', '=', $supervisor_user->id)
                                          ->where('ended', '=', NULL)
                                          ->where('shift_supervisor_token', '!=', NULL)
                                          ->first();

                        if($shift_superviser){

                            $vehicle['supervisor'] = [
                              "supervisor_name" => $supervisor_user->fullname,
                              "supervisor_station_from" => '',
                              "supervisor_started" => $shift_superviser->started,
                            ];
                            $supervisor_bus_station = $this->bus_stations->getDataBusStationById($shift_superviser->station_up_id);
                            $vehicle['supervisor']['supervisor_station_from'] = $bus_station ? $bus_station->name : '' ;
                        }
                    }
                }
            }

            unset($vehicle['user_id']);
            unset($vehicle['subdriver_id']);
            array_push($vehicle_arr, $vehicle);
        }

        return $vehicle_arr;
    }

    public function getVehicleIdByCompanyId($company_id){

        return Vehicle::where('company_id', $company_id)
                    ->orderBy('is_running', 'DESC')
                    ->pluck('id')
                    ->toArray();
        return response('Error', 404);
    }

    public function getlistVehicleAll($data)
    {
        return Vehicle::where('company_id', $data['company_id'])->with('rfidcard')->get();
        return response('Error', 404);
    }

}
