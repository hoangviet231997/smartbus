<?php
namespace App\Services;

use ___PHPSTORM_HELPERS\object;
use App\Models\Route;
use App\Services\PushLogsService;
use App\Services\BusStationsService;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use DB;
use App\Models\RoutesBusStation;
use App\Models\BusStation;

class RoutesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

    public function __construct(PushLogsService $push_logs, BusStationsService $bus_stations)
    {
        $this->push_logs = $push_logs;
        $this->bus_stations = $bus_stations;
    }

    public function createRoute($data)
    {
        $name = $data['name'];
        $company_id = $data['company_id'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $number = $data['number'];
        $distance_scan = $data['distance_scan'];
        $timeout_sound = $data['timeout_sound'] ;
        $modules =  json_encode($data['modules'], JSON_UNESCAPED_UNICODE);
        $tickets =  json_encode($data['tickets'], JSON_UNESCAPED_UNICODE);
        $bus_stations = $data['bus_stations'];

        if($this->checkExistsByNumberAndCompanyId($number,(int)$company_id)) return response('Mã số tuyến đã tồn tại', 404);

        // create route
        $route = new Route();
        $route->name = $name;
        $route->company_id = $company_id;
        $route->start_time = $start_time;
        $route->end_time = $end_time;
        $route->number = $number;
        $route->distance_scan = $data['distance_scan'] ? $data['distance_scan'] * 1000 : null;
        $route->timeout_sound = $data['timeout_sound'] ? $data['timeout_sound'] * 1000 : null;
        $route->module_data = $modules;
        $tickets = json_encode($data['tickets'], JSON_UNESCAPED_UNICODE);
        $route->ticket_data = count($data['tickets']) > 0  ? $tickets : null;

        if ($route->save()) {
            // create bus station

            $this->bus_stations->createBusStations($bus_stations, $route->id, $company_id);
            $route->number = (string) $number;

            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $route->id;
            $push_log['subject_type'] = 'route';
            $push_log['subject_data'] = $route;
            $this->push_logs->createPushLog($push_log);
            return response($route, 200);
        }

        return response('Create Error', 404);
    }

    public function updateRoute($data){

        $route = Route::find($data['id']);
        $route->name = $data['name'];
        $route->start_time = $data['start_time'];
        $route->end_time = $data['end_time'];
        $route->distance_scan = $data['distance_scan'] ? $data['distance_scan'] * 1000 : null;
        $route->timeout_sound = $data['timeout_sound'] ? $data['timeout_sound'] * 1000 : null;
        $route->module_data = json_encode($data['modules'], JSON_UNESCAPED_UNICODE);
        $tickets = json_encode($data['tickets'], JSON_UNESCAPED_UNICODE);
        $route->ticket_data = count($data['tickets']) > 0 ? $tickets : null;

        $company_id = $data['company_id'];
        $route->number = $data['number'];
        if($route->number != $data['number']){
          if($this->checkExistsByNumberAndCompanyId($data['number'],(int)$company_id)){
            return response('Mã số tuyến đã tồn tại', 404);
          }
        }

        if($route->save()){

            $this->bus_stations->updateBusStations($data['bus_stations'], $data['id'], $route->company_id);
            $route = $route->toArray();
            unset($route['created_at']);
            unset($route['updated_at']);
            unset($route['deleted_at']);
            $route['number'] = (string) $route['number'];

            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $route['company_id'];
            $push_log['subject_id'] = $route['id'];
            $push_log['subject_type'] = 'route';
            $push_log['subject_data'] = $route;
            $this->push_logs->createPushLog($push_log);

            return response($route, 200);
        }
    }

    public function deleteRoute($route_id, $company_id)
    {
        $this->bus_stations->deleteBusStations($route_id, $company_id);
        $push_log = [];
        $push_log['action'] = 'delete';
        $push_log['company_id'] = $company_id;
        $push_log['subject_id'] = $route_id;
        $push_log['subject_type'] = 'route';
        $push_log['subject_data'] = null;
        $this->push_logs->createPushLog($push_log);

        return response('Ok', 200);
    }

    public function getRouteById($id, $company_id = null)
    {
        if (!empty($company_id)) {
           return Route::where('id', $id)->where('company_id', $company_id)->first();
        } else {

            $route = Route::find($id);
            $bus_station = $this->bus_stations->getBusStationsById($id);
            $bus_stations = [];

            if( count($bus_station) > 0 ){

                foreach ($bus_station as $v) {

                    $obj = new \stdClass();
                    $obj->id = $v->id;
                    $obj->name = $v->name;
                    $obj->address = $v->address;
                    $obj->lat = $v->position->getLat();
                    $obj->lng = $v->position->getLng();
                    $obj->station_order =  $v->station_order;
                    $obj->direction =  $v->direction;
                    $obj->distance =  $v->distance;
                    $obj->group_key =  $v->group_key;
                    $obj->url_sound =  $v->url_sound;
                    $obj->station_relative =  $v->station_relative;
                    $bus_stations[] = $obj;
                }
                $route->bus_stations = $bus_stations;
            }

            return $route;
        }
    }

    public function listRoute($data)
    {
        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        $pagination = Route::where('company_id', $company_id)
                    ->paginate($limit)
                    ->toArray();

        header("pagination-total: ".$pagination['total']);
        header("pagination-current: ".$pagination['current_page']);
        header("pagination-last: ".$pagination['last_page']);

        return $pagination['data'];
    }

    public function getRoutesByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return Route::where($options)->get();
        }

        return response('Error', 404);
    }

    public function getPluckRouteIdByCompanyId($company_id){

        return Route::where('company_id', $company_id)
                    ->pluck('id')
                    ->toArray();
        return response('Error', 404);
    }

    public function updateModuleData($module_id, $company_id){

        if($module_id){

            $routes = Route::where('company_id', $company_id)
                            ->where('module_data','like', '%'.(string)$module_id.'%')
                            ->get();

            foreach ($routes as $route) {

                $module_arr = json_decode($route->module_data);
                $module_data_convert = array_diff($module_arr, [$module_id]);

                $module_tmp = [];
                foreach($module_data_convert as $value){ array_push( $module_tmp, $value);}

                $route->module_data = json_encode($module_tmp, JSON_UNESCAPED_UNICODE);

                if($route->save()){

                    $route = $route->toArray();
                    unset($route['created_at']);
                    unset($route['updated_at']);
                    unset($route['deleted_at']);
                    $route['number'] = (string) $route['number'];

                    $push_log = [];
                    $push_log['action'] = 'update';
                    $push_log['company_id'] = $company_id;
                    $push_log['subject_id'] = $route['id'];
                    $push_log['subject_type'] = 'route';
                    $push_log['subject_data'] = $route;
                    $this->push_logs->createPushLog($push_log);
                }
            }
        }
    }

    public function getRouteBusStationByCompanyId($company_id){

        $routes = Route::where('company_id', $company_id)
                        ->get()
                        ->toArray();

        $station_result = [];

        if(count($routes) > 0){

            foreach ($routes as $route) {

                $bus_stations = $this->bus_stations->getBusStationsById($route['id']);

                if(count($bus_stations) > 0 ){

                  foreach ($bus_stations as $v) {

                    $obj = new \stdClass();
                    $obj->route_name = $route['name'];
                    $obj->name = $v->name;
                    $obj->address = $v->address;
                    $obj->distance = $v->distance;
                    $obj->lat = $v->position->getLat();
                    $obj->lng = $v->position->getLng();
                    $obj->station_order =  $v->station_order;

                    $station_result[] = $obj;
                  }
                }
            }
        }
        return $station_result;
    }

    public function getRoutesBusStionByCompanyId($company_id){

        $routes = Route::where('company_id', $company_id)
                        ->get()
                        ->toArray();

        $route_station_result = [];

        if(count($routes) > 0){

            foreach ($routes as $route) {

                $obj = new \stdClass();
                $obj->route_name = $route['name'];
                $obj->route_id = $route['id'];
                $obj->bus_stations = [];

                $bus_stations = $this->bus_stations->getBusStationsById($route['id']);

                if(count($bus_stations) > 0 ){

                    foreach ($bus_stations as $v) {

                        $obj_station = new \stdClass();
                        $obj_station->name = $v->name;
                        $obj_station->id = $v->id;
                        $obj_station->lat = $v->position->getLat();
                        $obj_station->lng = $v->position->getLng();
                        $obj_station->station_order =  $v->station_order;
                        $obj_station->direction =  $v->direction;
                        $obj->bus_stations[] = $obj_station;
                    }

                    $route_station_result[] = $obj;
                }
            }
        }
        return $route_station_result;
    }

    public function getRoutesByCompaniesForApp($company_id) {
        $result = [];
        $routes = Route::where('company_id', $company_id)->get()->toArray();

        if (count($routes)) {

            foreach ($routes as $values) {

                $obj = new \stdClass();
                $obj->route_name = $values['name'];
                $obj->route_id = $values['id'];
                $obj->bus_stations = [];

                $route_bus_station = RoutesBusStation::where('route_id', $obj->route_id)->get();

                if (count($route_bus_station)) {

                    $tmp = [];

                    foreach ($route_bus_station as $value) {

                        $bus_station = BusStation::select('id', 'name', 'address', 'direction', 'distance', 'station_order', 'position')
                            ->where('id', $value->bus_station_id)->orderBy('station_order')->first();

                        $bus_station->lat = $bus_station->position->getLat();
                        $bus_station->lng = $bus_station->position->getLng();
                        unset($bus_station->position);
                        $tmp[] = $bus_station;
                    }

                    usort($tmp, array($this, "cmp_direction"));
                    $tmp = collect($tmp)->groupBy('direction')->toArray();

                    foreach ($tmp as $k => $vls) {
                        usort($vls, array($this, "cmp_station_order"));
                        $obj->bus_stations[] = $vls;
                    }
                }
                $result[]  = $obj;
            }
        }
        return $result;
    }

    public function checkExistsByNumberAndCompanyId($route_number, $company_id){
      return Route::where('company_id', $company_id)->where('number', '=', $route_number)->exists();
    }

    public function searchRoute($data)
    {
        $style_search = $data['style_search'] ?? '';
        $key_input = $data['key_input'] ?? '';
        $comapny_id = $data['company_id'];

        $route = Route::where('company_id', $comapny_id);

        switch ($style_search) {
            case 'name':
                $route->where('name', 'like', "%$key_input%");
                break;
            case 'route_number':
                $route->where('number', '=', $key_input);
                break;
            default:
                break;
        }

        return $route->get()->toArray();
    }

    //function sort
    public function cmp_station_order($a, $b) {
        $a = (object) $a;
        $b = (object) $b;
        return ((int)$a->station_order < (int)$b->station_order) ? -1 : 1;
    }
    public function cmp_direction($a, $b){
        $a = (object) $a;
        $b = (object) $b;
        return ((int)$a->direction < (int)$b->direction) ? -1 : 1;
    }
}
