<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\RoutesService;

class ManagerRoutesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\RoutesService
     */
    protected $routes;

    /**
     * Constructor
     */
    public function __construct(Request $request, RoutesService $routes)
    {
        $this->request = $request;
        $this->routes = $routes;
    }

    /**
     * Operation managerUpdateRoute
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateRoute()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required|max:255',
            'number' => 'required',
            'modules' => 'required',
            'end_time' => 'nullable',
            'start_time' => 'nullable'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->routes->updateRoute($input);
    }
    /**
     * Operation managerlistRoutes
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistRoutes()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->routes->listRoute($input);
    }
    /**
     * Operation manmagerCreateRoute
     *
     * create.
     *
     *
     * @return Http response
     */
    public function manmagerCreateRoute()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required|max:255',
            'number' => 'nullable',
            'modules' => 'required',
            'end_time' => 'nullable',
            'start_time' => 'nullable'
        ]);

        // save user
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->routes->createRoute($input);
    }
    /**
     * Operation managerDeleteRoute
     *
     * Delete.
     *
     * @param int $route_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteRoute($route_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        return $this->routes->deleteRoute($route_id, $company_id);
    }
    /**
     * Operation managerGetRouteById
     *
     * Find by ID.
     *
     * @param int $route_id  (required)
     *
     * @return Http response
     */
    public function managerGetRouteById($route_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->routes->getRouteById($route_id);
    }

     /**
     * Operation managerGetRoutesBusStions
     *
     * Find by ID.
     *
     *
     * @return Http response
     */
    public function managerGetRoutesBusStions()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->routes->getRoutesBusStionByCompanyId($user->company_id);
    }

    public function managerSearchRoute()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // save user
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->routes->searchRoute($input);
    }
}
