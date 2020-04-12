<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\BusStationsService;

class ManagerBusStationsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

    /**
     * Constructor
     */
    public function __construct(Request $request, BusStationsService $bus_stations)
    {
        $this->request = $request; 
        $this->bus_stations = $bus_stations; 
    }

    /**
     * Operation managerlistBusStations
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistBusStations()
    {
        $input = Request::all();

        //not path params validation
        if ($input['page'] < 0) {
            throw new \InvalidArgumentException('invalid value for $page when calling ManagerBusStationsApi.managerlistBusStations, must be bigger than or equal to 0.');
        }
        $page = $input['page'];

        $limit = $input['limit'];


        return response('How about implementing managerlistBusStations as a get method ?');
    }
     /**
     * Operation managerlistGroupBusStation
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistGroupBusStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // get company
        $company_id = $user->company_id;
        $input = $this->request->all();

        return $this->bus_stations->listGroupBusStation($company_id,$input);
    }
    /**
     * Operation manmagerCreateGroupBusStation
     *
     * create.
     *
     *
     * @return Http response
     */
    public function manmagerCreateGroupBusStation()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;
       return $this->bus_stations->createGroupBusStation($input);
    }
     /**
     * Operation manmagerUpdateGroupBusStation
     *
     * update.
     *
     *
     * @return Http response
     */
    public function manmagerUpdateGroupBusStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        
        return $this->bus_stations->updateGroupBusStation($input);
    }
    /**
     * Operation managerDeleteGroupBusStationById
     *
     * Delete.
     *
     * @param int $group_bus_station_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteGroupBusStationById($group_bus_station_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->bus_stations->deleteGroupBusStationById($group_bus_station_id, $input);
    }
    /**
     * Operation managerGetGroupBusStationById
     *
     * get find ID.
     *
     * @param int $group_bus_station_id  (required)
     *
     * @return Http response
     */
    public function managerGetGroupBusStationById($group_bus_station_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->bus_stations->getGroupBusStationById($group_bus_station_id);
    }

    public function managerSearchGroupBusStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->bus_stations->searchGroupBusStation($input);
    }
}
