<?php
namespace App\Services;

use App\Models\BusStation;
use App\Models\RoutesBusStation;
use App\Services\PushLogsService;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Carbon\Carbon;
use App\Models\Route;
use App\Models\ModuleCompany;
use App\Models\GroupBusStation;
use App\Services\PartnersService;
use App\Services\PublicFunctionService;
use App\Services\TicketPricesService;
use Log;

class BusStationsService
{
    protected $push_logs;
    protected $partner_codes;
    protected $public_functions;


    public function __construct(
        PushLogsService $push_logs,
        PartnersService $partner_codes,
        PublicFunctionService $public_functions,
        TicketPricesService $ticket_prices
    ) {
        $this->push_logs = $push_logs;
        $this->partner_codes = $partner_codes;
        $this->public_functions = $public_functions;
        $this->ticket_prices = $ticket_prices;
    }

    public function saveAudioBase64($data, $middle)
    {

        if ($data) {
            //cut string by ";" => $audio[0], $audio[1]
            $audio = explode(';',  $data);

            //get file name
            $audio_name = explode('/',  $audio[0]);
            $file_name = $middle . '_' . time() . '.' . $audio_name[1];

            //get data file
            $audio_data = str_replace('base64,', '', $audio[1]);
            $decoded = base64_decode($audio_data);

            //location file
            $path = public_path() . "/audio/bus-stations/" . $file_name;

            //save file
            if (file_put_contents($path, $decoded)) {
                return $file_name;
            }
        }
    }

    public function removeAudioBase64($file_name)
    {
        if ($file_name) {
            $path = public_path() . "/audio/bus-stations/" . $file_name;
            if (file_exists($path)) {
                if (unlink($path)) return true;
            }
        }
    }

    private function distance($lat1, $lng1, $lat2, $lng2)
    {
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return round($miles * 1.609344 * 1000); // kilomet -> km
    }

