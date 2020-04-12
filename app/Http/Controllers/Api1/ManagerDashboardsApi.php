<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\DashboardsService;
use App\Services\RoutesService;

class ManagerDashboardsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\DashboardsService
     */
    protected $dashboards;

     /**
     * @var App\Services\RoutesService
     */
    protected $routes;

    /**
     * Constructor
     */
    public function __construct(Request $request, DashboardsService $dashboards, RoutesService $routes)
    {
        $this->request = $request;        
        $this->dashboards = $dashboards;
        $this->routes = $routes;
    }

    /**
     * Operation managerDashboardsGetData
     *
     * get.
     *
     *
     * @return Http response
     */
    public function managerDashboardGetData()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->dashboards->managerDashboard($user->company_id);
    }

    /**
     * Operation managerDashboardGetVehicles
     *
     * get.
     *
     *
     * @return Http response
     */
    public function managerDashboardGetVehicles()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->dashboards->managerGetVehiclesByCompanyId($user->company_id);
    } 

    /**
     * Operation getVehiclesById
     *
     * get.
     *
     *
     * @return Http response
     */
    public function getVehiclesById($vehicles_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->dashboards->getVehiclesById($vehicles_id);
    } 

      /**
     * Operation managerDashboardGetRouteBusStations
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerDashboardGetRouteBusStations()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        return $this->routes->getRouteBusStationByCompanyId($company_id);
    }
}
