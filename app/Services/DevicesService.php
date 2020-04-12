<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\Transaction;
use App\Services\PushLogsService;
use App\Services\DevicesModelService;
use App\Services\IssuedsService;
use App\Services\CompaniesService;
use App\Services\UsersService;
use App\Services\VehiclesService;
use App\Services\BusStationsService;

use Log;
use App\Models\Shift;
use Carbon\Carbon;

class DevicesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\DevicesModelService
     */
    protected $devices_model;

    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

     /**
     * @var App\Services\CompaniesService
     */
    protected $companies;


    public function __construct(
        PushLogsService $push_logs,
        DevicesModelService $devices_model,
        IssuedsService $issueds,
        CompaniesService $companies,
        UsersService $users,
        VehiclesService $vehicles,
        BusStationsService $bus_stations
        )
    {
        $this->push_logs = $push_logs;
        $this->devices_model = $devices_model;
        $this->issueds = $issueds;
        $this->companies = $companies;
        $this->users = $users;
        $this->vehicles = $vehicles;
        $this->bus_stations = $bus_stations;
    }

    public function checkExistsById($id)
    {
        return Device::where('id', $id)->exists();
    }

    public function getShiftById($id)
    {
        return Shift::where('id', $id)->first();
    }

    public function createDevice($data)
    {
        if(!$this->devices_model->checkExistsById($data['device_model_id'])) return response('Device Model Not found', 404);
        if($this->checkExistsByIdentity($data['identity'])) return response('Device identity exists', 404);

        $device = new Device;
        $device->identity = $data['identity'];
        $device->device_model_id = $data['device_model_id'];
        $device->is_running = 0;
        $device->version = 1;

        if ($device->save()) return $this->getDeviceById($device['id']);

        return response('Create error', 404);
    }

    public function updateDevice($data)
    {
        $identity = $data['identity'];

        // Log::info('updateDevice:'.json_encode($data));
        if (!$this->devices_model->checkExistsById($data['device_model_id']))  return response('Device Model Not found', 404);

        $device = $this->getDeviceById($data['id']);

        if (empty($device))return response('Not found', 404);

        if($this->checkExistsByIdentity($identity)) return response('Identity is exists', 404);

        $device->identity = $data['identity'];
        $device->device_model_id = $data['device_model_id'];

        if ($device->save()) return $device;

        return response('Update error', 404);
    }

    public function deleteDevice($id)
    {
        // get Device
        $device = $this->getDeviceById($id);

        if (empty($device))
            return response('Not found', 404);

        if ($device->delete())
            return response('OK', 200);

        return response('Delete error', 404);
    }

    public function getDeviceByIdentitySearch($identity, $company_id = null)
    {

        $devices_result = [];

        if (empty($company_id)) {
            $device_search = Device::where('identity', 'like', '%' . $identity . '%')
                ->with('deviceModel', 'issueds')
                ->orderBy('is_running', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            if (count($device_search) > 0) {

                foreach ($device_search as $device) {

                    if (count($device['issueds']) > 0) {

                        $company_id = $device['issueds'][0]['company_id'];
                        $company = $this->companies->getCompanyById($company_id);
                        if ($company) $device['company'] = $company;
                    }
                    $devices_result[] = $device;
                }
            }
        } else {

            $device_search = Device::select(
                'devices.*',
                'shifts.id as shift_id',
                'shifts.started',
                'shifts.device_id',
                'shifts.user_id',
                'shifts.subdriver_id',
                'shifts.station_id',
                'shifts.shift_token',
                'shifts.shift_destroy'
                )
                ->join('issued', 'devices.id', '=', 'issued.device_id')
                ->leftJoin('shifts', function ($join) {
                    $join->on('devices.id', '=', 'shifts.device_id')
                        ->whereNull('shifts.ended');
                })
                ->where('issued.company_id', '=', $company_id)
                ->where('identity', 'like', '%' . $identity . '%')
                ->whereNull('issued.deleted_at')
                ->with('deviceModel')
                ->orderBy('is_running', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            foreach ($device_search as $keys => $values) {

                $status_device = json_decode($values['status_device']);
                $tmp = [
                    'id' => $values['id'],
                    'device_model_id' => $values['device_model_id'],
                    'identity' => $values['identity'],
                    'version' => $values['version'],
                    'is_running' => $values['is_running'],
                    'created_at' => $values['created_at'],
                    'updated_at' => $values['updated_at'],
                    'position' => $values['position'],
                    'status_device' => $status_device,
                    'device_model' => $values['device_model'],
                    'device_info' => [],
                    'device_id'=> $values['device_id'],
                    'shift_id'=>$values['shift_id'],
                    'started'=>$values['started'],
                    'station_id'=> $values['station_id'],
                    'subdriver_id'=> $values['subdriver_id'],
                    'user_id'=>$values['user_id'],
                    'shift_token' => $values['shift_token'],
                    'shift_destroy' => $values['shift_destroy']
                ];
                if (isset($status_device)) {
                    foreach ($status_device as $key => $value) {
                        $device_info = DB::table('device_info')->where('id', $value)->first();
                        switch ($device_info->id) {
                            case 1:
                                $device_info->class = 'fa fa-500px tx-success fa-lg';
                                break;
                            case 2:
                                $device_info->class = 'fa fa-500px fa-lg';
                                break;
                            case 3:
                                $device_info->class = 'fa fa-bluetooth tx-success fa-lg';
                                break;
                            case 4:
                                $device_info->class = 'fa fa-bluetooth fa-lg';
                                break;
                            case 5:
                                $device_info->class = 'fa fa-bluetooth tx-warning fa-lg';
                                break;
                            case 6:
                                $device_info->class = 'fa fa-signal tx-warning fa-lg';
                                break;
                            case 7:
                                $device_info->class = 'fa fa-battery-quarter tx-warning fa-lg';
                                break;
                            case 8:
                                $device_info->class = 'fa fa-newspaper-o tx-warning fa-lg';
                                break;
                            case 9:
                                $device_info->class = 'fa fa-print tx-success fa-lg';
                                break;
                            case 10:
                                $device_info->class = 'fa fa-print fa-lg';
                                break;
                            case 11:
                                $device_info->class = 'fa fa-print tx-warning fa-lg';
                                break;
                            case 12:
                                $device_info->class = 'fa fa-qrcode tx-success fa-lg';
                                break;
                            case 13:
                                $device_info->class = 'fa fa-qrcode fa-lg';
                                break;
                            case 14:
                                $device_info->class = 'fa fa-barcode tx-success fa-lg';
                                break;
                            case 15:
                                $device_info->class = 'fa fa-barcode fa-lg';
                                break;
                        }
                        $tmp['device_info'][] = (array) $device_info;
                    }
                }
                $devices_result[] = $tmp;
            }
        }
        return $devices_result;
    }

    public function listDevices($data)
    {
        $limit = $data['limit'];
        if (empty($limit) && $limit < 0)
            $limit = 10;

        if (empty($data['company_id'])) {
            $pagination = Device::with('deviceModel', 'issueds')
                ->orderBy('is_running', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($limit)
                ->toArray();
            $devices_arr = [];

            if (count($pagination['data']) > 0) {

                foreach ($pagination['data'] as $device) {

                    if (count($device['issueds']) > 0) {

                        $company_id = $device['issueds'][0]['company_id'];

                        // get company
                        $company = $this->companies->getCompanyById($company_id);

                        if ($company) {
                            $device['company'] = $company;
                        }
                    }
                    array_push($devices_arr, $device);
                }
            }

            header("pagination-total: " . $pagination['total']);
            header("pagination-current: " . $pagination['current_page']);
            header("pagination-last: " . $pagination['last_page']);

            return $devices_arr;
        } else {

            $company_id = $data['company_id'];
            $result = [];

            $pagination = Device::select('devices.*', 'shifts.id as shift_id', 'shifts.started','shifts.device_id', 'shifts.user_id', 'shifts.subdriver_id', 'shifts.station_id','shifts.shift_token'
            ,'shifts.shift_destroy')
                ->join('issued', 'devices.id', '=', 'issued.device_id')
                ->leftJoin('shifts', function ($join) {
                    $join->on('devices.id', '=', 'shifts.device_id')
                        ->whereNull('shifts.ended');
                })
                ->where('issued.company_id', '=', $company_id)
                ->whereNull('issued.deleted_at')
                ->with('deviceModel')
                ->orderBy('is_running', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($limit)
                ->toArray();

            header("pagination-total: " . $pagination['total']);
            header("pagination-current: " . $pagination['current_page']);
            header("pagination-last: " . $pagination['last_page']);

            foreach ($pagination['data'] as $keys => $values) {

                $status_device = (json_decode($values['status_device']));

                $tmp = [
                    'id' => $values['id'],
                    'device_model_id' => $values['device_model_id'],
                    'identity' => $values['identity'],
                    'version' => $values['version'],
                    'is_running' => $values['is_running'],
                    'created_at' => $values['created_at'],
                    'updated_at' => $values['updated_at'],
                    'position' => $values['position'],
                    'status_device' => $status_device,
                    'device_model' => $values['device_model'],
                    'device_info' => [],
                    'device_id'=> $values['device_id'],
                    'shift_id'=>$values['shift_id'],
                    'started'=>$values['started'],
                    'station_id'=>$values['station_id'],
                    'subdriver_id'=>$values['subdriver_id'],
                    'user_id'=>$values['user_id'],
                    'shift_token' => $values['shift_token'],
                    'shift_destroy' => $values['shift_destroy']
                ];

                if (isset($status_device)) {
                    foreach ($status_device as $key => $value) {
                        $device_info = DB::table('device_info')->where('id', $value)->first();
                        switch ($device_info->id) {
                            case 1:
                                $device_info->class = 'fa fa-500px tx-success fa-lg';
                                break;
                            case 2:
                                $device_info->class = 'fa fa-500px fa-lg';
                                break;
                            case 3:
                                $device_info->class = 'fa fa-bluetooth tx-success fa-lg';
                                break;
                            case 4:
                                $device_info->class = 'fa fa-bluetooth fa-lg';
                                break;
                            case 5:
                                $device_info->class = 'fa fa-bluetooth tx-warning fa-lg';
                                break;
                            case 6:
                                $device_info->class = 'fa fa-signal tx-warning fa-lg';
                                break;
                            case 7:
                                $device_info->class = 'fa fa-battery-quarter tx-warning fa-lg';
                                break;
                            case 8:
                                $device_info->class = 'fa fa-newspaper-o tx-warning fa-lg';
                                break;
                            case 9:
                                $device_info->class = 'fa fa-print tx-success fa-lg';
                                break;
                            case 10:
                                $device_info->class = 'fa fa-print fa-lg';
                                break;
                            case 11:
                                $device_info->class = 'fa fa-print tx-warning fa-lg';
                                break;
                            case 12:
                                $device_info->class = 'fa fa-qrcode tx-success fa-lg';
                                break;
                            case 13:
                                $device_info->class = 'fa fa-qrcode fa-lg';
                                break;
                            case 14:
                                $device_info->class = 'fa fa-barcode tx-success fa-lg';
                                break;
                            case 15:
                                $device_info->class = 'fa fa-barcode fa-lg';
                                break;
                        }
                        $tmp['device_info'][] = (array) $device_info;
                    }
                }
                array_push($result, $tmp);
            }
            return $result;
        }
    }

    public function getDeviceById($id)
    {
        return Device::where('id', $id)
                    ->with('deviceModel')->first();
    }

    public function getDeviceByIsRunning($is_running, $company_id)
    {
        if($company_id){

            return  Device::select('devices.*')
            ->join('issued', 'devices.id', '=', 'issued.device_id')
            ->where('issued.company_id', '=', $company_id)
            ->where('is_running', $is_running)
            ->whereNull('issued.deleted_at')
            ->orderBy('is_running', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        }
    }

    public function getDeviceByIdentity($identity)
    {
        return Device::where('identity', $identity)
                    ->with('deviceModel')
                    ->with('issueds')
                    ->first();
    }

    public function checkExistsByIdentity($identity){
        return Device::where('identity', $identity)
                        ->exists();
    }

    public function updateIsRunning($device_id, $type)
    {
        $device = $this->getDeviceById($device_id);

        if ($device) {

            $device->is_running = $type;
            if ($device->save()) {
                return true;
            }
        }

        return false;
    }

    public function getStartUserLoginShiftById($id){
        return Shift::find($id);
    }

    public function getRevenueChartByShift($data)
    {
        $shift_id = (int)$data['shift_id'];
        $company_id = $data['company_id'];
        $type_opt = $data['type_opt'];
        $req_transaction_id = isset($data['tran_id']) ? (int)$data['tran_id'] : null;

        $title_chart = [];
        $rep_transaction_id = null;

        $shift = Shift::where('id','=', $shift_id)
                ->where('shift_token', '!=', null)
                ->where('shift_destroy', '!=', 1)
                ->with('device', 'vehicle')
                ->first();

        if(empty($shift)) return  response('Shift Not found', 404);

        $title_chart[] = $shift['vehicle']->license_plates;
        $title_chart[] = $shift['device']->identity;

        // get user driver and user subdriver
        $user = (!empty($shift->user_id)) ? $this->users->getUserByKey('id', $shift->user_id) : null;
        $sub_user = (!empty($shift->subdriver_id)) ? $this->users->getUserByKey('id', $shift->subdriver_id) : null;
        if ($user != null)  $title_chart[] =  $user->fullname;
        if ($sub_user != null)  $title_chart[] =  $sub_user->fullname;

        $title_chart[] =  Carbon::parse($shift->started)->format('d/m/Y H:i:s');

        if (count($title_chart) > 0 && count($title_chart) == 1) $title_chart = $title_chart[0];
        else $title_chart = implode(' - ', $title_chart);

        $result = [];
        switch ($type_opt) {
          case 1:
            $data_ticket_types = DB::select('
                SELECT transactions.shift_id,  DATE_FORMAT(transactions.activated,"%d/%m/%Y %H:%i:%s") AS datetime,
                transactions.amount as price, transactions.type, bus_stations.name as bus_station_name
                FROM transactions
                LEFT JOIN bus_stations
                ON bus_stations.id = transactions.station_id
                WHERE transactions.type IN ("pos","charge","qrcode") AND transactions.shift_id = '. $shift_id.'
                ORDER BY transactions.activated
            ');

            if(count($data_ticket_types) > 0) $result = $data_ticket_types;
            break;

          case 2:

            $rep_transaction = Transaction::where('shift_id', $shift_id)->orderBy('activated', 'DESC')->first();
            $data_revenue = [];

            if(empty($req_transaction_id) || $req_transaction_id == 0){

                $data_revenue = DB::select('
                    SELECT DATE_FORMAT(MIN(activated),"%d/%m/%Y %H:%i:%s") AS datetime, sum(amount) as sub_total
                    FROM transactions
                    WHERE type IN ("pos","charge","qrcode") AND shift_id = '. $shift_id.'
                    GROUP BY ROUND(UNIX_TIMESTAMP(activated) / 90)
                    ORDER BY ROUND(UNIX_TIMESTAMP(activated) / 90)
                ');
            }else{
                $data_revenue = DB::select('
                    SELECT DATE_FORMAT(MIN(activated),"%d/%m/%Y %H:%i:%s") AS datetime, sum(amount) as sub_total
                    FROM transactions
                    WHERE type IN ("pos","charge","qrcode") AND shift_id = '. $shift_id.'
                    AND id > '.$req_transaction_id.'
                    GROUP BY ROUND(UNIX_TIMESTAMP(activated) / 90)
                    ORDER BY ROUND(UNIX_TIMESTAMP(activated) / 90)
                ');
            }

            if (count($data_revenue) > 0) {
                // $result = $data_revenue;
                $total = 0;
                foreach ($data_revenue as $revenue) {
                    $total += $revenue->sub_total;
                    $revenue_tmp['datetime'] = $revenue->datetime;
                    $revenue_tmp['sub_total'] = $revenue->sub_total;
                    $revenue_tmp['total'] = $total;
                    $result[] = $revenue_tmp;
                }
            }
            break;

          case 3:

            $data_deposit = DB::select('
                SELECT transactions.shift_id,  DATE_FORMAT(transactions.activated,"%d/%m/%Y %H:%i:%s") AS datetime,
                transactions.amount as price, transactions.type, bus_stations.name as bus_station_name
                FROM transactions
                LEFT JOIN bus_stations
                ON bus_stations.id = transactions.station_id
                WHERE transactions.type IN ("deposit","deposit_month") AND transactions.shift_id = '. $shift_id.'
                ORDER BY transactions.activated
            ');

            if(count($data_deposit) > 0) $result = $data_deposit;
            break;

          case 4:

            $data_ticket_types =DB::select('
                SELECT transactions.shift_id,  DATE_FORMAT(transactions.activated,"%d/%m/%Y %H:%i:%s") AS datetime,
                transactions.amount as price, transactions.type, bus_stations.name as bus_station_name
                FROM transactions
                LEFT JOIN bus_stations
                ON bus_stations.id = transactions.station_id
                WHERE transactions.type IN ("pos_goods","charge_goods","qrcode_goods") AND transactions.shift_id = '. $shift_id.'
                ORDER BY transactions.activated
            ');

            if(count($data_ticket_types) > 0) $result = $data_ticket_types;
            break;

          case 5:

            $data_ticket_types =DB::select('
                SELECT transactions.shift_id,  DATE_FORMAT(transactions.activated,"%d/%m/%Y %H:%i:%s") AS datetime,
                transactions.amount as price, transactions.type, bus_stations.name as bus_station_name
                FROM transactions
                LEFT JOIN bus_stations
                ON bus_stations.id = transactions.station_id
                WHERE transactions.type IN ("charge_free","charge_month") AND transactions.shift_id = '. $shift_id.'
                ORDER BY transactions.activated
            ');

            if(count($data_ticket_types) > 0) $result = $data_ticket_types;
            break;
        }

        $chart = [];
        $chart['title_chart'] = $title_chart;
        $chart['shift_id'] = $shift_id;
        $chart['msg'] = $result;
        $chart['tran_id'] = !empty($rep_transaction) ? $rep_transaction->id : null;
        return $chart;
    }

    public function updateStatusDevice($identity, $data){

        $device = $this->getDeviceByIdentity($identity);
        if (empty($device))return response('Device not found', 404);

        $updated_at = date("Y-m-d H:i:s", $data['timestamp']);
        $device->status_device = $data['status_device'];
        $device->updated_at =  $updated_at ;

        if ($device->save()) return $device;

        return response('Update error', 404);
    }
}