    public function createBusStations($bus_stations = array(), $route_id = 0, $company_id = 0)
    {

        // create bus station
        if (!empty($bus_stations) && count($bus_stations) > 0 && $route_id > 0 && $company_id > 0) {

            usort($bus_stations, array($this, "cmp_station_order"));
            $bus_stations = collect($bus_stations)->groupBy('direction')->toArray();

            foreach ($bus_stations as $key => $values) {

                $distance_glo = 0;

                foreach ($values as $k => $value) {

                    $audio_params = $value['str_audio_base64'] ?? null;
                    $direction = $value['direction'] ?? 0;
                    $distance_re =  (float) $value['distance'] ?? 0;
                    $status_distance = null;
                    if (isset($value['statusDistance'])) {
                        $status_distance = (int) $value['statusDistance'];
                    }

                    $bus_station = new BusStation();
                    $bus_station->name = $value['name'];
                    $bus_station->address = $value['address'];
                    $bus_station->position = new Point($value['lat'], $value['lng']); // (lat, lng)

                    if ($status_distance == 0) {

                        if ($k == 0) {
                            $distance_glo = 0;
                        }
                        if ($k > 0) {
                            $distance_glo += $this->distance($values[$k - 1]['lat'], $values[$k - 1]['lng'], $value['lat'], $value['lng']);
                        }
                        $bus_station->distance = $distance_glo;
                    }

                    if ($status_distance == 1) {
                        $bus_station->distance = (float) $distance_re * 1000;
                        $distance_glo += $bus_station->distance;
                    }

                    $bus_station->direction = $direction;
                    $bus_station->station_order = $value['station_order'];



                    if ($audio_params) {

                        $bus_station_tmp = BusStation::orderBy('id', 'DESC')->first();
                        $middle = '';
                        if ($bus_station_tmp) {
                            $middle = (int) $bus_station_tmp->id + 1;
                        }
                        $bus_station->url_sound = $this->saveAudioBase64($audio_params, $middle) ?? null;
                    }

                    if ($bus_station->save()) {
                        $route_bus_station = new RoutesBusStation();
                        $route_bus_station->route_id = $route_id;
                        $route_bus_station->bus_station_id = $bus_station->id;
                        $route_bus_station->create_at = Carbon::now();
                        if ($route_bus_station->save()) {

                            $route_bus_station = $route_bus_station->toArray();
                            unset($route_bus_station['create_at']);

                            $push_log = [];
                            $push_log['action'] = 'create';
                            $push_log['company_id'] = $company_id;
                            $push_log['subject_id'] = $route_bus_station['id'];
                            $push_log['subject_type'] = 'route_bus_station';
                            $push_log['subject_data'] = $route_bus_station;
                            $this->push_logs->createPushLog($push_log);
                        }

                        //hadle save relative station link
                        $tmp_relative = [];
                        if (isset($value['station_relative']) && count($value['station_relative']) > 0) {
                            $tmp_relative = $value['station_relative'];
                            array_push($tmp_relative, $bus_station->id);
                            $bus_station_v2 = BusStation::where('id', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                        }

                        //hadle save station_relative for station other of route other
                        $up_busstation = BusStation::whereIn('id', $tmp_relative)->where('id', '!=', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                        if (isset($bus_station->station_relative)) unset($bus_station->station_relative);

                        $push_log = [];
                        $push_log['action'] = 'create';
                        $push_log['company_id'] = $company_id;
                        $push_log['subject_id'] = $bus_station->id;
                        $push_log['subject_type'] = 'bus_station';
                        $push_log['subject_data'] = $bus_station;
                        $this->push_logs->createPushLog($push_log);
                    }
                }
            }
            return response('Create success', 200);
        } else {
            return response('Create Error', 404);
        }
    }

    public function updateBusStations($bus_stations = array(), $route_id = 0, $company_id = 0)
    {

        if (!empty($bus_stations) && count($bus_stations) > 0 && $route_id > 0 && $company_id > 0) {

            usort($bus_stations, array($this, "cmp_station_order"));
            $bus_stations = collect($bus_stations)->groupBy('direction')->toArray();

            foreach ($bus_stations as $values) {

                $distance_glo = 0;
                $count_id_remove = 0;

                foreach ($values as $k => $value) {

                    $audio_params = $value['str_audio_base64'] ?? null;
                    $direction = $value['direction'] ?? 0;
                    $distance_re =  (float) $value['distance'] ?? 0;
                    $status_distance = null;
                    $isCheck_urlSound = isset($value['isCheck_urlSound']) ? (int) $value['isCheck_urlSound'] : null;

                    if (isset($value['statusDistance'])) {
                        $status_distance = (int) $value['statusDistance'];
                    }

                    if ($value['id'] < 0) {

                        $route_bus_stations = RoutesBusStation::where([
                            'route_id' => $route_id,
                            'bus_station_id' => abs($value['id'])
                        ])->get();

                        $route_bus_station = $route_bus_stations[0];
                        $push_log = [];
                        $push_log['action'] = 'delete';
                        $push_log['company_id'] = $company_id;
                        $push_log['subject_id'] = $route_bus_station->id;
                        $push_log['subject_type'] = 'route_bus_station';
                        $push_log['subject_data'] = null;
                        $this->push_logs->createPushLog($push_log);
                        $route_bus_station->delete();

                        $bus_station = BusStation::find(abs($value['id']));

                        $push_log = [];
                        $push_log['action'] = 'delete';
                        $push_log['company_id'] = $company_id;
                        $push_log['subject_id'] = $bus_station->id;
                        $push_log['subject_type'] = 'bus_station';
                        $push_log['subject_data'] = null;
                        $this->push_logs->createPushLog($push_log);

                        if ($bus_station) {

                            if ($bus_station->station_relative != null) {
                                $tmp_relative = json_decode($bus_station->station_relative);
                                if (in_array($bus_station->id, $tmp_relative)) {
                                    $index = array_search($bus_station->id, $tmp_relative);
                                    unset($tmp_relative[$index]);
                                }
                                $up_busstation = BusStation::whereIn('id', $tmp_relative)->where('id', '!=', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                            }
                            $bus_station->delete();
                            $this->removeAudioBase64($bus_station->url_sound);
                        }

                        $count_id_remove++;
                    } elseif ($value['id'] == 0) {

                        $bus_station = new BusStation();
                        $bus_station->name = $value['name'];
                        $bus_station->address = $value['address'];
                        $bus_station->position = new Point($value['lat'], $value['lng']);
                        $bus_station->direction = $direction;

                        if ($status_distance == 0) {

                            if ($k == 0) {
                                $distance_glo = 0;
                            } else {
                                if ($k - ($count_id_remove + 1) >= 0) {
                                    $distance_glo += $this->distance($values[$k - ($count_id_remove + 1)]['lat'], $values[$k - ($count_id_remove + 1)]['lng'], $value['lat'], $value['lng']);
                                } else {
                                    $distance_glo = 0;
                                }
                            }
                            $bus_station->distance = $distance_glo;
                        }
                        if ($status_distance == 1) {
                            $bus_station->distance = $distance_re * 1000;
                            $distance_glo += $bus_station->distance;
                        }
                        $bus_station->station_order = $value['station_order'];

                        if (!empty($audio_params)) {
                            $bus_station_tmp = BusStation::orderBy('id', 'DESC')->first();
                            $middle = '';
                            if ($bus_station_tmp) {
                                $middle = (int) $bus_station_tmp->id + 1;
                            }
                            $bus_station->url_sound = $this->saveAudioBase64($audio_params, $middle) ?? null;
                        }

                        if ($bus_station->save()) {

                            //hadle save relative station link
                            $tmp_relative = [];
                            if (isset($value['station_relative']) && count($value['station_relative']) > 0) {
                                $tmp_relative = $value['station_relative'];
                                array_push($tmp_relative, $bus_station->id);
                                $bus_station_v2 = BusStation::where('id', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                            }

                            //hadle save station_relative for station other of route other
                            $up_busstation = BusStation::whereIn('id', $tmp_relative)->where('id', '!=', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                            if (isset($bus_station->station_relative)) unset($bus_station->station_relative);

                            //set try count remove bus station
                            $count_id_remove = 0;

                            $route_bus_station = new RoutesBusStation();
                            $route_bus_station->route_id = $route_id;
                            $route_bus_station->bus_station_id = $bus_station->id;
                            $route_bus_station->create_at = Carbon::now();

                            if ($route_bus_station->save()) {

                                $route_bus_station = $route_bus_station->toArray();
                                unset($route_bus_station['create_at']);

                                $push_log = [];
                                $push_log['action'] = 'create';
                                $push_log['company_id'] = $company_id;
                                $push_log['subject_id'] = $route_bus_station['id'];
                                $push_log['subject_type'] = 'route_bus_station';
                                $push_log['subject_data'] = $route_bus_station;
                                $this->push_logs->createPushLog($push_log);
                            }

                            $push_log = [];
                            $push_log['action'] = 'create';
                            $push_log['company_id'] = $company_id;
                            $push_log['subject_id'] = $bus_station->id;
                            $push_log['subject_type'] = 'bus_station';
                            $push_log['subject_data'] = $bus_station;
                            $this->push_logs->createPushLog($push_log);
                        }
                    } else {

                        $bus_station = BusStation::find($value['id']);
                        $bus_station->name = $value['name'];
                        $bus_station->address = $value['address'];
                        $bus_station->position = new Point($value['lat'], $value['lng']);
                        $bus_station->direction = $direction;

                        if ($status_distance == 0 && !empty($status_distance)) {
                            if ($k == 0) {
                                $distance_glo = 0;
                            } else {
                                if ($k - ($count_id_remove + 1) >= 0) {
                                    $distance_glo += $this->distance($values[$k - ($count_id_remove + 1)]['lat'], $values[$k - ($count_id_remove + 1)]['lng'], $value['lat'], $value['lng']);
                                } else {
                                    $distance_glo = 0;
                                }
                            }
                            $bus_station->distance = $distance_glo;
                        }

                        if ($status_distance == 1 && !empty($status_distance)) {

                            $bus_station->distance = $distance_re * 1000;
                            $distance_glo += $bus_station->distance;
                        }

                        $bus_station->station_order = $value['station_order'];
                        $sound_tmp = $bus_station->url_sound;

                        if (!empty($audio_params)) {
                            $bus_station->url_sound = $this->saveAudioBase64($audio_params, $bus_station->id) ?? null;
                            if ($bus_station->url_sound) {
                                $this->removeAudioBase64($sound_tmp);
                            }
                        }

                        //is check remove url sound bus station
                        if ($isCheck_urlSound == 1) {
                            if ($bus_station->url_sound) {
                                $this->removeAudioBase64($bus_station->url_sound);
                                $bus_station->url_sound = null;
                            }
                        }

                        //hadle save relative station link
                        $tmp_relative = [];
                        $tmp_backup = ($bus_station->station_relative != null) ? json_decode($bus_station->station_relative) : [];

                        if (isset($value['station_relative']) && count($value['station_relative']) > 0) {

                            $tmp_relative = $value['station_relative'];
                            $bus_station->station_relative = (in_array($bus_station->id, $tmp_relative)) ? json_encode($tmp_relative) : NULL;
                        }

                        if ($bus_station->save()) {
                            //hadle save station_relative for station other of route other
                            if (count($tmp_backup) > 0) {
                                $arr_id_remove = [];
                                foreach ($tmp_backup as $v_tmp_backup) {
                                    if (!in_array($v_tmp_backup, $tmp_relative)) $arr_id_remove[] = $v_tmp_backup;
                                }
                                if (count($arr_id_remove) > 0) $up_busstation_v1 = BusStation::whereIn('id', $arr_id_remove)->update(['station_relative' => NULL]);
                            }
                            $up_busstation = BusStation::whereIn('id', $tmp_relative)->where('id', '!=', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                            if (isset($bus_station->station_relative)) unset($bus_station->station_relative);

                            //set try count remove bus station
                            $count_id_remove = 0;

                            $push_log = [];
                            $push_log['action'] = 'update';
                            $push_log['company_id'] = $company_id;
                            $push_log['subject_id'] = $bus_station->id;
                            $push_log['subject_type'] = 'bus_station';
                            $push_log['subject_data'] = $bus_station;
                            $this->push_logs->createPushLog($push_log);
                        }
                    }
                }
            }

            return response('Update success', 200);
        } else {

            return response('Update Error', 404);
        }
    }

    public function deleteBusStations($route_id, $company_id)
    {
        $route_bus_station = RoutesBusStation::where('route_id', $route_id)->get();
        RoutesBusStation::where('route_id', $route_id)->delete();
        foreach ($route_bus_station as $v) {
            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $v->id;
            $push_log['subject_type'] = 'route_bus_station';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);

            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $v->bus_station_id;
            $push_log['subject_type'] = 'bus_station';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);

            $bus_station = BusStation::where('id', $v->bus_station_id)->first();
            if ($bus_station) {
                if ($bus_station->station_relative != null) {
                    $tmp_relative = json_decode($bus_station->station_relative);
                    if (in_array($bus_station->id, $tmp_relative)) {
                        $index = array_search($bus_station->id, $tmp_relative);
                        unset($tmp_relative[$index]);
                    }
                    $up_busstation = BusStation::whereIn('id', $tmp_relative)->where('id', '!=', $bus_station->id)->update(['station_relative' => json_encode($tmp_relative)]);
                }
                $this->removeAudioBase64($bus_station->url_sound);
                $bus_station->delete();
            }
        }
        Route::where('id', $route_id)->delete();
    }

    public function getBusStationsById($route_id)
    {

        $route_bus_station = RoutesBusStation::where('route_id', $route_id)->get();
        $data = [];
        foreach ($route_bus_station as $v) {
            $data[] = BusStation::find($v->bus_station_id);
        }

        usort($data, array($this, "cmp_station_order"));
        return $data;
    }

    public function getDataBusStationById($id)
    {
        $bus_station = BusStation::find($id);
        return $bus_station;
    }

    public function getBusStationByNotIDAndInArray($arr_id, $id_not)
    {

        return BusStation::whereIn('bus_stations.id', $arr_id)
            ->where('bus_stations.id', '!=', $id_not)
            ->leftJoin('routes_bus_station', 'routes_bus_station.bus_station_id', '=', 'bus_stations.id')
            ->select('bus_stations.*', 'routes_bus_station.route_id')
            ->get()
            ->toArray();
    }

    // public function getDataBusStationAndRouteIdByArrayIdAndGroupKey($arr_id, $group_key){

    //     $bus_stations = BusStation::whereNotIn('id', $arr_id)
    //                 ->where('group_key','=', $group_key)
    //                 ->get();
    //     $result = [];
    //     foreach ($bus_stations as $bus_station) {

    //         $route_bus_station = RoutesBusStation::where('bus_station_id',$bus_station->id)->first();
    //         if(!empty($route_bus_station)){
    //             $station_obj = new \stdClass;
    //             $station_obj->route_id = (int)$route_bus_station->route_id;
    //             $station_obj->id = (int)$bus_station['id'];
    //             $station_obj->name = $bus_station['name'];
    //             $station_obj->station_order = (int)$bus_station['station_order'];
    //             $station_obj->group_key = $bus_station['group_key'];
    //             $station_obj->lat = $bus_station->position->getLat();
    //             $station_obj->lng = $bus_station->position->getLng();
    //             $result[] = $station_obj;
    //         }
    //     }
    //     return $result;
    // }



    //group bus stations
    public function listGroupBusStation($comapny_id, $data)
    {

        $limit = $data['limit'] ?? '';
        if (empty($limit) && $limit < 0) $limit = 10;

        $pagination = GroupBusStation::where('company_id', $comapny_id)->paginate($limit)->toArray();

        header("pagination-total: " . $pagination['total']);
        header("pagination-current: " . $pagination['current_page']);
        header("pagination-last: " . $pagination['last_page']);

        return $pagination['data'];
    }

    public function createGroupBusStation($data)
    {

        $key_flag = false;
        $key = '';
        while (!$key_flag) {
            $key = base64_encode(md5(uniqid()));
            if (!$this->getExistsGroupBusStationByKey($key)) {
                $key_flag = true;
            }
        }

        $isModuleCar = false;
        $module_app_arr = ModuleCompany::where('company_id', $data['company_id'])
            ->pluck('key_module')
            ->toArray();
        if (in_array('module_xe_khach', $module_app_arr)) $isModuleCar = true;

        $group_bus_station = new GroupBusStation();
        $group_bus_station->name = $data['name'];
        $group_bus_station->key = $key;
        $group_bus_station->company_id = $data['company_id'];
        $group_bus_station->ticket_price_id = $data['ticket_price_id'] ?? null;
        $group_bus_station->direction = $data['direction'] ?? 0;
        $group_bus_station->type = $data['type'] ?? null;
        $group_bus_station->parent_gr_bus_station_id = $data['parent_gr_bus_station_id'] ?? null;
        $group_bus_station->bus_stations = json_encode($data['bus_stations']);
        $group_bus_station->color = $data['color'] ?? null;

        if ($group_bus_station->save()) {

            if ($isModuleCar) {

                $ticket_price = $this->ticket_prices->getPriceById($group_bus_station->ticket_price_id);

                if($ticket_price){

                    $route_bus_station = RoutesBusStation::where('bus_station_id', $group_bus_station->parent_gr_bus_station_id)->first();
                    $group_bus_station->route_id  = $route_bus_station ? $route_bus_station->route_id : 0;
                    $group_bus_station->price  = $ticket_price->price;
                    $group_bus_station->ticket_type_id = $ticket_price->ticket_type_id;

                    unset($group_bus_station->updated_at);
                    unset($group_bus_station->created_at);
                    unset($group_bus_station->key);
                    unset($group_bus_station->ticket_price_id);
                    unset($group_bus_station->company_id);

                    $push_log = [];
                    $push_log['action'] = 'create';
                    $push_log['company_id'] = $data['company_id'];
                    $push_log['subject_id'] = $group_bus_station->id;
                    $push_log['subject_type'] = 'group_bus_station';
                    $push_log['subject_data'] = $group_bus_station;
                    $this->push_logs->createPushLog($push_log);
                }
            }
            return $group_bus_station;
        }
        return response('Create faild', 404);
    }

    public function deleteGroupBusStationById($id, $data)
    {

        $group_bus_station = GroupBusStation::find($id);

        $isModuleCar = false;
        $module_app_arr = ModuleCompany::where('company_id', $data['company_id'])
            ->pluck('key_module')
            ->toArray();
        if (in_array('module_xe_khach', $module_app_arr)) $isModuleCar = true;

        if ($group_bus_station) {

            if ($group_bus_station->delete()) {

                if ($isModuleCar) {

                    unset($group_bus_station->updated_at);
                    unset($group_bus_station->created_at);
                    unset($group_bus_station->key);

                    $push_log = [];
                    $push_log['action'] = 'delete';
                    $push_log['company_id'] = $data['company_id'];
                    $push_log['subject_id'] = $group_bus_station->id;
                    $push_log['subject_type'] = 'group_bus_station';
                    $push_log['subject_data'] = null;
                    $this->push_logs->createPushLog($push_log);
                }

                return response("OK", 200);
            }
        }
    }

    public function updateGroupBusStation($data)
    {

        $group_bus_station = GroupBusStation::find($data['id']);
        $group_bus_station->name = $data['name'];
        $group_bus_station->company_id = $data['company_id'];
        $group_bus_station->ticket_price_id = $data['ticket_price_id'] ?? null;
        $group_bus_station->bus_stations = json_encode($data['bus_stations']);
        $group_bus_station->direction = $data['direction'] ?? 0;
        $group_bus_station->type = $data['type'] ?? null;
        $group_bus_station->parent_gr_bus_station_id = $data['parent_gr_bus_station_id'] ?? null;
        $group_bus_station->color = $data['color'] ?? null;

        $isModuleCar = false;
        $module_app_arr = ModuleCompany::where('company_id', $data['company_id'])
            ->pluck('key_module')
            ->toArray();
        if (in_array('module_xe_khach', $module_app_arr)) $isModuleCar = true;

        if ($group_bus_station->save()) {

            if ($isModuleCar) {

                $ticket_price = $this->ticket_prices->getPriceById($group_bus_station->ticket_price_id);

                if($ticket_price){

                    $route_bus_station = RoutesBusStation::where('bus_station_id', $group_bus_station->parent_gr_bus_station_id)->first();
                    $group_bus_station->route_id  = $route_bus_station ? $route_bus_station->route_id : 0;
                    $group_bus_station->price  = $ticket_price->price;
                    $group_bus_station->ticket_type_id = $ticket_price->ticket_type_id;

                    unset($group_bus_station->updated_at);
                    unset($group_bus_station->created_at);
                    unset($group_bus_station->key);
                    unset($group_bus_station->ticket_price_id);
                    unset($group_bus_station->company_id);

                    $push_log = [];
                    $push_log['action'] = 'update';
                    $push_log['company_id'] = $data['company_id'];
                    $push_log['subject_id'] = $group_bus_station->id;
                    $push_log['subject_type'] = 'group_bus_station';
                    $push_log['subject_data'] = $group_bus_station;
                    $this->push_logs->createPushLog($push_log);
                }
            }
            return $group_bus_station;
        }
        return response('Update faild', 404);
    }

    public function getGroupBusStationById($id)
    {
        return GroupBusStation::find($id);
    }

    public function getExistsGroupBusStationByKey($key)
    {
        return GroupBusStation::where('key', $key)->exists();
    }

    //function sort
    public function cmp_station_order($a, $b)
    {
        $a = (object) $a;
        $b = (object) $b;
        return ($a->station_order < $b->station_order) ? -1 : 1;
    }

    //funcion for app
    public function updateBusStationByCompanyIdForApp($token, $data)
    {

        $company_id = $data['companyId'] ?? null;
        $partner_code = $data['partnerCode'] ?? null;
        $data_hash = $data['dataHash'] ?? null;

        if (empty($company_id)) {
            return ['status' => 404, 'message' => 'Tham số mã công ty không tìm thấy', 'data' => []];
        }
        if (empty($partner_code)) {
            return ['status' => 404, 'message' => 'Tham số mã đối tác Không tìm thấy', 'data' => []];
        }
        if (count($data_hash) == 0 || empty($data_hash)) {
            return ['status' => 404, 'message' => 'Tham số chuỗi mã hóa tìm thấy tham số hoặc dữ liệu không có', 'data' => []];
        }

        $partner = $this->partner_codes->getPartnerByPartnerCode($partner_code);

        if (empty($partner)) {
            return ['status' => 404, 'message' => 'Mã đối tác không tồn tại', 'data' => []];
        }

        $app_key = $partner->app_key;

        $token_de = json_decode($this->public_functions->deCrypto($token, $partner_code));

        if (empty($token_de)) {
            return ['status' => 404, 'message' => 'Giải mã token thất bại', 'data' => []];
        }

        $app_key_de = $token_de->appKey ?? null;

        if (empty($app_key_de)) {
            return ['status' => 404, 'message' => 'Giải mã token thất bại', 'data' => []];
        }
        if ($app_key_de != $app_key) {
            return ['status' => 404, 'message' => 'Giải mã token thất bại', 'data' => []];
        }

        $results = [];

        foreach ($data_hash as $key => $value) {

            $data_de = $this->public_functions->deCrypto($value, $app_key);
            $data_json = json_decode($data_de, true);

            if (!$data_json || ($data_json == null)) {
                return ['status' => 404, 'message' => 'Giải mã hash thất bại', 'data' => []];
            }

            $id = (int) $data_json['id'] ?? null;
            $route_id = (int) $data_json['route_id'] ?? null;
            $lat = (float) $data_json['lat'] ?? null;
            $lng = (float) $data_json['lng'] ?? null;
            $station_order = (int) $data_json['station_order'] ?? null;
            $direction = (int) $data_json['direction'] ?? null;

            if (
                empty($lat) || empty($lng)
            ) {
                return ['status' => 404, 'message' => 'Giải mã hash thất bại', 'data' => []];
            }

            $bus_station = BusStation::find($id);

            if (!empty($bus_station)) {

                $bus_station->position = new Point($lat, $lng);

                if ($bus_station->save()) {

                    $push_log = [];
                    $push_log['action'] = 'update';
                    $push_log['company_id'] = $company_id;
                    $push_log['subject_id'] = $bus_station->id;
                    $push_log['subject_type'] = 'bus_station';
                    $push_log['subject_data'] = $bus_station;
                    $this->push_logs->createPushLog($push_log);

                    $results[] = $bus_station;
                }
            }
        }

        return ['status' => 200, 'message' => 'Cập nhật dữ liệu thành công', 'data' => $results];


        // ----------------version 1
        // $route_bus_station = RoutesBusStation::where('route_id',$route_id)->get();
        // $bus_stations = [];
        // foreach ($route_bus_station as $v) {
        //     $bus_station = BusStation::where("id", $v->bus_station_id)
        //                 ->where("direction", $direction)
        //                 ->where("station_order", ">=", ((int)$station_order - 1))
        //                 ->first();
        //
        //     if(!empty($bus_station)){
        //     $bus_station->lat = $bus_station->position->getLat();
        //     $bus_station->lng = $bus_station->position->getLng();
        //     unset( $bus_station->position);
        //         $bus_stations[] = $bus_station;
        //     }
        // }

        // usort($bus_stations, array($this, "cmp_station_order"));

        // if(count($bus_stations) > 0){

        //     $distance_glo = 0;

        //     foreach ($bus_stations as $key => $value) {

        //         if ($key == 0) {
        //             $distance_glo = $value->distance;
        //         } else {

        //             $distance_glo += $this->distance($bus_stations[$key-1]->lat, $bus_stations[$key-1]->lng, $value->lat, $value->lng);
        //             $value->distance = $distance_glo;

        //             if((int)$id == (int)$value->id){
        //                 $value->position = new Point($lat, $lng);
        //             }

        //             $value->save();
        //         }
        //     }

        //     return ['status' => 200, 'message' => 'Cập nhật dữ liệu thành công', 'data' => []];
        // }

        //--------------------version 2
    }

    public function searchGroupBusStation($data)
    {
        $name = $data['name'] ?? '';
        $comapny_id = $data['company_id'];
        return GroupBusStation::where('company_id', $comapny_id)->where('name', 'like', "%$name%")->get()->toArray();
    }
}
