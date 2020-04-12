<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\VehiclesService;
use App\Models\Vehicle;

class ManagerVehiclesApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;      

    /**
     * Constructor
     */
    public function __construct(Request $request, VehiclesService $vehicles)
    {
        $this->request = $request;
        $this->vehicles = $vehicles;
    }

    /**
     * Operation managerUpdateVehicle
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateVehicle()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',            
            'license_plates' => 'bail|required|max:50',
            'rfid' => 'required',
            'route_id' => 'required',
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->updateVehicle($input); 
    }

    /**
     * Operation managerlistVehicles
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistVehicles()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->listVehicles($input);
    }

    /**
     * Operation manmagerCreateVehicle
     *
     * create.
     *
     *
     * @return Http response
     */
    public function managerCreateVehicle()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;       

        //path params validation
        $this->validate($this->request, [
            'license_plates' => 'bail|required|max:50',
            'rfid' => 'required',
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->createVehicle($input);  
    }

    /**
     * Operation managerDeleteVehicle
     *
     * Delete a vehicle.
     *
     * @param int $vehicle_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteVehicle($vehicle_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check vehicle id
        if (empty($vehicle_id) || (int)$vehicle_id < 0) 
            return response('Invalid ID supplied', 404);
        
        return $this->vehicles->deleteVehicle($vehicle_id, $company_id);           
    }

    /**
     * Operation managerGetVehicleById
     *
     * Find by ID.
     *
     * @param int $vehicle_id  (required)
     *
     * @return Http response
     */
    public function managerGetVehicleById($vehicle_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check vehicle id
        if (empty($vehicle_id) || (int)$vehicle_id < 0) 
            return response('Invalid ID supplied', 404);

        // get Vehicle
        $vehicle = $this->vehicles->getVehicleByKey('id', $vehicle_id, $company_id);

        if (empty($vehicle)) 
            return response('Vehicle Not found', 404);

        return $vehicle;
    }

    /**
     * Operation managerVehicleAssignRoute
     *
     * assign route.
     *
     *
     * @return Http response
     */
    public function managerVehicleAssignRoute()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',            
            'route_id' => 'bail|required|min:1',
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->assignRoute($input); 
    } 

    /**
     * Operation managerVehicleSearch
     *
     * assign route.
     *
     *
     * @return Http response
     */
    public function managerVehicleSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->vehicleSearch($input); 
    } 

    /**
     * Operation getlistVehicleAll
     *
     * assign route.
     *
     *
     * @return Http response
     */
    public function getlistVehicleAll()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->vehicles->getlistVehicleAll($input); 
    } 
    
}