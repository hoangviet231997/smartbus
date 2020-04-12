<?php
namespace App\Services;
use App\Services\CompaniesService;
use App\Services\VehiclesService;
use DB;

class DashboardsService
{
    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;        

    public function __construct(CompaniesService $companies, VehiclesService $vehicles)
    {
        $this->companies = $companies;
        $this->vehicles = $vehicles;
    }

    public function managerDashboard($company_id)
    {
        // get company
        $company = $this->companies->getCompanyById($company_id);

        if (empty($company)) {
            return response('Company Not found', 404);
        }

        // get vehicle by company
        $vehicles_is_running = [];
        $vehicles_not_running = [];
        $vehicles = $this->vehicles->getVehiclesByOptions([
                        ['company_id', $company_id]
                    ]);

        if (count($vehicles) > 0) {

            foreach ($vehicles as $vehicle) {

                $vehicle['data'] = $this->getVehiclesById($vehicle->id);

                if ($vehicle->location) {
                    $vehicle->lat = $vehicle->location->getLat(); 
                    $vehicle->lng = $vehicle->location->getLng();
                    unset($vehicle->location);

                    if($vehicle->is_running == 1){
                        array_push($vehicles_is_running, $vehicle);
                    }
                    if($vehicle->is_running == 0){
                        array_push($vehicles_not_running, $vehicle);
                    }
                }
            }
        }

        $data = [];
        $data['company'] = $company;
        $data['vehicles_is_running'] = $vehicles_is_running;
        $data['vehicles_not_running'] = $vehicles_not_running;

        return $data;
    }

    public function managerGetVehiclesByCompanyId($company_id)
    {
        // get company
        $company = $this->companies->getCompanyById($company_id);

        if (empty($company)) {
            return response('Company Not found', 404);
        }

        // get vehicle by company
        $vehicles_is_running = [];
        $vehicles_not_running = [];
        $vehicles = $this->vehicles->getVehiclesByOptions([
                                ['company_id', $company_id]
                            ]);
        if (count($vehicles) > 0) {

            foreach ($vehicles as $vehicle) {
                $vehicle['data'] = $this->getVehiclesById($vehicle->id);
                if ($vehicle->location) {
                    $vehicle->lat = $vehicle->location->getLat(); 
                    $vehicle->lng = $vehicle->location->getLng();
                    unset($vehicle->location);
                    
                    if($vehicle->is_running == 1){
                        array_push($vehicles_is_running, $vehicle);
                    }
                    if($vehicle->is_running == 0){
                        array_push($vehicles_not_running, $vehicle);
                    }
                }
            }
        }

        $data = [];
        $data['company'] = $company;
        $data['vehicles_is_running'] = $vehicles_is_running;
        $data['vehicles_not_running'] = $vehicles_not_running;

        return $data;
    }

    public function getVehiclesById($vehicles_id) {
        return DB::table('vehicles')
                            ->where('vehicles.id', $vehicles_id)
                            ->where('vehicles.deleted_at', '=', null)
                            ->join('shifts', 'shifts.vehicle_id', '=', 'vehicles.id')
                            ->leftJoin('users', 'users.id', '=', 'shifts.user_id')
                            ->leftJoin('bus_stations', 'bus_stations.id', '=', 'shifts.station_id')
                            ->where('users.deleted_at', '=', null)
                            ->leftJoin('users as users1', 'users1.id', '=', 'shifts.subdriver_id')
                            ->where('users1.deleted_at', '=', null)
                            ->select('vehicles.license_plates', 'users.fullname as driver_name', 'users1.fullname as subdriver_name', 'bus_stations.name as direction_name')
                            ->orderBy('shifts.created_at', 'desc')
                            ->first();
    }
}