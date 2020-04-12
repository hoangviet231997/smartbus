<?php

namespace App\Services;

use ___PHPSTORM_HELPERS\object;
use App\Services\UsersService;
use App\Services\DevicesService;
use App\Services\ShiftsService;
use App\Services\RoutesService;
use App\Services\VehiclesService;
use App\Services\TransactionsService;
use App\Services\TicketTypesService;
use App\Services\TicketAllocatesService;
use App\Services\TicketPricesService;
use App\Services\CompaniesService;
use App\Services\RolesService;
use App\Services\BusStationsService;
use App\Services\RfidCardsService;
use App\Services\MembershipsService;
use App\Services\MembershipTypeService;
use App\Services\ModuleCompanyService;
use App\Services\HistoryShiftService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\RfidCard;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\ShiftSupervisor;
use App\Models\User;
use DB;
use Illuminate\Support\Arr;
use stdClass;

class ReportsService
{
    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\RoutesService
     */
    protected $routes;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;

    /**
     * @var App\Services\TicketTypesService
     */
    protected $ticket_types;

    /**
     * @var App\Services\TicketAllocatesService
     */
    protected $ticket_allocates;

    /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_prices;

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * @var App\Services\RolesService
     */
    protected $roles;

    /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations;

    /**
     * @var App\Services\RfidCardsService
     */
    protected $rfidcards;

    /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;

    /**
     * @var App\Services\MembershipTypeService
     */
    protected $membership_types;

    /**
     * @var App\Services\ModuleCompanyService
     */
    protected $module_apps;

    /**
     * @var App\Services\HistoryShiftService
     */
    protected $history_shifts;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    public function __construct(
        UsersService $users,
        ShiftsService $shifts,
        RoutesService $routes,
        VehiclesService $vehicles,
        TransactionsService $transactions,
        TicketTypesService $ticket_types,
        TicketAllocatesService $ticket_allocates,
        TicketPricesService $ticket_prices,
        CompaniesService $companies,
        RolesService $roles,
        BusStationsService $bus_stations,
        RfidCardsService $rfidcards,
        MembershipsService $memberships,
        ModuleCompanyService $module_apps,
        HistoryShiftService $history_shifts,
        MembershipTypeService $membership_types,
        DevicesService $devices
    ) {
        $this->users = $users;
        $this->shifts = $shifts;
        $this->routes = $routes;
        $this->vehicles = $vehicles;
        $this->transactions = $transactions;
        $this->ticket_types = $ticket_types;
        $this->ticket_prices = $ticket_prices;
        $this->ticket_allocates = $ticket_allocates;
        $this->companies = $companies;
        $this->roles = $roles;
        $this->bus_stations = $bus_stations;
        $this->rfidcards = $rfidcards;
        $this->memberships = $memberships;
        $this->membership_types = $membership_types;
        $this->module_apps = $module_apps;
        $this->history_shifts = $history_shifts;
        $this->devices = $devices;
    }

    // --------------------------------------view ---------------------------------------//
    public function viewAllReceipt($data)
    {
        $company_id = $data['company_id'];
        $start_date = date("Y-m-d 00:00:00", strtotime($data['date']));
        $end_date = date("Y-m-d 23:59:59", strtotime($data['date']));
        $shifts_arr = [];
        // get shifts
        $shifts = Shift::leftJoin('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
            ->leftJoin('routes', 'routes.id', '=', 'shifts.route_id')
            ->leftJoin('bus_stations', 'bus_stations.id', '=', 'shifts.station_id')
            ->leftJoin('devices', 'devices.id', '=', 'shifts.device_id')
            ->where([
                ['shifts.ended', '>=', $start_date],
                ['shifts.ended', '<=', $end_date],
                ['shifts.ended', '!=', NULL],
                ['shifts.shift_destroy', '!=', 1],
                ['vehicles.company_id', '=', $company_id]
            ])
            ->select(
                'shifts.*',
                'routes.name as route_name',
                'devices.identity as identity',
                'vehicles.license_plates as license_plates',
                'bus_stations.name as from_station'
            )->orderBy('shifts.ended')->get();

        if (count($shifts) > 0) {

            foreach ($shifts as $shift) {

                // datetime
                $started = date("d-m-Y H:i:s", strtotime($shift->started));
                $ended = empty($shift->ended) ? '' : date("d-m-Y H:i:s", strtotime($shift->ended));
                $date_time = $started . ' <=> ' . $ended;
                $total_amount = $shift->total_amount;

                $shifts_tmp = [
                    'shift_id' => $shift->id,
                    'license_plates' => $shift->license_plates ?? '',
                    'driver_name' => '',
                    'subdriver_name' => '',
                    'route_name' => $shift->route_name ?? '',
                    'route_id' => $shift->route_id,
                    'from_station' => $shift->from_station ?? '',
                    'date_time' => $date_time,
                    'total_price' => 0,
                    'total_deposit' => 0,
                    'total_charge' => 0,
                    'total_goods' => 0,
                    'collected' => $shift->collected,
                    'shift_destroy' => $shift->shift_destroy,
                    'hidden' => $shift->hidden,
                    'is_amount' => 0,
                    'identity' => $shift->identity ?? ''
                ];

                //get driver
                if (round($shift->user_id) > 0) {
                    $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $shifts_tmp['driver_name'] = $driver['fullname'];
                }

                // get subdriver
                if (round($shift->subdriver_id) > 0) {
                    $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $shifts_tmp['subdriver_name'] = $subdriver['fullname'];
                }

                // get total price
                $transactions = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id]
                    // ['ticket_destroy', '!=', 1]
                ]);

                if (count($transactions) > 0) {

                    $shifts_tmp['is_amount'] = 1;

                    foreach ($transactions as $transaction) {

                        if($transaction->ticket_destroy != 1){
                            if ((float) $transaction->amount > 0) {
                                if ($transaction->type == 'charge') $shifts_tmp['total_charge'] += (float) $transaction->amount;
                                if ($transaction->type == 'deposit' || $transaction->type == 'deposit_month') $shifts_tmp['total_deposit'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos') $shifts_tmp['total_price'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos_goods') $shifts_tmp['total_goods'] += (float) $transaction->amount;
                            }
                        }else{
                            $total_amount -= $transaction->amount;
                        }
                    }
                }

                $shifts_tmp['total_amount'] = $total_amount;
                $shifts_arr[] = $shifts_tmp;
            }
        }
        return $shifts_arr;
    }

    public function viewNotCollectMoneyReceipt($data)
    {
        $company_id = $data['company_id'];
        $orWheres = [];

        if (!$data['user_id']) {
            $start_date = date("Y-m-d 00:00:00", strtotime($data['date']));
            $end_date = date("Y-m-d 23:59:59", strtotime($data['date_to']));
            $orWheres = [
                ['shifts.ended', '>=', $start_date],
                ['shifts.ended', '<=', $end_date],
                ['shifts.ended', '!=', NULL],
                ['shifts.shift_destroy', '!=', 1],
                ['vehicles.company_id', '=', $company_id],
                ['shifts.collected', '=', 0]
            ];
        } else {
            $user_id = $data['user_id'];
            $company_id = $data['company_id'];
            // get user
            $user = $this->users->getUserByKey('id', $user_id, $company_id);

            if (empty($user)) {
                return response('User not found.', 404);
            }
            // convert date
            $role_name = $user->role->name;
            $orWheres = [
                ['shifts.ended', '!=', NULL],
                ['shifts.collected', '=', 0],
                ['shifts.shift_destroy', '!=', 1],
                ['vehicles.company_id', '=', $company_id],
            ];

            if ($role_name == 'driver') {
                array_push($orWheres, ['shifts.user_id', $user_id]);
            } elseif ($role_name == 'subdriver') {
                array_push($orWheres, ['shifts.subdriver_id', $user_id]);
            } else
                return response('Invalid user', 404);
        }

        $shifts = Shift::leftJoin('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
            ->leftJoin('routes', 'routes.id', '=', 'shifts.route_id')
            ->leftJoin('bus_stations', 'bus_stations.id', '=', 'shifts.station_id')
            ->leftJoin('devices', 'devices.id', '=', 'shifts.device_id')
            ->where($orWheres)
            ->select(
                'shifts.*',
                'routes.name as route_name',
                'devices.identity as identity',
                'vehicles.license_plates as license_plates',
                'bus_stations.name as from_station'
            )->orderBy('shifts.ended')->get();

        // get shifts
        $shifts_arr = [];

        if (count($shifts) > 0) {

            foreach ($shifts as $shift) {

                // datetime
                $started = date("d-m-Y H:i:s", strtotime($shift->started));
                $ended = empty($shift->ended) ? '' : date("d-m-Y H:i:s", strtotime($shift->ended));
                $date_time = $started . ' <=> ' . $ended;
                $total_amount = $shift->total_amount;

                $shifts_tmp = [
                    'shift_id' => $shift->id,
                    'license_plates' => $shift->license_plates ?? '',
                    'driver_name' => '',
                    'subdriver_name' => '',
                    'route_name' => $shift->route_name ?? '',
                    'route_id' => $shift->route_id,
                    'from_station' => $shift->from_station ?? '',
                    'date_time' => $date_time,
                    'total_price' => 0,
                    'total_deposit' => 0,
                    'total_charge' => 0,
                    'total_goods' => 0,
                    'collected' => $shift->collected,
                    'shift_destroy' => $shift->shift_destroy,
                    'hidden' => $shift->hidden,
                    'is_amount' => 0,
                    'identity' => $shift->identity ?? ''
                ];

                //get driver
                if (round($shift->user_id) > 0) {
                    $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $shifts_tmp['driver_name'] = $driver['fullname'];
                }

                // get subdriver
                if (round($shift->subdriver_id) > 0) {
                    $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $shifts_tmp['subdriver_name'] = $subdriver['fullname'];
                }

                // get total price
                $transactions = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id]
                    // ['ticket_destroy', '!=', 1]
                ]);

                if (count($transactions) > 0) {

                    $shifts_tmp['is_amount'] = 1;

                    foreach ($transactions as $transaction) {

                        if($transaction->ticket_destroy != 1){
                            if ((float) $transaction->amount > 0) {
                                if ($transaction->type == 'charge') $shifts_tmp['total_charge'] += (float) $transaction->amount;
                                if ($transaction->type == 'deposit' || $transaction->type == 'deposit_month') $shifts_tmp['total_deposit'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos') $shifts_tmp['total_price'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos_goods') $shifts_tmp['total_goods'] += (float) $transaction->amount;
                            }
                        }else{
                            $total_amount -= $transaction->amount;
                        }
                    }
                }

                $shifts_tmp['total_amount'] = $total_amount;
                $shifts_arr[] = $shifts_tmp;
            }
        }
        return $shifts_arr;
    }

    public function viewReceipt($data)
    {
        $user_id = $data['user_id'] ?? 0;
        $date = $data['date'];
        $date_to = $data['date_to'];
        $company_id = $data['company_id'] ?? 0;
        $vehicle_id = $data['vehicle_id'] ?? 0;
        // get user
        $user = $this->users->getUserByKey('id', $user_id, $company_id);

        if (empty($user)) return response('User not found.', 404);

        // convert date
        $role_name = $user->role->name;
        $start_date = date("Y-m-d 00:00:00", strtotime($date));
        $end_date = date("Y-m-d 23:59:59", strtotime($date_to));

        $orWheres = [
            ['ended', '>=', $start_date],
            ['ended', '<=', $end_date],
            ['ended', '!=', NULL],
            ['shift_destroy', '!=', 1]
        ];

        if ($role_name == 'driver') {
            array_push($orWheres, ['user_id', $user_id]);
        } elseif ($role_name == 'subdriver') {
            array_push($orWheres, ['subdriver_id', $user_id]);
        } else return response('Invalid user', 404);

        if($vehicle_id || $vehicle_id > 0) array_push($orWheres, ['shifts.vehicle_id', $vehicle_id]);

        // get shifts
        $shifts_arr = [];
        $shifts = Shift::leftJoin('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
                        ->leftJoin('routes', 'routes.id', '=', 'shifts.route_id')
                        ->leftJoin('bus_stations', 'bus_stations.id', '=', 'shifts.station_id')
                        ->leftJoin('devices', 'devices.id', '=', 'shifts.device_id')
                        ->where($orWheres)
                        ->select(
                            'shifts.*',
                            'routes.name as route_name',
                            'devices.identity as identity',
                            'vehicles.license_plates as license_plates',
                            'bus_stations.name as from_station'
                        )->orderBy('shifts.ended')->get();

        if (count($shifts) > 0) {

            foreach ($shifts as $shift) {

                // datetime
                $started = date("d-m-Y H:i:s", strtotime($shift->started));
                $ended = empty($shift->ended) ? '' : date("d-m-Y H:i:s", strtotime($shift->ended));
                $date_time = $started . ' <=> ' . $ended;
                $total_amount = $shift->total_amount;

                $shifts_tmp = [
                    'shift_id' => $shift->id,
                    'total_amount' => $shift->total_amount,
                    'license_plates' => $shift->license_plates ?? '',
                    'driver_name' => '',
                    'subdriver_name' => '',
                    'route_name' => $shift->route_name ?? '',
                    'route_id' => $shift->route_id,
                    'from_station' => $shift->from_station ?? '',
                    'date_time' => $date_time,
                    'total_price' => 0,
                    'total_deposit' => 0,
                    'total_charge' => 0,
                    'total_goods' => 0,
                    'collected' => $shift->collected,
                    'shift_destroy' => $shift->shift_destroy,
                    'hidden' => $shift->hidden,
                    'is_amount' => 0,
                    'identity' => $shift->identity ?? ''
                ];

                //get driver
                if (round($shift->user_id) > 0) {
                    $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $shifts_tmp['driver_name'] = $driver['fullname'];
                }

                // get subdriver
                if (round($shift->subdriver_id) > 0) {
                    $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $shifts_tmp['subdriver_name'] = $subdriver['fullname'];
                }

                // get total price
                $transactions = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id],
                    ['ticket_destroy', '!=', 1]
                ]);
                if (count($transactions) > 0) {

                    $shifts_tmp['is_amount'] = 1;

                    foreach ($transactions as $transaction) {

                        if($transaction->ticket_destroy != 1){
                            if ((float) $transaction->amount > 0) {
                                if ($transaction->type == 'charge') $shifts_tmp['total_charge'] += (float) $transaction->amount;
                                if ($transaction->type == 'deposit' || $transaction->type == 'deposit_month') $shifts_tmp['total_deposit'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos') $shifts_tmp['total_price'] += (float) $transaction->amount;
                                if ($transaction->type == 'pos_goods') $shifts_tmp['total_goods'] += (float) $transaction->amount;
                            }
                        }else{
                            $total_amount -= $transaction->amount;
                        }
                    }
                }

                $shifts_tmp['total_amount'] = $total_amount;
                $shifts_arr[] = $shifts_tmp;
            }
        }
        return $shifts_arr;
    }

    public function viewStaff($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $route_id = (int) $data['route_id'] ?? 0;
        $position = $data['position'];
        $company_id = (int) $data['company_id'];
        $user_id = $data['user_id'] ? (int) $data['user_id'] : 0 ;
        $key_role = '';

        $optRoles = [];
        if ($position == 'all') {
            $key_role = 'role_all';
            $optRoles = $this->roles->getIdRolePluckByName(['driver', 'subdriver']);
        } else {
            $key_role = 'role_only';
            $optRoles = $this->roles->getIdRolePluckByName([$position]);
        }
        $users = [];
        if($user_id > 0)
          $users = $this->users->getUsersByIdReturnArray($user_id);
        else
          $users = $this->users->getUsersByRoleAndCompany($optRoles, $company_id);

        $staffs_arr = [];

        if (count($users) > 0) {

            foreach ($users as $user) {

                $wheres = [
                    ["shifts.ended", ">=", $from_date],
                    ["shifts.ended", "<=", $to_date],
                    ["shifts.ended", "!=", NULL],
                    ['shifts.shift_destroy', '!=', 1]
                ];

                if ($route_id > 0) $wheres[] = ["shifts.route_id", "=", $route_id];

                if ($user->role->name == 'subdriver') {
                    $position_name = 'Phụ xe';
                    $wheres[] = ["shifts.subdriver_id", "=", $user->id];
                }

                if ($user->role->name == 'driver') {
                    $position_name = 'Lái xe';
                    $wheres[] = ["shifts.user_id", "=", $user->id];
                }

                $tmp = Shift::join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->where($wheres)
                        ->where('routes.company_id', $company_id)
                        ->select('shifts.*', 'routes.name as route_name','routes.number as route_number')
                        ->orderBy('shifts.route_id')
                        ->get();

                if (count($tmp) > 0) {

                    $groups = collect($tmp)->groupBy('route_id');

                    foreach ($groups as $shifts) {

                        $staff_tmp = array(
                            'company_id' => $company_id,
                            'rfid' => $user->rfidcard->rfid,
                            'fullname' => $user->fullname,
                            'route_number' => $shifts[0]['route_number'],
                            'route_id' => $shifts[0]['route_id'],
                            'position_name' => $position_name,
                            'role_name' => $user->role->name,
                            'role_id' => $user->role->id,
                            'key_role' => $key_role,
                            'total_price_deposit' =>  0,
                            'count_ticket_pos' => 0,
                            'total_price_pos' => 0,
                            'count_ticket_charge' => 0,
                            'total_price_qrcode' => 0,
                            'count_ticket_qrcode' => 0,
                            'total_price_month' => 0,
                            'total_price_charge' => 0,
                            'total_price_discount' =>  0,
                            'total_price_collected' => 0,
                            'count_revenue_ticket' => 0,
                            'count_discount_ticket' => 0,
                            'count_collected_ticket' => 0
                        );

                        if (count($shifts) > 0) {

                            foreach ($shifts as $shift) {

                                $transactions = Transaction::leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                                        ->whereIn('transactions.type', ['deposit', 'deposit_month', 'qrcode', 'charge', 'pos'])
                                        ->where('transactions.shift_id', $shift['id'])
                                        ->where('transactions.company_id', $company_id)
                                        ->where('transactions.ticket_destroy', '!=', 1)
                                        ->selectRaw('
                                            transactions.type,
                                            sum(ticket_prices.price) as total_price,
                                            sum(transactions.amount) as total_amount,
                                            count(transactions.type) as count_ticket'
                                        )
                                        ->groupBy('transactions.type')
                                        ->get();

                                if (count($transactions) > 0) {

                                    foreach ($transactions as $transaction) {

                                        if ($transaction->type == 'pos') {
                                            $staff_tmp['count_ticket_pos'] += $transaction->count_ticket;
                                            $staff_tmp['total_price_pos'] += (float) $transaction->total_amount;
                                        }

                                        if ($transaction->type == 'charge') {
                                            $staff_tmp['count_ticket_charge'] += $transaction->count_ticket;
                                            // $ticket_price =  $this->ticket_prices->getPriceById($transaction->ticket_price_id);
                                            // if ($ticket_price) {
                                            $staff_tmp['total_price_charge'] += (float) $transaction->total_price;
                                            $staff_tmp['total_price_collected'] += (float) $transaction->total_amount;
                                            $staff_tmp['total_price_discount'] += (float) $transaction->total_price - (float) $transaction->total_amount;
                                            // }
                                        }

                                        if ($transaction->type == 'deposit' || $transaction->type == 'deposit_month') {
                                            $staff_tmp['total_price_deposit'] += (float) $transaction->total_amount;
                                        }

                                        if ($transaction->type == 'qrcode') {
                                            $staff_tmp['count_ticket_qrcode'] += $transaction->count_ticket;
                                            $staff_tmp['total_price_qrcode'] += (float) $transaction->total_amount;
                                        }
                                    }
                                }

                                $transactions_v2 =  Transaction::whereIn('transactions.type', ['charge_month'])
                                        ->where('transactions.shift_id', $shift['id'])
                                        ->where('transactions.company_id', $company_id)
                                        ->where('transactions.ticket_destroy', '!=', 1)
                                        ->select('transactions.*')
                                        ->get();

                                if(count($transactions_v2) > 0) {

                                    $rfid_values = collect($transactions_v2)->groupBy('rfid')->toArray();

                                    foreach ($rfid_values as $rfid_k => $rfid_vls) {

                                        $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();
                                        foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                            $count_route_all = DB::table('transactions')
                                                ->where([
                                                    'transactions.rfid' => $rfid_k,
                                                    'transactions.company_id' => $company_id,
                                                    'transactions.type' => 'charge_month',
                                                    ['transactions.ticket_destroy' , '!=', 1]
                                                ])
                                                ->where('transactions.ticket_number', (string)$k_tkt_num)
                                                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                                ->where([
                                                    ['shifts.ended', '!=', NULL],
                                                    ['shifts.shift_destroy', '!=', 1],
                                                    ['shifts.ended', '>=', $from_date],
                                                    ['shifts.ended', '<=', $to_date]
                                                ])->count();

                                            $staff_tmp['total_price_month'] +=  $v_tkt_num[0]['amount'] / $count_route_all * count($v_tkt_num);
                                        }
                                    }
                                }
                            }
                        }

                        $staff_tmp['count_revenue_ticket'] = $staff_tmp['total_price_pos'] + $staff_tmp['total_price_charge'] + $staff_tmp['total_price_qrcode'] + $staff_tmp['total_price_month'];
                        $staff_tmp['count_discount_ticket'] = $staff_tmp['total_price_discount'];
                        $staff_tmp['count_collected_ticket'] = $staff_tmp['total_price_pos'] + $staff_tmp['total_price_collected'] + $staff_tmp['total_price_qrcode'] + $staff_tmp['total_price_month'];

                        $staffs_arr[] = $staff_tmp;
                    }
                }
            }
        }

        $staff_result = [];
        $staffs_arr = collect($staffs_arr)->groupBy('route_id')->toArray();

        $data_total_only = [
            'count_ticket_pos_only' => 0,
            'total_ticket_pos_only' => 0,

            'count_ticket_qrcode_only' => 0,
            'total_ticket_qrcode_only' => 0,
            'total_price_month_only' => 0,

            'count_ticket_charge_only' => 0,
            'total_ticket_charge_only' => 0,
            'total_discount_charge_only' => 0,
            'total_collected_charge_only' => 0,
            'total_revenue_ticket_all_only' => 0,
            'total_collected_ticket_all_only' => 0,
            'total_discount_ticket_all_only' => 0,
            'total_deposit_only' => 0,
        ];

        $data_total_driver = [
            'count_ticket_pos_driver' => 0,
            'total_ticket_pos_driver' => 0,

            'count_ticket_qrcode_driver' => 0,
            'total_ticket_qrcode_driver' => 0,

            'total_price_month_driver' => 0,

            'count_ticket_charge_driver' => 0,
            'total_ticket_charge_driver' => 0,
            'total_discount_charge_driver' => 0,
            'total_collected_charge_driver' => 0,
            'total_revenue_ticket_all_driver' => 0,
            'total_collected_ticket_all_driver' => 0,
            'total_discount_ticket_all_driver' => 0,
            'total_deposit_driver' => 0,
        ];

        $data_total_subdriver = [
            'count_ticket_pos_subdriver' => 0,
            'total_ticket_pos_subdriver' => 0,
            'count_ticket_qrcode_subdriver' => 0,
            'total_ticket_qrcode_subdriver' => 0,

            'total_price_month_subdriver' => 0,

            'count_ticket_charge_subdriver' => 0,
            'total_ticket_charge_subdriver' => 0,
            'total_discount_charge_subdriver' => 0,
            'total_collected_charge_subdriver' => 0,
            'total_revenue_ticket_all_subdriver' => 0,
            'total_collected_ticket_all_subdriver' => 0,
            'total_discount_ticket_all_subdriver' => 0,
            'total_deposit_subdriver' => 0,
        ];

        foreach ($staffs_arr as $key => $staffs) {

            $staffs = collect($staffs)->groupBy('role_id')->toArray();

            foreach ($staffs as $k => $values) {
                $obj = new \stdClass;
                $obj->count_ticket_pos = 0;
                $obj->total_price_pos = 0;

                $obj->count_ticket_qrcode = 0;
                $obj->total_price_qrcode = 0;

                $obj->total_price_month = 0;

                $obj->count_ticket_charge = 0;
                $obj->total_price_charge = 0;
                $obj->total_price_discount = 0;
                $obj->total_price_collected = 0;
                $obj->count_revenue_ticket = 0;
                $obj->count_discount_ticket = 0;
                $obj->count_collected_ticket = 0;
                $obj->total_price_deposit = 0;

                foreach ($values as $v) {

                    $obj->count_ticket_pos += $v['count_ticket_pos'];
                    $obj->total_price_pos += $v['total_price_pos'];

                    $obj->count_ticket_qrcode += $v['count_ticket_qrcode'];
                    $obj->total_price_qrcode += $v['total_price_qrcode'];

                    $obj->total_price_month += $v['total_price_month'];;

                    $obj->count_ticket_charge += $v['count_ticket_charge'];
                    $obj->total_price_charge += $v['total_price_charge'];
                    $obj->total_price_discount += $v['total_price_discount'];
                    $obj->total_price_collected += $v['total_price_collected'];
                    $obj->count_revenue_ticket += $v['total_price_pos'] +  $v['total_price_charge'] + $v['total_price_qrcode'] + $v['total_price_month'];
                    $obj->count_discount_ticket += $v['total_price_discount'];
                    $obj->count_collected_ticket += $v['total_price_pos'] + $v['total_price_collected'] + $v['total_price_qrcode'] + $v['total_price_month'];
                    $obj->total_price_deposit += $v['total_price_deposit'];

                    $staff_result[] = $v;

                    if ($v['key_role'] == 'role_only') {

                        $data_total_only['count_ticket_pos_only'] += $v['count_ticket_pos'];
                        $data_total_only['total_ticket_pos_only'] += $v['total_price_pos'];

                        $data_total_only['count_ticket_qrcode_only'] += $v['count_ticket_qrcode'];
                        $data_total_only['total_ticket_qrcode_only'] += $v['total_price_qrcode'];

                        $data_total_only['total_price_month_only'] += $v['total_price_month'];

                        $data_total_only['count_ticket_charge_only'] += $v['count_ticket_charge'];
                        $data_total_only['total_ticket_charge_only'] += $v['total_price_charge'];
                        $data_total_only['total_discount_charge_only'] += $v['total_price_discount'];
                        $data_total_only['total_collected_charge_only'] += $v['total_price_collected'];
                        $data_total_only['total_revenue_ticket_all_only'] += $v['count_revenue_ticket'];
                        $data_total_only['total_collected_ticket_all_only'] += $v['count_collected_ticket'];
                        $data_total_only['total_discount_ticket_all_only'] += $v['count_discount_ticket'];
                        $data_total_only['total_deposit_only'] += $v['total_price_deposit'];
                    }

                    if ($v['key_role'] == 'role_all') {

                        if ($v['role_name'] == 'driver') {

                            $data_total_driver['count_ticket_pos_driver'] += $v['count_ticket_pos'];
                            $data_total_driver['total_ticket_pos_driver'] += $v['total_price_pos'];

                            $data_total_driver['count_ticket_qrcode_driver'] += $v['count_ticket_qrcode'];
                            $data_total_driver['total_ticket_qrcode_driver'] += $v['total_price_qrcode'];

                            $data_total_driver['total_price_month_driver'] += $v['total_price_month'];

                            $data_total_driver['count_ticket_charge_driver'] += $v['count_ticket_charge'];
                            $data_total_driver['total_ticket_charge_driver'] += $v['total_price_charge'];
                            $data_total_driver['total_discount_charge_driver'] += $v['total_price_discount'];
                            $data_total_driver['total_collected_charge_driver'] += $v['total_price_collected'];
                            $data_total_driver['total_revenue_ticket_all_driver'] += $v['count_revenue_ticket'];
                            $data_total_driver['total_collected_ticket_all_driver'] += $v['count_collected_ticket'];
                            $data_total_driver['total_discount_ticket_all_driver'] += $v['count_discount_ticket'];
                            $data_total_driver['total_deposit_driver'] += $v['total_price_deposit'];
                        }

                        if ($v['role_name'] == 'subdriver') {

                            $data_total_subdriver['count_ticket_pos_subdriver'] += $v['count_ticket_pos'];
                            $data_total_subdriver['total_ticket_pos_subdriver'] += $v['total_price_pos'];

                            $data_total_subdriver['count_ticket_qrcode_subdriver'] += $v['count_ticket_qrcode'];
                            $data_total_subdriver['total_ticket_qrcode_subdriver'] += $v['total_price_qrcode'];

                            $data_total_subdriver['total_price_month_subdriver'] += $v['total_price_month'];

                            $data_total_subdriver['count_ticket_charge_subdriver'] += $v['count_ticket_charge'];
                            $data_total_subdriver['total_ticket_charge_subdriver'] += $v['total_price_charge'];
                            $data_total_subdriver['total_discount_charge_subdriver'] += $v['total_price_discount'];
                            $data_total_subdriver['total_collected_charge_subdriver'] += $v['total_price_collected'];
                            $data_total_subdriver['total_revenue_ticket_all_subdriver'] += $v['count_revenue_ticket'];
                            $data_total_subdriver['total_collected_ticket_all_subdriver'] += $v['count_collected_ticket'];
                            $data_total_subdriver['total_discount_ticket_all_subdriver'] += $v['count_discount_ticket'];
                            $data_total_subdriver['total_deposit_subdriver'] += $v['total_price_deposit'];
                        }
                    }
                }

                $staff_result[] = $obj;
            }
        }

        $data_total_only['total_price_month_only'] = round($data_total_only['total_price_month_only'], 2);
        $data_total_driver['total_price_month_driver'] = round($data_total_driver['total_price_month_driver'], 2);
        $data_total_subdriver['total_price_month_subdriver'] = round($data_total_subdriver['total_price_month_subdriver'], 2);

        $result = [];
        $result['isCheckModuleApp'] = $this->isCheckModuleApp($company_id);
        $result['staffs_arr'] = $staff_result;
        $result['data_total_only'] = $data_total_only;
        $result['data_total_driver'] = $data_total_driver;
        $result['data_total_subdriver'] = $data_total_subdriver;

        return $result;
    }

    public function viewVehicleAll($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];

        if ($data) {
            $vehicle_arr = $this->getReportVehicleAllByData($data);

            $result = [];
            $result['vehicle_arr'] = $vehicle_arr;
            $result['isCheckModuleApp'] = $this->isCheckModuleApp($data['company_id']);
            return $result;

        }
    }

    public function viewCard($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $card_arr = [];

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $transactions = DB::table('transactions')
            ->leftJoin('shifts', 'shifts.id', '=', 'transactions.shift_id')
            ->where('shifts.ended', '>=', $from_date)
            ->where('shifts.ended', '<=', $to_date)
            ->where('shifts.ended', '!=', NULL)
            ->where('shifts.shift_destroy', '!=', 1)
            ->where('transactions.company_id', $company_id)
            ->whereIn('transactions.type', ['charge', 'deposit'])
            ->where('transactions.rfid', '!=', NULL)
            ->where('transactions.ticket_destroy', '!=', 1)
            ->orderBy('transactions.activated')
            ->select('transactions.*')
            ->get()
            ->toArray();

        $total_memberships = [
            "total_balance_before" => 0,
            "total_deposit_in" => 0,
            "total_charge_in" => 0,
            "total_balance" => 0,
            "total_balance_end" => 0
        ];

        $rfid_arr = [];

        if (count($transactions) > 0) {

            $transaction_group = collect($transactions)->groupBy('rfid')->toArray();
            $rfid_arr = array_keys($transaction_group);

            foreach ($transaction_group as $key => $values) {

                $obj = new \stdClass;
                $obj->rfid = $key;
                $obj->total_charge_in = 0;
                $obj->total_deposit_in = 0;
                $obj->total_balance_before = 0;

                $rfidcard =  $this->rfidcards->getRfidCardByRfid($key);
                if (empty($rfidcard)) continue;

                $membership = $this->memberships->getMembershipByRfidcardId($rfidcard->id);

                if (!empty($membership)) {
                    $obj->barcode = $membership->rfidcard->barcode;
                    $obj->fullname = $membership->fullname;
                    $obj->membership_type = $membership->membershipType['name'];
                    $obj->phone = $membership->phone;
                    $obj->balance = (float) $membership->balance;
                } else continue;

                foreach ($values as $v) {
                    if ($v->type == 'charge')   $obj->total_charge_in += $v->amount;
                    if ($v->type == 'deposit')  $obj->total_deposit_in += $v->amount;
                }

                if (!$values[0]->balance || $values[0]->balance != NULL){
                    if ($values[0]->type == 'deposit') $obj->total_balance_before = (int)$values[0]->balance - $values[0]->amount;
                    if ($values[0]->type == 'charge') $obj->total_balance_before = (int)$values[0]->balance + $values[0]->amount;
                }

                $obj->total_balance_end = $obj->total_balance_before + $obj->total_deposit_in - $obj->total_charge_in;

                $total_memberships['total_balance_before'] += $obj->total_balance_before;
                $total_memberships['total_charge_in'] += $obj->total_charge_in;
                $total_memberships['total_deposit_in'] += $obj->total_deposit_in;
                $total_memberships['total_balance_end'] += $obj->total_balance_end;
                $total_memberships['total_balance'] += $obj->balance;

                $card_arr[] = $obj;
            }
        }

        $date_first = DB::table('transactions')
            ->where('company_id', $company_id)
            ->where('ticket_destroy', '!=', 1)
            ->whereIn('type', ['charge', 'deposit'])
            ->orderBy('activated')
            ->first();

        if (!empty($date_first)) {

            if ($date_first->activated <= $to_date) {

                $transactions_v2 = DB::table('transactions')
                    ->leftJoin('shifts', 'shifts.id', '=', 'transactions.shift_id')
                    ->where('shifts.ended', '>=', $date_first->activated)
                    ->where('shifts.ended', '<=', $to_date)
                    ->where('shifts.ended', '!=', NULL)
                    ->where('shifts.shift_destroy', '!=', 1)
                    ->where('transactions.company_id', $company_id)
                    ->whereIn('transactions.type', ['charge', 'deposit'])
                    ->whereNotIn('transactions.rfid', $rfid_arr)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->orderBy('transactions.activated', 'DESC')
                    ->get()
                    ->toArray();

                if (count($transactions_v2) > 0) {

                    $transaction_group = collect($transactions_v2)->groupBy('rfid')->toArray();

                    foreach ($transaction_group as $key => $values) {

                        $obj = new \stdClass;
                        $obj->rfid = $key;
                        $obj->total_charge_in = 0;
                        $obj->total_deposit_in = 0;
                        $obj->total_balance_before = 0;

                        $rfidcard =  $this->rfidcards->getRfidCardByRfid($key);
                        if (empty($rfidcard))   continue;

                        $membership = $this->memberships->getMembershipByRfidcardId($rfidcard->id);

                        if (!empty($membership)) {
                            $obj->barcode = $membership->rfidcard->barcode;
                            $obj->fullname = $membership->fullname;
                            $obj->membership_type = $membership->membershipType['name'];
                            $obj->phone = $membership->phone;
                            $obj->balance = (float) $membership->balance;
                        }

                        if (!empty($values[0]->balance)) $obj->total_balance_before = $values[0]->balance;

                        $obj->total_balance_end = $obj->total_balance_before;

                        //handle total
                        $total_memberships['total_balance_before'] += $obj->total_balance_before;
                        $total_memberships['total_charge_in'] += $obj->total_charge_in;
                        $total_memberships['total_deposit_in'] += $obj->total_deposit_in;
                        $total_memberships['total_balance_end'] += $obj->total_balance_end;
                        $total_memberships['total_balance'] += $obj->balance;

                        $card_arr[] = $obj;
                    }
                }
            }
        }

        $result['card_arr'] = $card_arr;
        $result['total_memberships'] = $total_memberships;
        return $result;
    }

    public function viewVehicleByRoute($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $vehicle_id = (int) $data['vehicle_id'];
        $route_id = (int) $data['route_id'];

        $vehicles_arr = [];

        // date
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        // get all route of company
        $where_arr = [
            ["shifts.ended", ">=", $from_date],
            ["shifts.ended", "<=", $to_date],
            ["shifts.ended", "!=", NULL],
            ['shifts.shift_destroy', '!=', 1]
        ];

        if ($route_id > 0) {

            $where_arr[] =  ["shifts.route_id", "=", $route_id];
            if($vehicle_id > 0) $where_arr[] =  ["shifts.vehicle_id", "=", $vehicle_id];

            $shifts = Shift::join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->join('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
                        ->leftJoin('bus_stations', 'bus_stations.id', '=', 'shifts.station_id')
                        ->where($where_arr)
                        ->select(
                            'shifts.*',
                            'routes.name as route_name',
                            'routes.number as route_number',
                            'vehicles.license_plates as license_plate',
                            'bus_stations.name as station_name'
                        )
                        ->get();

            if (count($shifts) > 0) {

                $obj_total = new \stdClass;
                $obj_total->route_name = "only";
                $obj_total->route_number = 'only';
                $obj_total->total_price_pos = 0;
                $obj_total->count_ticket_pos = 0;
                $obj_total->count_ticket_charge = 0;
                $obj_total->total_price_charge = 0;
                $obj_total->total_price_discount = 0;
                $obj_total->total_price_collected = 0;
                $obj_total->count_ticket_qrcode = 0;
                $obj_total->total_price_qrcode = 0;
                $obj_total->total_price_month = 0;
                $obj_total->total_price_deposit = 0;
                $obj_total->count_revenue_ticket = 0;
                $obj_total->count_discount_ticket = 0;
                $obj_total->count_collected_ticket = 0;

                foreach ($shifts as $key => $value) {

                    $value = (object)$value;

                    $obj = new \stdClass;
                    $obj->license_plate = $value->license_plate ?? '';
                    $obj->route_name = $value->route_name ?? '';
                    $obj->route_number = $value->route_number ?? '';
                    $obj->station_name =  $value->station_name ?? '';
                    $obj->driver_name = '';
                    $obj->subdriver_name = '';
                    $obj->started = $value->started;
                    $obj->ended = $value->ended;
                    $obj->station_name = '';
                    $obj->total_price_pos = 0;
                    $obj->count_ticket_pos = 0;
                    $obj->count_ticket_charge = 0;
                    $obj->total_price_charge = 0;
                    $obj->total_price_discount = 0;
                    $obj->total_price_collected = 0;
                    $obj->count_ticket_qrcode = 0;
                    $obj->total_price_qrcode = 0;
                    $obj->total_price_month = 0;
                    $obj->total_price_deposit = 0;
                    // $obj->total_revenue = 0;

                    // get driver
                    if ($value->user_id) {
                        $user = $this->users->getUserByKey('id', $value->user_id, $company_id);
                        $obj->driver_name = $user ? $user['fullname'] : '';
                    }

                    // get subdriver
                    if ($value->subdriver_id) {
                        $user = $this->users->getUserByKey('id', $value->subdriver_id, $company_id);
                        $obj->subdriver_name = $user ? $user['fullname'] : '';
                    }

                    $transactions = Transaction::leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                                ->whereIn('transactions.type', ['deposit', 'qrcode', 'charge', 'pos'])
                                ->where('transactions.shift_id', $value->id)
                                ->where('transactions.company_id', $company_id)
                                ->where('transactions.ticket_destroy', '!=', 1)
                                ->select('transactions.*', 'ticket_prices.price as price')
                                ->get();

                    if (count($transactions) > 0) {

                        foreach ($transactions as $transaction) {

                            if ($transaction->type == 'deposit')
                                $obj->total_price_deposit += (float)$transaction->amount;

                            if ($transaction->type == 'qrcode') {
                                $obj->count_ticket_qrcode += 1;
                                $obj->total_price_qrcode += (float) $transaction->amount;
                            }

                            if ($transaction->type == 'charge') {
                                $obj->count_ticket_charge += 1;
                                $obj->total_price_charge += (float) $transaction->price;
                                $obj->total_price_collected += (float) $transaction->amount;
                                $obj->total_price_discount += (float) $transaction->price - (float) $transaction->amount;
                            }

                            if ($transaction->type == 'pos') {
                                $obj->total_price_pos += (float)$transaction->amount;
                                $obj->count_ticket_pos += 1;
                            }
                        }
                    }

                    $transactions_v2 =  Transaction::whereIn('transactions.type', ['charge_month'])
                                    ->where('transactions.shift_id', $value->id)
                                    ->where('transactions.company_id', $company_id)
                                    ->where('transactions.ticket_destroy', '!=', 1)
                                    ->select('transactions.*')
                                    ->get();

                    if(count($transactions_v2) > 0) {

                        $rfid_values = collect($transactions_v2)->groupBy('rfid')->toArray();
                        foreach ($rfid_values as $rfid_k => $rfid_vls) {

                            $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();
                            foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                $count_route_all = DB::table('transactions')
                                    ->where([
                                        'transactions.rfid' => $rfid_k,
                                        'transactions.company_id' => $company_id,
                                        'transactions.type' => 'charge_month',
                                        ['transactions.ticket_destroy' , '!=', 1]
                                    ])
                                    ->where('transactions.ticket_number', (string)$k_tkt_num)
                                    ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                    ->where([
                                        ['shifts.ended', '!=', NULL],
                                        ['shifts.shift_destroy', '!=', 1],
                                        ['shifts.ended', '>=', $from_date],
                                        ['shifts.ended', '<=', $to_date]
                                    ])->count();
                                $obj->total_price_month += $v_tkt_num[0]['amount'] / $count_route_all * count($v_tkt_num);
                            }
                        }
                    }

                    // $obj->total_price_month += round($obj->total_revenue);
                    $obj->count_revenue_ticket =  $obj->total_price_qrcode +  $obj->total_price_charge +   $obj->total_price_pos +  $obj->total_price_month;
                    $obj->count_discount_ticket =  $obj->total_price_discount;
                    $obj->count_collected_ticket =  $obj->total_price_pos +  $obj->total_price_collected;

                    $obj_total->total_price_pos += $obj->total_price_pos;
                    $obj_total->count_ticket_pos += $obj->count_ticket_pos;
                    $obj_total->count_ticket_charge += $obj->count_ticket_charge;
                    $obj_total->total_price_charge += $obj->total_price_charge;
                    $obj_total->total_price_discount += $obj->total_price_discount;
                    $obj_total->total_price_collected += $obj->total_price_collected;
                    $obj_total->count_ticket_qrcode += $obj->count_ticket_qrcode;
                    $obj_total->total_price_qrcode += $obj->total_price_qrcode;
                    $obj_total->total_price_month += $obj->total_price_month;
                    $obj_total->total_price_deposit += $obj->total_price_deposit;
                    $obj_total->count_revenue_ticket += $obj->count_revenue_ticket;
                    $obj_total->count_discount_ticket += $obj->count_discount_ticket;
                    $obj_total->count_collected_ticket += $obj->count_collected_ticket;

                    $vehicles_arr[] = $obj;
                }
                $obj_total->total_price_month = round($obj_total->total_price_month, 2);
                $vehicles_arr[] = $obj_total;
            }
        }else{

            // $arr_route_id = $this->routes->getPluckRouteIdByCompanyId($company_id);
            if ($vehicle_id > 0) $where_arr[] =  ["shifts.vehicle_id", "=", $vehicle_id];

            $shifts = Shift::join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->where('routes.company_id', '=', $company_id)
                        ->where($where_arr)
                        ->select(
                            'shifts.*',
                            'routes.name as route_name',
                            'routes.number as route_number'
                        )
                        ->get();

            if(count($shifts) > 0){
                $shifts = collect($shifts)->groupBy('route_id')->toArray();

                $obj_total = new \stdClass;
                $obj_total->route_name = "all";
                $obj_total->route_number = 'all';
                $obj_total->total_price_pos = 0;
                $obj_total->count_ticket_pos = 0;
                $obj_total->count_ticket_charge = 0;
                $obj_total->total_price_charge = 0;
                $obj_total->total_price_discount = 0;
                $obj_total->total_price_collected = 0;
                $obj_total->count_ticket_qrcode = 0;
                $obj_total->total_price_qrcode = 0;
                $obj_total->total_price_month = 0;
                $obj_total->total_price_deposit = 0;
                $obj_total->count_revenue_ticket = 0;
                $obj_total->count_discount_ticket = 0;
                $obj_total->count_collected_ticket = 0;

                foreach ($shifts as $key => $values) {

                    $obj = new \stdClass;
                    $obj->total_price_pos = 0;
                    $obj->count_ticket_pos = 0;
                    $obj->count_ticket_charge = 0;
                    $obj->total_price_charge = 0;
                    $obj->total_price_discount = 0;
                    $obj->total_price_collected = 0;
                    $obj->count_ticket_qrcode = 0;
                    $obj->total_price_qrcode = 0;
                    // $obj->total_revenue = 0;
                    $obj->total_price_month = 0;
                    $obj->total_price_deposit = 0;
                    $obj->route_name = $values[0]['route_name'] ?? '';
                    $obj->route_number = $values[0]['route_number'] ?? '';

                    foreach ($values as $value) {

                        $value = (object)$value;

                        $transactions = Transaction::leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                                        ->whereIn('transactions.type', ['deposit', 'qrcode', 'charge', 'pos'])
                                        ->where('transactions.shift_id', $value->id)
                                        ->where('transactions.company_id', $company_id)
                                        ->where('transactions.ticket_destroy', '!=', 1)
                                        ->select('transactions.*', 'ticket_prices.price as price')
                                        ->get();

                        if (count($transactions) > 0) {

                            foreach ($transactions as $transaction) {

                                if ($transaction->type == 'deposit')
                                    $obj->total_price_deposit += (float)$transaction->amount;

                                if ($transaction->type == 'qrcode') {
                                    $obj->count_ticket_qrcode += 1;
                                    $obj->total_price_qrcode += (float) $transaction->amount;
                                }

                                if ($transaction->type == 'charge') {
                                    $obj->count_ticket_charge += 1;
                                    $obj->total_price_charge += (float) $transaction->price;
                                    $obj->total_price_collected += (float) $transaction->amount;
                                    $obj->total_price_discount += (float) $transaction->price - (float) $transaction->amount;
                                }

                                if ($transaction->type == 'pos') {
                                    $obj->total_price_pos += (float)$transaction->amount;
                                    $obj->count_ticket_pos += 1;
                                }
                            }
                        }

                        $transactions_v2 = Transaction::whereIn('transactions.type', ['charge_month'])
                                        ->where('transactions.shift_id', $value->id)
                                        ->where('transactions.company_id', $company_id)
                                        ->where('transactions.ticket_destroy', '!=', 1)
                                        ->select('transactions.*')
                                        ->get();

                        if(count($transactions_v2) > 0) {
                            //group data by rfid
                            $rfid_values = collect($transactions_v2)->groupBy('rfid')->toArray();
                            foreach ($rfid_values as $rfid_k => $rfid_vls) {

                                $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();
                                foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                    $count_route_all = DB::table('transactions')
                                        ->where([
                                            'transactions.rfid' => $rfid_k,
                                            'transactions.company_id' => $company_id,
                                            'transactions.type' => 'charge_month',
                                            ['transactions.ticket_destroy' , '!=', 1]
                                        ])
                                        ->where('transactions.ticket_number', (string)$k_tkt_num)
                                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                        ->where([
                                            ['shifts.ended', '!=', NULL],
                                            ['shifts.shift_destroy', '!=', 1],
                                            ['shifts.ended', '>=', $from_date],
                                            ['shifts.ended', '<=', $to_date]
                                        ])->count();

                                    $obj->total_price_month +=  $v_tkt_num[0]['amount'] / $count_route_all * count($v_tkt_num);
                                }
                            }
                        }

                    }
                    // $obj->total_price_month += round($obj->total_revenue);
                    $obj->count_revenue_ticket =  $obj->total_price_qrcode +  $obj->total_price_charge +   $obj->total_price_pos +  $obj->total_price_month;
                    $obj->count_discount_ticket =  $obj->total_price_discount;
                    $obj->count_collected_ticket =  $obj->total_price_pos +  $obj->total_price_collected;

                    $obj_total->total_price_pos += $obj->total_price_pos;
                    $obj_total->count_ticket_pos += $obj->count_ticket_pos;
                    $obj_total->count_ticket_charge += $obj->count_ticket_charge;
                    $obj_total->total_price_charge += $obj->total_price_charge;
                    $obj_total->total_price_discount += $obj->total_price_discount;
                    $obj_total->total_price_collected += $obj->total_price_collected;
                    $obj_total->count_ticket_qrcode += $obj->count_ticket_qrcode;
                    $obj_total->total_price_qrcode += $obj->total_price_qrcode;
                    $obj_total->total_price_month += $obj->total_price_month;
                    $obj_total->total_price_deposit += $obj->total_price_deposit;
                    $obj_total->count_revenue_ticket += $obj->count_revenue_ticket;
                    $obj_total->count_discount_ticket += $obj->count_discount_ticket;
                    $obj_total->count_collected_ticket += $obj->count_collected_ticket;
                    $vehicles_arr[] = $obj;
                }
                $obj_total->total_price_month = round($obj_total->total_price_month, 2);
                $vehicles_arr[] = $obj_total;
            }
        }

        $result = [];
        $result['isCheckModuleApp'] = $this->isCheckModuleApp($company_id);
        $result['vehicles_arr'] = $vehicles_arr;

        return $result;
    }

    public function viewDaily($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $route_id = $data['route_id'];
        $company_id = $data['company_id'];

        // date
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $shift_detail = [];
        $route_detail = [];
        $route_group_detail = [];
        $route_group_debt_any = [];
        $shift_now_yesterday_result = [];
        $route_group_yesterday_result = [];
        $route_group_debt_all['total_debt'] = 0;
        $shift_yesterday_debt = 0;

        //select query shift_detail data the shifts by date
        if ($route_id == 0) {

            //select query route_group_debt_any data the shifts in debt
            $shift_debt1 = DB::select('
                SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                WHERE shifts.collected = 0
                AND shifts.shift_destroy != 1
                AND shifts.ended < "' . $from_date . '"
                AND routes.company_id = ' . (int) $company_id . '
                ORDER BY shifts.route_id
            ');

            //select query route_group_debt_any data the shifts in debt
            $shift_debt2 = DB::select('
                SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                INNER JOIN history_shifts ON shifts.id = history_shifts.shift_id
                WHERE shifts.collected = 1
                AND shifts.shift_destroy != 1
                AND shifts.ended < "' . $from_date . '"
                AND history_shifts.created_at >= "' . $from_date . '"
                AND routes.company_id = ' . (int) $company_id . '
                ORDER BY shifts.route_id
            ');

            $shifts = DB::table('shifts')
                ->where('routes.company_id', $company_id)
                ->where('ended', '>=', $from_date)
                ->where('ended', '<=', $to_date)
                ->where('shift_destroy', '!=', 1)
                ->join('routes', 'shifts.route_id', '=', 'routes.id')
                ->select(
                    'shifts.id',
                    'shifts.route_id',
                    'shifts.vehicle_id',
                    'shifts.user_id',
                    'shifts.subdriver_id',
                    'shifts.collected',
                    'routes.number as route_number'
                )
                ->orderBy('shifts.route_id')
                ->get()
                ->toArray();

            //select query shift_now_yesterday_result data shifts collected day now of shifts yesterday
            $shift_now_yesterday = DB::select('
                SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number, shifts.user_id, shifts.subdriver_id, shifts.vehicle_id,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                INNER JOIN history_shifts ON history_shifts.shift_id = shifts.id
                WHERE routes.company_id = ' . (int) $company_id . '
                AND shifts.ended < "' . $from_date . '"
                AND shifts.collected = 1
                AND shifts.shift_destroy != 1
                AND history_shifts.created_at >= "' . $from_date . '"
                AND history_shifts.created_at <= "' . $to_date . '"
                ORDER BY shifts.route_id
            ');
        } else {

            //select query route_group_debt_any data the shifts in debt
            $shift_debt1 = DB::select('
                    SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                WHERE shifts.collected = 0
                AND shifts.shift_destroy != 1
                AND shifts.ended < "' . $from_date . '"
                AND routes.company_id = ' . (int) $company_id . '
                AND routes.id = ' . (int) $route_id . '
                ORDER BY shifts.route_id
            ');

            //select query route_group_debt_any data the shifts in debt
            $shift_debt2 = DB::select('
                SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                INNER JOIN history_shifts ON shifts.id = history_shifts.shift_id
                WHERE shifts.collected = 1
                AND shifts.shift_destroy != 1
                AND shifts.ended < "' . $from_date . '"
                AND history_shifts.created_at >= "' . $from_date . '"
                AND routes.company_id = ' . (int) $company_id . '
                AND routes.id = ' . (int) $route_id . '
                ORDER BY shifts.route_id
            ');

            $shifts = DB::table('shifts')
                ->where('routes.company_id', $company_id)
                ->where('ended', '>=', $from_date)
                ->where('ended', '<=', $to_date)
                ->where('shift_destroy', '!=', 1)
                ->where('shifts.route_id', '=', $route_id)
                ->join('routes', 'shifts.route_id', '=', 'routes.id')
                ->select(
                    'shifts.id',
                    'shifts.route_id',
                    'shifts.vehicle_id',
                    'shifts.user_id',
                    'shifts.subdriver_id',
                    'shifts.collected',
                    'routes.number as route_number'
                )
                ->orderBy('shifts.route_id')
                ->get()
                ->toArray();

            //select query shift_now_yesterday_result data shifts collected day now of shifts yesterday
            $shift_now_yesterday = DB::select('
                SELECT shifts.id, shifts.collected, shifts.route_id, routes.number as route_number, shifts.user_id, shifts.subdriver_id, shifts.vehicle_id,
                (SELECT SUM(amount) from transactions where transactions.shift_id = shifts.id and transactions.type in ("pos", "deposit", "deposit_month") and transactions.ticket_destroy != 1) as total_amount
                FROM shifts
                INNER JOIN routes ON shifts.route_id = routes.id
                INNER JOIN history_shifts ON history_shifts.shift_id = shifts.id
                WHERE routes.company_id = ' . (int) $company_id . '
                AND routes.id = ' . (int) $route_id . '
                AND shifts.ended < "' . $from_date . '"
                AND shifts.collected = 1
                AND shifts.shift_destroy != 1
                AND history_shifts.created_at >= "' . $from_date . '"
                AND history_shifts.created_at <= "' . $to_date . '"
                ORDER BY shifts.route_id
            ');
        }

        //merge $shift_debt1, $shift_debt2
        $shift_debt =  array_merge($shift_debt1, $shift_debt2);
        usort($shift_debt, array($this, "cmp_route_id"));

        //handle the shifts debt
        if (count($shift_debt) > 0) {

            $route_debt_obj = new \stdClass;
            $route_debt_obj->route_id = 0;
            $route_debt_obj->route_number = '';
            $route_debt_obj->total_debt = 0;
            $route_debt_obj->key_handle = 'debt';

            foreach ($shift_debt as $key => $value) {

                $value = (object) $value;

                $route_debt_obj->route_id = $value->route_id;
                $route_debt_obj->route_number = $value->route_number;
                $route_debt_obj->key_handle = 'debt';
                $route_debt_obj->total_debt += $value->total_amount;
                $route_group_debt_all['total_debt'] += $value->total_amount;

                if ($key + 1 < count($shift_debt)) {
                    $key1_obj = (object) $shift_debt[$key + 1];
                    $key_obj = (object) $shift_debt[$key];
                }

                if ($key + 1 == count($shift_debt) || ($key + 1 < count($shift_debt) && $key_obj->route_id != $key1_obj->route_id)) {

                    $route_group_debt_any[] =  $route_debt_obj;

                    if ($key + 1 < count($shift_debt)) {

                        $route_debt_obj = new \stdClass;
                        $route_debt_obj->total_debt = 0;
                    }
                }
            }
        }

        //hendle the shifts by now _ yesterday
        if (count($shift_now_yesterday) > 0) {

            $yesterday_obj = new \stdClass;
            $yesterday_obj->total_debt = 0;
            $yesterday_obj->total_collected = 0;
            $yesterday_obj->total_not_collected = 0;
            $yesterday_obj->key_handle = 'yesterday';

            foreach ($shift_now_yesterday as $key => $value) {

                if ($value->collected == 1) {

                    $value_tmp = [
                        'vehicle_id' => $value->vehicle_id,
                        'driver_name' => '',
                        'subdriver_name' => '',
                        'route_number' => $value->route_number ?? '',
                        'license_plate' => '',
                        'role_id' => '',
                        'route_id' => $value->route_id,
                        'total_debt' => $value->total_amount ?? 0,
                        'total_collected' => $value->total_amount ?? 0,
                        'collected' => $value->collected,
                        'staff_collected' => '',
                        'staff_collected_id' => 0,
                        'staff_collected_name' => 0,
                        'date_collected' => ''
                    ];

                    if ($value->user_id) {
                        $user = $this->users->getUserByKey('id', $value->user_id, $company_id);
                        $value_tmp['driver_name'] = $user['fullname'];
                        $value_tmp['role_id'] = $user->role->id;
                    }

                    if ($value->subdriver_id) {
                        $user = $this->users->getUserByKey('id', $value->subdriver_id, $company_id);
                        $value_tmp['subdriver_name'] = $user['fullname'];
                        $value_tmp['role_id'] = $user->role->id;
                    }

                    if ($value->vehicle_id) {
                        $vehicle = $this->vehicles->getVehicleById($value->vehicle_id);
                        $value_tmp['license_plate'] = $vehicle->license_plates;
                    }

                    $history_shift = $this->history_shifts->getHistoryShiftByShiftId($value->id);
                    if ($history_shift) {
                        $value_tmp['staff_collected'] = $history_shift->user->pin_code;
                        $value_tmp['staff_collected_id'] = $history_shift->user->id;
                        $value_tmp['staff_collected_name'] = $history_shift->user->fullname;
                        $value_tmp['date_collected'] = $history_shift->created_at;
                    }

                    $yesterday_obj->route_id = $value->route_id;
                    $yesterday_obj->route_number = $value->route_number;
                    $yesterday_obj->key_handle = 'yesterday';
                    $yesterday_obj->total_debt += $value_tmp['total_debt'];
                    $yesterday_obj->total_collected += $value_tmp['total_collected'];
                    $yesterday_obj->total_not_collected -= $value_tmp['total_collected'];

                    // $shift_now_yesterday_all['total_debt'] +=  $value_tmp['total_debt'];
                    // $shift_now_yesterday_all['total_collected'] += $value_tmp['total_collected'];
                    // $shift_now_yesterday_all['total_not_collected'] -= $value_tmp['total_collected'];

                    if ($key + 1 == count($shift_now_yesterday) || ($key + 1 < count($shift_now_yesterday) && $shift_now_yesterday[$key]->route_id != $shift_now_yesterday[$key + 1]->route_id)) {

                        $route_group_yesterday_result[] = $yesterday_obj;

                        if ($key + 1 < count($shift_now_yesterday)) {

                            $yesterday_obj = new \stdClass;
                            $yesterday_obj->total_debt = 0;
                            $yesterday_obj->total_collected = 0;
                            $yesterday_obj->total_not_collected = 0;
                        }
                    }
                    array_push($shift_now_yesterday_result, $value_tmp);
                }
            }
        }

        //handle the shift_detail by date
        if (count($shifts) > 0) {

            $route_obj = new \stdClass;
            $route_obj->total_debt = 0;
            $route_obj->role_id = 0;
            $route_obj->count_ticket_pos = 0;
            $route_obj->total_price_pos = 0;
            $route_obj->count_ticket_charge = 0;
            $route_obj->total_price_charge = 0;
            $route_obj->total_price_discount = 0;
            $route_obj->total_price_collected = 0;
            $route_obj->count_ticket_online = 0;
            $route_obj->total_price_online = 0;
            $route_obj->count_ticket_month = 0;
            $route_obj->count_revenue_ticket = 0;
            $route_obj->count_discount_ticket = 0;
            $route_obj->count_collected_ticket = 0;
            $route_obj->total_price_deposit = 0;
            $route_obj->total_collected = 0;
            $route_obj->total_not_collected = 0;
            $route_obj->key_handle = 'shift_detail';

            foreach ($shifts as $k => $shift) {

                $data = [
                    'driver_name' => '',
                    'license_plates' => '',
                    'subdriver_name' => '',
                    'role_id' => 0,
                    'count_ticket_pos' => 0,
                    'total_price_pos' => 0,
                    'count_ticket_charge' => 0,
                    'total_price_charge' => 0,
                    'total_price_discount' => 0,
                    'total_price_collected' => 0,
                    'count_ticket_online' => 0,
                    'total_price_online' => 0,
                    'count_ticket_month' => 0,
                    'count_revenue_ticket' => 0,
                    'count_discount_ticket' => 0,
                    'count_collected_ticket' => 0,
                    'total_price_deposit' => 0,
                    'total_collected' => 0,
                    'total_not_collected' => 0,
                    'staff_collected' => '',
                    'staff_collected_id' => 0,
                    'staff_collected_name' => '',
                    'date_collected' => ''
                ];

                $tmp_collected = 0;

                if ($shift->user_id) {
                    $user = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $data['driver_name'] = $user['fullname'];
                    $data['role_id'] = $user->role->id;
                }

                if ($shift->subdriver_id) {
                    $user = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $data['subdriver_name'] = $user['fullname'];
                    $data['role_id'] = $user->role->id;
                }

                if ($shift->vehicle_id) {
                    $vehicle = $this->vehicles->getVehicleById($shift->vehicle_id);
                    $data['license_plates'] = $vehicle->license_plates;
                }

                $transactions = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id],
                    ['ticket_destroy', '!=', 1]
                ]);

                if (count($transactions) > 0) {

                    foreach ($transactions as $transaction) {

                        $type = $transaction->type;
                        $amount = $transaction->amount;

                        if ($transaction->type == 'pos') {
                            $data['count_ticket_pos'] += 1;
                            $data['total_price_pos'] += (float) $transaction->amount;
                            $tmp_collected += (float) $transaction->amount;
                        }

                        if ($transaction->type == 'charge') {

                            $data['count_ticket_charge'] += 1;
                            $ticket_price = $this->ticket_prices->getPriceById($transaction->ticket_price_id);
                            $data['total_price_collected'] += (float) $transaction->amount;
                            if ($ticket_price) {
                                $data['total_price_charge'] += (float) $ticket_price->price;
                                $data['total_price_discount'] += (float) $ticket_price->price - $transaction->amount;
                            }
                        }

                        if ($transaction->type == 'deposit' || $transaction->type == 'deposit_month') {
                            $data['total_price_deposit'] += (float) $transaction->amount;
                            $tmp_collected += (float) $transaction->amount;
                        }

                        if ($transaction->type == 'qrcode') {
                            $data['count_ticket_online'] += 1;
                            $data['total_price_online'] += (float) $transaction->amount;
                        }

                        if ($transaction->type == 'charge_month') {
                            $data['count_ticket_month'] += 1;
                        }
                    }
                }

                $data['count_revenue_ticket'] = ($data['total_price_pos'] + $data['total_price_charge'] + $data['total_price_online']);
                $data['count_discount_ticket'] =  $data['total_price_discount'];
                $data['count_collected_ticket'] = ($data['total_price_pos'] + $data['total_price_collected']);

                if ($shift->collected == 1) {
                    $where_arr = ["from_date" => $from_date,  "to_date" => $to_date,  "shift_id" => $shift->id];
                    $history_shift = $this->history_shifts->getHistoryShiftByOption($where_arr, 'created_at');
                    if ($history_shift) {
                        $data['total_collected'] = $tmp_collected;
                        $data['staff_collected'] = $history_shift->user->pin_code;
                        $data['staff_collected_id'] = $history_shift->user->id;
                        $data['staff_collected_name'] = $history_shift->user->fullname;
                        $data['date_collected'] = $history_shift->created_at;
                    } else {
                        $data['total_not_collected'] = $tmp_collected;
                    }
                } else {
                    $data['total_not_collected'] = $tmp_collected;
                }

                $route_tmp = array(
                    'vehicle_id' => $shift->vehicle_id,
                    'license_plate' =>  $data['license_plates'],
                    'role_id' =>  $data['role_id'],
                    'driver_name' => $data['driver_name'],
                    'subdriver_name' => $data['subdriver_name'],
                    'route_number' => $shift->route_number,
                    'route_id' => $shift->route_id,
                    'count_ticket_pos' => $data['count_ticket_pos'],
                    'total_price_pos' => $data['total_price_pos'],
                    'count_ticket_charge' => $data['count_ticket_charge'],
                    'total_price_charge' => $data['total_price_charge'],
                    'total_price_discount' => $data['total_price_discount'],
                    'total_price_collected' => $data['total_price_collected'],
                    'count_ticket_online' => $data['count_ticket_online'],
                    'total_price_online' => $data['total_price_online'],
                    'count_ticket_month' => $data['count_ticket_month'],
                    'count_revenue_ticket' =>   $data['count_revenue_ticket'],
                    'count_discount_ticket' =>  $data['count_discount_ticket'],
                    'count_collected_ticket' =>   $data['count_collected_ticket'],
                    'total_price_deposit' =>  $data['total_price_deposit'],
                    'total_collected' =>    $data['total_collected'],
                    'total_not_collected' =>  $data['total_not_collected'],
                    'staff_collected' =>  $data['staff_collected'],
                    'staff_collected_id' =>  $data['staff_collected_id'],
                    'staff_collected_name' =>  $data['staff_collected_name'],
                    'date_collected' => $data['date_collected']
                );

                array_push($route_detail, $route_tmp);

                $route_obj->route_number = $shift->route_number;
                $route_obj->route_id = $shift->route_id;
                $route_obj->role_id = $data['role_id'];
                $route_obj->key_handle = 'shift_detail';
                $route_obj->count_ticket_pos += $data['count_ticket_pos'];
                $route_obj->total_price_pos += $data['total_price_pos'];
                $route_obj->count_ticket_charge += $data['count_ticket_charge'];
                $route_obj->total_price_charge += $data['total_price_charge'];
                $route_obj->total_price_discount += $data['total_price_discount'];
                $route_obj->total_price_collected += $data['total_price_collected'];
                $route_obj->count_ticket_online += $data['count_ticket_online'];
                $route_obj->total_price_online += $data['total_price_online'];
                $route_obj->count_ticket_month += $data['count_ticket_month'];
                $route_obj->count_revenue_ticket += $data['count_revenue_ticket'];
                $route_obj->count_discount_ticket += $data['count_discount_ticket'];
                $route_obj->count_collected_ticket += $data['count_collected_ticket'];
                $route_obj->total_price_deposit += $data['total_price_deposit'];
                $route_obj->total_collected += $data['total_collected'];
                $route_obj->total_not_collected += $data['total_not_collected'];

                if ($k + 1 == count($shifts) || ($k + 1 < count($shifts) && $shifts[$k]->route_id != $shifts[$k + 1]->route_id)) {

                    $route_group_detail[] =  $route_obj;

                    if ($k + 1 < count($shifts)) {

                        $route_obj = new \stdClass;
                        $route_obj->total_debt = 0;
                        $route_obj->role_id = 0;
                        $route_obj->count_ticket_pos = 0;
                        $route_obj->total_price_pos = 0;
                        $route_obj->count_ticket_charge = 0;
                        $route_obj->total_price_charge = 0;
                        $route_obj->total_price_discount = 0;
                        $route_obj->total_price_collected = 0;
                        $route_obj->count_ticket_online = 0;
                        $route_obj->total_price_online = 0;
                        $route_obj->count_ticket_month = 0;
                        $route_obj->count_revenue_ticket = 0;
                        $route_obj->count_discount_ticket = 0;
                        $route_obj->count_collected_ticket = 0;
                        $route_obj->total_price_deposit = 0;
                        $route_obj->total_collected = 0;
                        $route_obj->total_not_collected = 0;
                    }
                }
            }
        }

        if (count($route_group_debt_any) > 0) {
            //DEBT ALL
            foreach ($route_group_debt_any as $key_debt => $value_debt) {

                $group_tmp_debt = [
                    'route_id' => $value_debt->route_id,
                    'route_number' => $value_debt->route_number,
                    'total_debt' => $value_debt->total_debt,
                    'key_handle' => $value_debt->key_handle,
                    'count_ticket_pos' => 0,
                    'total_price_pos' => 0,
                    'count_ticket_charge' => 0,
                    'total_price_charge' => 0,
                    'total_price_discount' => 0,
                    'total_price_collected' => 0,
                    'count_ticket_online' => 0,
                    'total_price_online' => 0,
                    'count_ticket_month' => 0,
                    'count_revenue_ticket' => 0,
                    'count_discount_ticket' => 0,
                    'count_collected_ticket' => 0,
                    'total_price_deposit' => 0,
                    'total_collected' => 0,
                    'total_not_collected' => $value_debt->total_debt,
                ];
                array_push($route_group_detail, $group_tmp_debt);
            }
        }

        if (count($route_group_yesterday_result) > 0) {

            //THU HOM NAY
            foreach ($route_group_yesterday_result as $key_now =>  $value_now) {

                $group_tmp_yesterday = [
                    'route_id' => $value_now->route_id,
                    'route_number' => $value_now->route_number,
                    'total_debt' => $value_now->total_debt,
                    'key_handle' => $value_now->key_handle,
                    'count_ticket_pos' => 0,
                    'total_price_pos' => 0,
                    'count_ticket_charge' => 0,
                    'total_price_charge' => 0,
                    'total_price_discount' => 0,
                    'total_price_collected' => 0,
                    'count_ticket_online' => 0,
                    'total_price_online' => 0,
                    'count_ticket_month' => 0,
                    'count_revenue_ticket' => 0,
                    'count_discount_ticket' => 0,
                    'count_collected_ticket' => 0,
                    'total_price_deposit' => 0,
                    'total_collected' => $value_now->total_collected,
                    'total_not_collected' => $value_now->total_not_collected,
                ];
                array_push($route_group_detail, $group_tmp_yesterday);
            }
        }

        $route_group_colected_all = [
            'total_debt' => 0,
            'count_ticket_pos' => 0,
            'total_price_pos' => 0,
            'count_ticket_charge' => 0,
            'total_price_charge' => 0,
            'total_price_discount' => 0,
            'total_price_collected' => 0,
            'count_ticket_online' => 0,
            'total_price_online' => 0,
            'count_ticket_month' => 0,
            'count_revenue_ticket' => 0,
            'count_discount_ticket' => 0,
            'count_collected_ticket' => 0,
            'total_price_deposit' => 0,
            'total_collected' => 0,
            'total_not_collected' => 0,
        ];

        usort($route_group_detail, array($this, "cmp_route_id"));
        $route_group_colected_any = [];

        if (count($route_group_detail) > 0) {

            $route_collected_obj = new \stdClass;
            $route_collected_obj->total_debt_route = 0;
            $route_collected_obj->count_ticket_pos = 0;
            $route_collected_obj->total_price_pos = 0;
            $route_collected_obj->count_ticket_charge = 0;
            $route_collected_obj->total_price_charge = 0;
            $route_collected_obj->total_price_discount = 0;
            $route_collected_obj->total_price_collected = 0;
            $route_collected_obj->count_ticket_online = 0;
            $route_collected_obj->total_price_online = 0;
            $route_collected_obj->count_ticket_month = 0;
            $route_collected_obj->count_revenue_ticket = 0;
            $route_collected_obj->count_discount_ticket = 0;
            $route_collected_obj->count_collected_ticket = 0;
            $route_collected_obj->total_price_deposit = 0;
            $route_collected_obj->total_collected = 0;
            $route_collected_obj->total_not_collected = 0;

            foreach ($route_group_detail as $key => $vl) {
                $vl = (object) $vl;
                $route_collected_obj->route_id = $vl->route_id;
                // $route_collected_obj->role_id = $vl->role_id;
                $route_collected_obj->route_number =  $vl->route_number;

                if ($vl->key_handle == 'debt') {

                    $route_collected_obj->total_not_collected += $vl->total_not_collected;
                    $route_group_colected_all['total_not_collected'] += $vl->total_not_collected;
                }

                if ($vl->key_handle == 'yesterday') {

                    $route_collected_obj->total_debt_route +=  $vl->total_debt;
                    $route_collected_obj->total_not_collected += $vl->total_not_collected;
                    $route_collected_obj->total_collected += $vl->total_collected;
                    $route_group_colected_all['total_debt'] += $vl->total_debt;
                    $route_group_colected_all['total_not_collected'] += $vl->total_not_collected;
                    $route_group_colected_all['total_collected'] += $vl->total_collected;
                }

                if ($vl->key_handle == 'shift_detail') {

                    $route_collected_obj->total_not_collected += $vl->total_not_collected;
                    $route_collected_obj->total_collected += $vl->total_collected;
                    $route_group_colected_all['total_not_collected'] += $vl->total_not_collected;
                    $route_group_colected_all['total_collected'] += $vl->total_collected;
                }

                $route_collected_obj->count_ticket_pos +=  $vl->count_ticket_pos;
                $route_collected_obj->total_price_pos +=  $vl->total_price_pos;
                $route_collected_obj->count_ticket_charge +=  $vl->count_ticket_charge;
                $route_collected_obj->total_price_discount +=  $vl->total_price_discount;
                $route_collected_obj->total_price_collected +=  $vl->total_price_collected;
                $route_collected_obj->count_ticket_online +=  $vl->count_ticket_online;
                $route_collected_obj->total_price_online +=  $vl->total_price_online;
                $route_collected_obj->count_ticket_month +=  $vl->count_ticket_month;
                $route_collected_obj->total_price_charge +=  $vl->total_price_charge;
                $route_collected_obj->count_revenue_ticket +=  $vl->count_revenue_ticket;
                $route_collected_obj->count_discount_ticket +=  $vl->count_discount_ticket;
                $route_collected_obj->count_collected_ticket +=  $vl->count_collected_ticket;
                $route_collected_obj->total_price_deposit +=  $vl->total_price_deposit;

                if ($key + 1 < count($route_group_detail)) {
                    $key1_obj = (object) $route_group_detail[$key + 1];
                    $key_obj = (object) $route_group_detail[$key];
                }

                if ($key + 1 == count($route_group_detail) || ($key + 1 < count($route_group_detail) && $key_obj->route_id != $key1_obj->route_id)) {

                    $route_collected_obj = (array)$route_collected_obj;
                    $route_group_colected_any[] = $route_collected_obj;


                    if ($key + 1 < count($route_group_detail)) {

                        $route_collected_obj = new \stdClass;
                        $route_collected_obj->total_debt_route = 0;
                        $route_collected_obj->count_ticket_pos = 0;
                        $route_collected_obj->total_price_pos = 0;
                        $route_collected_obj->count_ticket_charge = 0;
                        $route_collected_obj->total_price_charge = 0;
                        $route_collected_obj->total_price_discount = 0;
                        $route_collected_obj->total_price_collected = 0;
                        $route_collected_obj->count_ticket_online = 0;
                        $route_collected_obj->total_price_online = 0;
                        $route_collected_obj->count_ticket_month = 0;
                        $route_collected_obj->count_revenue_ticket = 0;
                        $route_collected_obj->count_discount_ticket = 0;
                        $route_collected_obj->count_collected_ticket = 0;
                        $route_collected_obj->total_price_deposit = 0;
                        $route_collected_obj->total_collected = 0;
                        $route_collected_obj->total_not_collected = 0;
                    }
                }

                $route_group_colected_all['count_ticket_pos'] +=  $vl->count_ticket_pos;
                $route_group_colected_all['total_price_pos'] +=   $vl->total_price_pos;
                $route_group_colected_all['count_ticket_charge'] +=   $vl->count_ticket_charge;
                $route_group_colected_all['total_price_charge'] +=   $vl->total_price_charge;
                $route_group_colected_all['total_price_discount'] +=   $vl->total_price_discount;
                $route_group_colected_all['total_price_collected'] +=   $vl->total_price_collected;
                $route_group_colected_all['count_ticket_online'] +=  $vl->count_ticket_online;
                $route_group_colected_all['total_price_online'] +=   $vl->total_price_online;
                $route_group_colected_all['count_ticket_month'] +=   $vl->count_ticket_month;
                $route_group_colected_all['count_revenue_ticket'] +=   $vl->count_revenue_ticket;
                $route_group_colected_all['count_discount_ticket'] +=   $vl->count_discount_ticket;
                $route_group_colected_all['count_collected_ticket'] +=   $vl->count_collected_ticket;
                $route_group_colected_all['total_price_deposit'] +=   $vl->total_price_deposit;
            }
        }

        usort($route_detail, array($this, "cmp_vehicle_id"));
        usort($shift_now_yesterday_result, array($this, "cmp_vehicle_id"));

        $result_arr = [];
        $route_result = [];

        //handle data after merge -------------------------------------------------
        $result_merge = array_merge((array)$route_detail,(array)$shift_now_yesterday_result,(array)$route_group_colected_any,(array)$route_group_debt_any);
        $result_merge = collect($result_merge)->groupBy('route_id')->toArray();
        ksort($result_merge);

        if (count($result_merge) > 0) {

            foreach ($result_merge as $keys => $values) {

                for ($i = 0; $i < count($values); $i++) {

                    $values[$i] = (object) $values[$i];

                    if (isset($values[count($values) - 2]->total_debt_route)) {

                        if ($i < (count($values) - 2))   $result_arr[] = $values[$i];

                        if ($i == (count($values) - 2)) {

                            $obj = new \stdClass;
                            $obj->count_collected_ticket   =  $values[$i]->count_collected_ticket;
                            $obj->count_discount_ticket    =  $values[$i]->count_discount_ticket;
                            $obj->count_revenue_ticket     =  $values[$i]->count_revenue_ticket;
                            $obj->count_ticket_charge      =  $values[$i]->count_ticket_charge;
                            $obj->count_ticket_month       =  $values[$i]->count_ticket_month;
                            $obj->count_ticket_online      =  $values[$i]->count_ticket_online;
                            $obj->count_ticket_pos         =  $values[$i]->count_ticket_pos;
                            $obj->route_id                 =  $values[$i]->route_id;
                            $obj->route_number             =  $values[$i]->route_number;
                            $obj->total_collected          =  $values[$i]->total_collected;
                            $obj->total_debt_route         =  $values[$i]->total_debt_route;
                            $obj->total_not_collected      =  $values[$i]->total_not_collected;
                            $obj->total_price_charge       =  $values[$i]->total_price_charge;
                            $obj->total_price_collected    =  $values[$i]->total_price_collected;
                            $obj->total_price_deposit      =  $values[$i]->total_price_deposit;
                            $obj->total_price_discount     =  $values[$i]->total_price_discount;
                            $obj->total_price_online       =  $values[$i]->total_price_online;
                            $obj->total_price_pos          =  $values[$i]->total_price_pos;

                            $obj->title_debt_any = 'Nợ ngày trước tuyến ' . $values[$i]->route_number;
                            $obj->total_debt_any = $values[count($values) - 1]->total_debt;

                            $result_arr[] = $obj;
                            break;
                        }
                    } else {
                      if (isset($values[count($values) - 1]->total_debt_route)) {

                          if ($i < (count($values) - 1))   $result_arr[] = $values[$i];

                          if ($i == (count($values) - 1)) {

                              $obj = new \stdClass;
                              $obj->count_collected_ticket   =  $values[$i]->count_collected_ticket;
                              $obj->count_discount_ticket    =  $values[$i]->count_discount_ticket;
                              $obj->count_revenue_ticket     =  $values[$i]->count_revenue_ticket;
                              $obj->count_ticket_charge      =  $values[$i]->count_ticket_charge;
                              $obj->count_ticket_month       =  $values[$i]->count_ticket_month;
                              $obj->count_ticket_online      =  $values[$i]->count_ticket_online;
                              $obj->count_ticket_pos         =  $values[$i]->count_ticket_pos;
                              $obj->route_id                 =  $values[$i]->route_id;
                              $obj->route_number             =  $values[$i]->route_number;
                              $obj->total_collected          =  $values[$i]->total_collected;
                              $obj->total_debt_route         =  $values[$i]->total_debt_route;
                              $obj->total_not_collected      =  $values[$i]->total_not_collected;
                              $obj->total_price_charge       =  $values[$i]->total_price_charge;
                              $obj->total_price_collected    =  $values[$i]->total_price_collected;
                              $obj->total_price_deposit      =  $values[$i]->total_price_deposit;
                              $obj->total_price_discount     =  $values[$i]->total_price_discount;
                              $obj->total_price_online       =  $values[$i]->total_price_online;
                              $obj->total_price_pos          =  $values[$i]->total_price_pos;

                              $obj->title_debt_any = 'Nợ ngày trước tuyến ' . $values[$i]->route_number;
                              $obj->total_debt_any = 0;

                              $result_arr[] = $obj;
                              break;
                          }
                      } else {
                          if ($i < (count($values) - 1))   $result_arr[] = $values[$i];

                          if ($i == (count($values) - 1)) {

                              $obj = new \stdClass;
                              $obj->count_collected_ticket   =  0;
                              $obj->count_discount_ticket    =  0;
                              $obj->count_revenue_ticket     =  0;
                              $obj->count_ticket_charge      =  0;
                              $obj->count_ticket_month       =  0;
                              $obj->count_ticket_online      =  0;
                              $obj->count_ticket_pos         =  0;
                              $obj->route_id                 =  0;
                              $obj->route_number             =  0;
                              $obj->total_collected          =  0;
                              $obj->total_debt_route         =  0;
                              $obj->total_not_collected      =  0;
                              $obj->total_price_charge       =  0;
                              $obj->total_price_collected    =  0;
                              $obj->total_price_deposit      =  0;
                              $obj->total_price_discount     =  0;
                              $obj->total_price_online       =  0;
                              $obj->total_price_pos          =  0;

                              $obj->title_debt_any = 'Nợ ngày trước tuyến ' . $values[$i]->route_number;
                              $obj->total_debt_any = 0;

                              $result_arr[] = $obj;
                              break;
                          }

                      }
                    }
                }
            }
        }

        $merge_staff_collected = array_merge((array)$route_detail,(array)$shift_now_yesterday_result);
        $merge_staff_collected = collect($merge_staff_collected)->groupBy('staff_collected_id')->toArray();
        $total_collecter = [];

        if (count($merge_staff_collected) > 0) {

            foreach ($merge_staff_collected as $keys => $values) {

                if ((int) $keys != 0) {

                    $obj_total_collecter = new \stdClass;
                    $obj_total_collecter->fullname = $values[0]['staff_collected_name'];
                    $obj_total_collecter->total_amount = collect($values)->sum('total_collected');

                    $total_collecter[] = $obj_total_collecter;
                }
            }
        }
        //end handle data after merge ------------------------------------------------

        $route_result['route_group_debt_all'] = $route_group_debt_all;
        $route_result['route_detail'] = $result_arr;
        $route_result['route_group_colected_all'] = $route_group_colected_all;
        $route_result['total_collecter'] = $total_collecter;
        $route_result['isCheckModuleApp'] = $this->isCheckModuleApp($company_id);
        // $route_result['shift_now_yesterday_all'] = $shift_now_yesterday_all;
        // $route_result['route_group_colected_any'] = $route_group_colected_any;
        // $route_result['shift_now_yesterday_result'] = $shift_now_yesterday_result;
        // $route_result['route_detail'] = $route_detail;
        // $route_result['route_group_debt_any'] = $route_group_debt_any;
        return $route_result;
    }

    public function viewDetailTransactionSearch($data)
    {
        $company_id = $data['company_id'];
        $start_date = date("Y-m-d 00:00:00", strtotime($data['date_from']));
        $end_date = date("Y-m-d 23:59:59", strtotime($data['date_to']));
        $type_request = $data['type_payment'];
        $type_query = [];

        switch ($type_request) {
            case 'all':
                $type_query = ['pos', 'pos_taxi', 'charge', 'charge_free', 'charge_taxi', 'deposit', 'qrcode', 'qrcode_taxi', 'topup_momo', 'app:1'];
                break;

            case 'pos':
                $type_query = ['pos', 'pos_taxi'];
                break;

            case 'charge':
                $type_query = ['charge', 'charge_taxi'];
                break;

            case 'charge_free':
                $type_query = ['charge_free'];
                break;

            case 'deposit':
                $type_query = ['deposit', 'topup_momo'];
                break;

            case 'online':
                $type_query = ['app:1', 'qrcode', 'qrcode_taxi'];
                break;
        }

        $transactions = Transaction::where('transactions.company_id', $company_id)
            ->where('transactions.ticket_destroy', '!=', 1)
            ->whereIn('transactions.type', $type_query)
            ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
            ->where('shifts.ended', '>=', $start_date)
            ->where('shifts.ended', '<=', $end_date)
            ->join('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
            ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
            ->leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
            ->select(
                'transactions.ticket_number',
                'transactions.ticket_price_id',
                'transactions.amount',
                'transactions.activated',
                'transactions.type',
                'vehicles.license_plates',
                'transactions.station_id',
                'bus_stations.name as station_name',
                'ticket_prices.price as ticket_price'
            )
            ->orderByRaw('transactions.type, vehicles.id, transactions.activated')
            ->get();
        if (count($transactions) < 0) return response('Transaction not found', 404);

        $obj = new \stdClass;
        $obj->pos = 0;
        $obj->pos_taxi = 0;
        $obj->online = 0;
        $obj->online_taxi = 0;
        $obj->charge = 0;
        $obj->charge_taxi = 0;
        $obj->charge_free = 0;
        $obj->deposit = 0;
        $obj->topup_momo = 0;

        $obj->pos_num = 0;
        $obj->pos_taxi_num = 0;
        $obj->online_num = 0;
        $obj->online_taxi_num = 0;
        $obj->charge_num = 0;
        $obj->charge_taxi_num = 0;
        $obj->charge_free_num = 0;
        $obj->deposit_num = 0;
        $obj->topup_momo_num = 0;

        $total_transactions = [
            "total_price" => 0,
            "total_discount" => 0,
            "total_collected" => 0,
        ];

        if (count($transactions) > 0) {

            foreach ($transactions as $transaction) {

                if ($transaction->ticket_price_id) {
                    $ticket_price = $this->ticket_prices->getPriceById($transaction->ticket_price_id);
                    if ($ticket_price) {
                        $total_transactions['total_price'] += $ticket_price->price;
                        $total_transactions['total_discount'] += $ticket_price->price - $transaction->amount;
                    }
                }
                $total_transactions['total_collected'] += $transaction->amount;


                if ($transaction->type == 'pos') {
                    $obj->pos_num += 1;
                    $obj->pos += $transaction->amount;
                }

                if ($transaction->type == 'pos_taxi') {
                    $obj->pos_taxi_num += 1;
                    $obj->pos_taxi += $transaction->amount;
                }

                if ($transaction->type == 'app:1' || $transaction->type == 'qrcode') {
                    $obj->online_num += 1;
                    $obj->online += $transaction->amount;
                }

                if ($transaction->type == 'qrcode_taxi') {
                    $obj->online_taxi_num += 1;
                    $obj->online_taxi += $transaction->amount;
                }

                if ($transaction->type == 'charge') {
                    $obj->charge_num += 1;
                    $obj->charge += $transaction->amount;
                }

                if ($transaction->type == 'charge_taxi') {
                    $obj->charge_taxi_num += 1;
                    $obj->charge_taxi += $transaction->amount;
                }

                if ($transaction->type == 'charge_free') {
                    $obj->charge_free_num += 1;
                    $obj->charge_free += $transaction->amount;
                }

                if ($transaction->type == 'deposit') {
                    $obj->deposit_num += 1;
                    $obj->deposit += $transaction->amount;
                }

                if ($transaction->type == 'topup_momo') {
                    $obj->topup_momo_num += 1;
                    $obj->topup_momo += $transaction->amount;
                }
            }
        }

        $result['transactions'] = $transactions;
        $result['total_transactions'] = $total_transactions;
        $result['isCheckModuleApp'] = $this->isCheckModuleApp($data['company_id']);

        $result['pos'] = ['number' => $obj->pos_num, 'total' => $obj->pos];
        $result['pos_taxi'] = ['number' => $obj->pos_taxi_num, 'total' => $obj->pos_taxi];
        $result['online'] = ['number' => $obj->online_num, 'total' => $obj->online];
        $result['online_taxi'] = ['number' => $obj->online_taxi_num, 'total' => $obj->online_taxi];
        $result['charge'] = ['number' => $obj->charge_num, 'total' => $obj->charge];
        $result['charge_taxi'] = ['number' => $obj->charge_taxi_num, 'total' => $obj->charge_taxi];
        $result['charge_free'] = ['number' => $obj->charge_free_num, 'total' => $obj->charge_free];
        $result['deposit'] = ['number' => $obj->deposit_num, 'total' => $obj->deposit];
        $result['topup_momo'] = ['number' => $obj->topup_momo_num, 'total' => $obj->topup_momo];

        return $result;
    }

    public function viewTransactionOnline($data)
    {

        $company_id = $data['company_id'];
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $partner = $data['partner'];
        $type_request = $data['type'];
        $company_id = $data['company_id'];

        // date
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $result = [];
        $transactions = [];
        $type= [];
        $total_onlines = [
            'count' => 0,
            'total' => 0
        ];

        if($type_request == 'payment'){ $type = ['qrcode','qrcode_taxi']; }
        if($type_request == 'topup'){ $type = ['topup_momo']; }

        if ($partner == "momo") {

            $transactions = Transaction::where('transactions.company_id', $company_id)
                ->where('transactions.ticket_destroy', '!=', 1)
                ->whereIn('transactions.type', $type)
                ->where('transactions.activated', '>=', $from_date)
                ->where('transactions.activated', '<=', $to_date)
                ->select('transactions.*')
                ->get();

            if (count($transactions) > 0) {
                foreach ($transactions as $transaction) {
                    $total_onlines['count'] += 1;
                    $total_onlines['total'] += $transaction->amount;
                }
            }
        }

        if($partner == "vietinbank"){
            $result['transactions'] = $transactions;
        }


        $result['transactions'] = $transactions;
        $result['total_onlines'] = $total_onlines;
        $result['isCheckModuleApp'] = $this->isCheckModuleApp($company_id);
        return  $result;
    }

    public function viewTicket($data)
    {
        $company_id = (int)$data['company_id'];
        $from_date =  date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date =  date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $route_id = (int)$data['route_id'] ?? 0;
        $price_id = (int)$data['price_id'] ?? 0;

        $ticket_result = [];

        if($price_id > 0){

            $ticket_allocate = $this->ticket_allocates->getTotalTicketCreated([
                'company_id' => $company_id,
                'ticket_price_id' => $price_id,
                'to_date' => $to_date
            ]);
            $total_released = $ticket_allocate ? $ticket_allocate->end_number : 0;
            $ticket_price = $this->ticket_prices->getDataPriceById($price_id);

            if($route_id > 0) {

                $route = $this->routes->getRouteById($route_id);
                $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                            ->where([
                                ['transactions.company_id', $company_id],
                                ['shifts.route_id', $route_id],
                                ['transactions.activated', '>=', $from_date],
                                ['transactions.activated', '<=', $to_date],
                                ['transactions.ticket_destroy', '!=', 1],
                                ['transactions.ticket_price_id', '=', $price_id]
                            ])
                            ->whereIn('transactions.type', ['pos', 'charge', 'qrcode','deposit_month'])
                            ->selectRaw('transactions.type, count(transactions.type) as count_type, sum(transactions.amount) as sum_type')
                            ->groupBy('transactions.type')
                            ->get()
                            ->toArray();
                if (count($transactions) > 0) {
                    $tmp_arr = [
                        "price" => $ticket_price->price,
                        "order_code" => $ticket_price->ticketType['order_code'] ?? '',
                        "route_number" => $route ? $route->number : '',
                        "route_id" => $route ? $route->id : '',
                        "total_released" => $total_released,
                        "total_pos" => 0,
                        "total_deposit_month" => 0,
                        "total_charge" => 0,
                        "total_qrcode" => 0,
                        "total_revenue" => 0,
                        "total_collected" => 0,
                    ];
                    foreach ($transactions as  $transaction) {

                        if ($transaction['type'] == 'pos') $tmp_arr['total_pos'] += $transaction['count_type'];
                        if ($transaction['type'] == 'charge') $tmp_arr['total_charge'] += $transaction['count_type'];
                        if ($transaction['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $transaction['count_type'];
                        if ($transaction['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $transaction['count_type'];

                        $tmp_arr['total_revenue'] += $ticket_price->price * $transaction['count_type'];
                        $tmp_arr['total_collected'] += $transaction['sum_type'];
                    }
                    $ticket_result[] = $tmp_arr;
                }
            }elseif($route_id == 0){

                $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                            ->where([
                                ['transactions.company_id', $company_id],
                                ['transactions.activated', '>=', $from_date],
                                ['transactions.activated', '<=', $to_date],
                                ['transactions.ticket_destroy', '!=', 1],
                                ['transactions.ticket_price_id', '=', $price_id]
                            ])
                            ->whereIn('transactions.type', ['pos', 'charge', 'qrcode','deposit_month'])
                            ->selectRaw('shifts.route_id, transactions.type, count(transactions.type) as count_type, sum(transactions.amount) as sum_type')
                            ->groupBy('shifts.route_id','transactions.type')
                            ->get()
                            ->toArray();

                if (count($transactions) > 0) {

                    $total_tmp_arr = [
                        "route_number" => 'all',
                        "total_released" => $total_released,
                        "total_pos" => 0,
                        "total_charge" => 0,
                        "total_qrcode" => 0,
                        "total_deposit_month" => 0,
                        "total_revenue" => 0,
                        "total_collected" => 0,
                    ];

                    $transactions =  collect($transactions)->groupBy('route_id')->toArray();

                    foreach ($transactions as $key => $values) {

                        $route = $this->routes->getRouteById((int)$key);

                        $tmp_arr = [
                            "price" => $ticket_price->price,
                            "order_code" => $ticket_price->ticketType['order_code'] ?? '',
                            "route_number" => $route ? $route->number : '',
                            "route_id" => $route ? $route->id : 0,
                            "total_released" => $total_released,
                            "total_pos" => 0,
                            "total_charge" => 0,
                            "total_qrcode" => 0,
                            "total_deposit_month" => 0,
                            "total_revenue" => 0,
                            "total_collected" => 0,
                        ];

                        foreach ($values as $v) {

                            if ($v['type'] == 'pos') $tmp_arr['total_pos'] += $v['count_type'];
                            if ($v['type'] == 'charge') $tmp_arr['total_charge'] += $v['count_type'];
                            if ($v['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $v['count_type'];
                            if ($v['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $v['count_type'];

                            $tmp_arr['total_revenue'] += $ticket_price->price *  $v['count_type'];
                            $tmp_arr['total_collected'] += $v['sum_type'];
                        }

                        $ticket_result[] = $tmp_arr;

                        $total_tmp_arr['total_pos'] += $tmp_arr['total_pos'];
                        $total_tmp_arr['total_charge'] += $tmp_arr['total_charge'];
                        $total_tmp_arr['total_qrcode'] += $tmp_arr['total_qrcode'];
                        $total_tmp_arr['total_deposit_month'] += $tmp_arr['total_deposit_month'];
                        $total_tmp_arr['total_revenue'] += $tmp_arr['total_revenue'];
                        $total_tmp_arr['total_collected'] += $tmp_arr['total_collected'];
                    }
                    $ticket_result[] = $total_tmp_arr;
                }
            }
        }elseif($price_id == 0){

            $ticket_types = $this->ticket_types->getTicketTypeByCompanyIdAndByType($company_id, -1);

            if($route_id > 0){

                $route = $this->routes->getRouteById($route_id);

                if(count($ticket_types) > 0){

                    $total_tmp_arr = [
                        "route_number" => 'all',
                        "total_released" => 0,
                        "total_pos" => 0,
                        "total_charge" => 0,
                        "total_qrcode" => 0,
                        "total_deposit_month" => 0,
                        "total_revenue" => 0,
                        "total_collected" => 0
                    ];

                    foreach ($ticket_types as $ticket_type) {

                        $ticket_price_id = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['id'];
                        $price = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['price'];
                        $ticket_allocate = $this->ticket_allocates->getTotalTicketCreated([
                            'company_id' => $company_id,
                            'ticket_price_id' => $ticket_price_id,
                            'to_date' => $to_date
                        ]);
                        $total_released = $ticket_allocate ? $ticket_allocate->end_number : 0;

                        //handle for total
                        $total_tmp_arr['total_released'] += $total_released;

                        $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                    ->where([
                                        ['transactions.company_id', $company_id],
                                        ['shifts.route_id', $route_id],
                                        ['transactions.activated', '>=', $from_date],
                                        ['transactions.activated', '<=', $to_date],
                                        ['transactions.ticket_destroy', '!=', 1],
                                        ['transactions.ticket_price_id', '=', $ticket_price_id]
                                    ])
                                    ->whereIn('transactions.type', ['pos', 'charge', 'qrcode','deposit_month'])
                                    ->selectRaw('transactions.type, count(transactions.type) as count_type, sum(transactions.amount) as sum_type')
                                    ->groupBy('transactions.type')
                                    ->get()
                                    ->toArray();

                        if (count($transactions) > 0) {

                            $tmp_arr = [
                                "price" => $price,
                                "order_code" => $ticket_type['order_code'] ?? '',
                                "route_number" => $route ? $route->number : '',
                                "route_id" => $route ? $route->id : '',
                                "total_released" => $total_released,
                                "total_pos" => 0,
                                "total_charge" => 0,
                                "total_deposit_month" => 0,
                                "total_qrcode" => 0,
                                "total_revenue" => 0,
                                "total_collected" => 0,
                            ];

                            foreach ($transactions as  $transaction) {

                                if ($transaction['type'] == 'pos') $tmp_arr['total_pos'] += $transaction['count_type'];
                                if ($transaction['type'] == 'charge') $tmp_arr['total_charge'] += $transaction['count_type'];
                                if ($transaction['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $transaction['count_type'];
                                if ($transaction['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $transaction['count_type'];

                                $tmp_arr['total_revenue'] += $price * $transaction['count_type'];
                                $tmp_arr['total_collected'] += $transaction['sum_type'];
                            }
                            $ticket_result[] = $tmp_arr;

                            //handle total
                            $total_tmp_arr['total_pos'] += $tmp_arr['total_pos'];
                            $total_tmp_arr['total_charge'] += $tmp_arr['total_charge'];
                            $total_tmp_arr['total_qrcode'] += $tmp_arr['total_qrcode'];
                            $total_tmp_arr['total_deposit_month'] += $tmp_arr['total_deposit_month'];
                            $total_tmp_arr['total_revenue'] += $tmp_arr['total_revenue'];
                            $total_tmp_arr['total_collected'] += $tmp_arr['total_collected'];
                        }
                    }

                    if($total_tmp_arr['total_collected'] > 0) $ticket_result[] = $total_tmp_arr;
                }
            }elseif($route_id == 0){

                if(count($ticket_types) > 0){

                    $total_tmp_arr = [
                        "route_number" => 'all',
                        "total_released" => 0,
                        "total_pos" => 0,
                        "total_charge" => 0,
                        "total_deposit_month" => 0,
                        "total_qrcode" => 0,
                        "total_revenue" => 0,
                        "total_collected" => 0
                    ];

                    foreach ($ticket_types as $ticket_type) {

                        $ticket_price_id = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['id'];
                        $price = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['price'];
                        $ticket_allocate = $this->ticket_allocates->getTotalTicketCreated([
                            'company_id' => $company_id,
                            'ticket_price_id' => $ticket_price_id,
                            'to_date' => $to_date
                        ]);
                        $total_released = $ticket_allocate ? $ticket_allocate->end_number : 0;

                        //handle for total
                        $total_tmp_arr['total_released'] += $total_released;

                        $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                    ->where([
                                        ['transactions.company_id', $company_id],
                                        ['transactions.activated', '>=', $from_date],
                                        ['transactions.activated', '<=', $to_date],
                                        ['transactions.ticket_destroy', '!=', 1],
                                        ['transactions.ticket_price_id', '=', $ticket_price_id]
                                    ])
                                    ->whereIn('transactions.type', ['pos', 'charge', 'qrcode','deposit_month'])
                                    ->selectRaw('shifts.route_id, transactions.type, count(transactions.type) as count_type, sum(transactions.amount) as sum_type')
                                    ->groupBy('shifts.route_id','transactions.type')
                                    ->get()
                                    ->toArray();

                        if (count($transactions) > 0) {

                            $transactions =  collect($transactions)->groupBy('route_id')->toArray();

                            foreach ($transactions as $key => $values) {

                                $route = $this->routes->getRouteById((int)$key);

                                $tmp_arr = [
                                    "price" => $price,
                                    "order_code" => $ticket_type['order_code'] ?? '',
                                    "route_number" => $route ? $route->number : '',
                                    "route_id" => $route ? $route->id : 0,
                                    "total_released" => $total_released,
                                    "total_pos" => 0,
                                    "total_charge" => 0,
                                    "total_deposit_month" => 0,
                                    "total_qrcode" => 0,
                                    "total_revenue" => 0,
                                    "total_collected" => 0
                                ];

                                foreach ($values as $v) {

                                    if ($v['type'] == 'pos') $tmp_arr['total_pos'] += $v['count_type'];
                                    if ($v['type'] == 'charge') $tmp_arr['total_charge'] += $v['count_type'];
                                    if ($v['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $v['count_type'];
                                    if ($v['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $v['count_type'];

                                    $tmp_arr['total_revenue'] += $price *  $v['count_type'];
                                    $tmp_arr['total_collected'] += $v['sum_type'];
                                }

                                $ticket_result[] = $tmp_arr;

                                //handle total
                                $total_tmp_arr['total_pos'] += $tmp_arr['total_pos'];
                                $total_tmp_arr['total_deposit_month'] += $tmp_arr['total_deposit_month'];
                                $total_tmp_arr['total_charge'] += $tmp_arr['total_charge'];
                                $total_tmp_arr['total_qrcode'] += $tmp_arr['total_qrcode'];
                                $total_tmp_arr['total_revenue'] += $tmp_arr['total_revenue'];
                                $total_tmp_arr['total_collected'] += $tmp_arr['total_collected'];
                            }
                        }
                    }

                    if($total_tmp_arr['total_collected'] > 0) $ticket_result[] = $total_tmp_arr;
                }
            }
        }

        return $ticket_result;
    }

    public function viewTicketByStation($data)
    {

        $company_id = (int)$data['company_id'];
        $from_date =  date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date =  date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $route_id = (int)$data['route_id'] ?? 0;
        $price_id = (int)$data['price_id'] ?? 0;

        $ticket_result = [];

        $where = [
            ['transactions.company_id', $company_id],
            ['transactions.activated', '>=', $from_date],
            ['transactions.activated', '<=', $to_date],
            ['transactions.ticket_destroy', '!=', 1]
        ];

        if($route_id > 0){ $where[] = ['shifts.route_id', $route_id];}

        if($price_id > 0){

            $ticket_price = $this->ticket_prices->getDataPriceById($price_id);
            $where[] = ['transactions.ticket_price_id', $price_id];

            $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                        ->where($where)
                        ->whereIn('transactions.type', ['pos', 'charge', 'qrcode', 'deposit_month'])
                        ->selectRaw('shifts.route_id,transactions.station_id, transactions.type, count(transactions.type) as count_type')
                        ->groupBy('shifts.route_id','transactions.station_id','transactions.type')
                        ->get()
                        ->toArray();
            if(count($transactions) > 0){

                $total_tmp_arr = [
                    "route_number" => 'all',
                    "total_pos" => 0,
                    "total_charge" => 0,
                    "total_qrcode" => 0,
                    "total_deposit_month" => 0
                ];

                $transactions = collect($transactions)->groupBy('route_id')->toArray();

                foreach ($transactions as $k_route => $v_routes) {

                    $route = $this->routes->getRouteById((int)$k_route);
                    $v_routes = collect($v_routes)->groupBy('station_id')->toArray();

                    foreach ($v_routes as $k_station => $v_stations) {

                        $bus_station = $this->bus_stations->getDataBusStationById((int)$k_station);

                        $tmp_arr = [
                            "price" => $ticket_price->price,
                            "order_code" => $ticket_price->ticketType['order_code'] ?? '',
                            "route_number" => $route ? $route->number : '',
                            "route_id" => $route ? $route->id : '',
                            "station_name" => $bus_station ? $bus_station->name : '',
                            "total_pos" => 0,
                            "total_charge" => 0,
                            "total_qrcode" => 0,
                            "total_deposit_month" => 0
                        ];
                        foreach ($v_stations as $v) {
                            if ($v['type'] == 'pos') $tmp_arr['total_pos'] += $v['count_type'];
                            if ($v['type'] == 'charge') $tmp_arr['total_charge'] += $v['count_type'];
                            if ($v['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $v['count_type'];
                            if ($v['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $v['count_type'];
                        }

                        //hadle total
                        $total_tmp_arr['total_pos'] += $tmp_arr['total_pos'];
                        $total_tmp_arr['total_charge'] += $tmp_arr['total_charge'];
                        $total_tmp_arr['total_qrcode'] += $tmp_arr['total_qrcode'];
                        $total_tmp_arr['total_deposit_month'] += $tmp_arr['total_deposit_month'];

                        $ticket_result[] = $tmp_arr;
                    }
                }

                $ticket_result[] = $total_tmp_arr;
            }
        }else{

            $ticket_types = $this->ticket_types->getTicketTypeByCompanyIdAndByType($company_id, -1);
            $total_tmp_arr = [
                "route_number" => 'all',
                "total_pos" => 0,
                "total_charge" => 0,
                "total_qrcode" => 0,
                "total_deposit_month" => 0
            ];

            foreach ($ticket_types as $ticket_type) {

                $ticket_price_id = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['id'];
                $price = $ticket_type['ticket_prices'][count($ticket_type['ticket_prices']) - 1]['price'];

                $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                ->where($where)
                                ->where('transactions.ticket_price_id','=', (int)$ticket_price_id)
                                ->whereIn('transactions.type', ['pos', 'charge', 'qrcode', 'deposit_month'])
                                ->selectRaw('shifts.route_id,transactions.station_id, transactions.type, count(transactions.type) as count_type')
                                ->groupBy('shifts.route_id','transactions.station_id','transactions.type')
                                ->get()
                                ->toArray();
                if(count($transactions) > 0){

                    $transactions = collect($transactions)->groupBy('route_id')->toArray();

                    foreach ($transactions as $k_route => $v_routes) {

                        $route = $this->routes->getRouteById((int)$k_route);
                        $v_routes = collect($v_routes)->groupBy('station_id')->toArray();

                        foreach ($v_routes as $k_station => $v_stations) {

                            $bus_station = $this->bus_stations->getDataBusStationById((int)$k_station);

                            $tmp_arr = [
                                "price" => $price,
                                "order_code" => $ticket_type['order_code'] ?? '',
                                "route_number" => $route ? $route->number : '',
                                "route_id" => $route ? $route->id : '',
                                "station_name" => $bus_station ? $bus_station->name : '',
                                "total_pos" => 0,
                                "total_charge" => 0,
                                "total_qrcode" => 0,
                                "total_deposit_month" => 0
                            ];
                            foreach ($v_stations as $v) {
                                if ($v['type'] == 'pos') $tmp_arr['total_pos'] += $v['count_type'];
                                if ($v['type'] == 'charge') $tmp_arr['total_charge'] += $v['count_type'];
                                if ($v['type'] == 'qrcode') $tmp_arr['total_qrcode'] += $v['count_type'];
                                if ($v['type'] == 'deposit_month') $tmp_arr['total_deposit_month'] += $v['count_type'];
                            }

                            //hadle total
                            $total_tmp_arr['total_pos'] += $tmp_arr['total_pos'];
                            $total_tmp_arr['total_charge'] += $tmp_arr['total_charge'];
                            $total_tmp_arr['total_qrcode'] += $tmp_arr['total_qrcode'];
                            $total_tmp_arr['total_deposit_month'] += $tmp_arr['total_deposit_month'];

                            $ticket_result[] = $tmp_arr;
                        }
                    }
                }
            }
            $ticket_result[] = $total_tmp_arr;
        }
        return $ticket_result;
    }

    //not used function (save make use code)
    public function viewTicketDemo($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $ticket_arr = [];

        // date
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        // get company
        $company = $this->companies->getCompanyById($company_id);

        //get ticket
        $ticket_allocates = $this->ticket_allocates->getTicketAllocateByOptions([
            ["company_id", "=", $company_id],
            ["created_at", ">=", $from_date],
            ["created_at", "<=", $to_date]
        ], 'ticket_price_id')->toArray();

        if (count($ticket_allocates) > 0) {
            $n = 0;
            $m = 0;
            for ($i = 0; $i < count($ticket_allocates); $i++) {

                $ticket_price_id = $ticket_allocates[$i]['ticket_price_id'];

                if (($i + 1 == count($ticket_allocates)) || $ticket_price_id != $ticket_allocates[$i + 1]['ticket_price_id']) {

                    $start_number = $ticket_allocates[$n]['start_number'];
                    $end_number = $ticket_allocates[$m]['end_number'];
                    $prices = $this->ticket_prices->getPriceById($ticket_price_id);
                    $price = $prices->price;

                    $count_sale = DB::table('transactions')
                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                        ->where('transactions.ticket_price_id', $ticket_price_id)
                        ->where('transactions.ticket_destroy', '!=', 1)
                        ->where('transactions.company_id', $company_id)
                        ->whereIn('transactions.type', ['pos', 'charge', 'qrcode'])
                        ->where('transactions.ticket_number', '!=', '')
                        ->where('shifts.ended', '>=', $from_date)
                        ->where('shifts.ended', '<=', $to_date)
                        ->count();


                    $ticket_tmp = array(
                        'price' => $price,
                        'start_number' => $start_number,
                        'end_number' => $end_number,
                        'count_sale' => $count_sale
                    );
                    array_push($ticket_arr, $ticket_tmp);
                    $n = $i + 1;
                    $m = $i + 1;
                } else {
                    if ($ticket_price_id == $ticket_allocates[$i + 1]['ticket_price_id']) {
                        if ($ticket_allocates[$n]['start_number'] > $ticket_allocates[$i + 1]['start_number']) $n = $i + 1;
                        if ($ticket_allocates[$m]['end_number'] < $ticket_allocates[$i + 1]['end_number']) $m = $i + 1;
                    }
                }
            }
        }
        return $ticket_arr;
    }

    public function viewVehicleByPeriod($data)
    {
        if ($data) {

            $vehicle_period_arr = $this->getReportVehicleByPeriod($data);
            $result = [];
            $result['vehicle_arr'] = $vehicle_period_arr;
            // $result['isCheckModuleApp'] =  $isCheckModuleApp;
            $result['isCheckModuleApp'] =  $this->isCheckModuleApp($data['company_id']);

            return $result;
        }
    }

    public function viewVehicleRoutePeriod($data)
    {
        $now_from_date = date("Y-m-d 00:00:00", strtotime($data['now_from_date']));
        $now_to_date = date("Y-m-d 23:59:59", strtotime($data['now_to_date']));
        $last_from_date = date("Y-m-d 00:00:00", strtotime($data['last_from_date']));
        $last_to_date = date("Y-m-d 23:59:59", strtotime($data['last_to_date']));
        $object_compare = $data['object_compare'];
        $object_report = $data['object_report'];
        $company_id = $data['company_id'];
        $result = [];
        $join_raw = [];
        $select_raw = "";
        $groupBy_raw = "";
        $orderBy_raw = "";

        if ($object_report == 'vehicle') {

            $join_raw[] = ['vehicles', 'vehicles.id', '=', 'shifts.vehicle_id'];
            $or_Where[] = ['vehicles.company_id', $company_id];
            $orderBy_raw = 'shifts.vehicle_id';

            if ($object_compare == 'all') {
                $select_raw = DB::raw('
                    shifts.vehicle_id,
                    transactions.shift_id,
                    count(transactions.type) as count_type,
                    sum(transactions.amount) as total_amount
                ');
                $groupBy_raw = DB::raw('
                    shifts.vehicle_id,
                    transactions.shift_id
                ');
            } else {
                $select_raw = DB::raw('
                    shifts.vehicle_id,
                    transactions.shift_id,
                    transactions.type,
                    count(transactions.type) as count_type,
                    sum(transactions.amount) as total_amount
                ');
                $groupBy_raw = DB::raw('
                    shifts.vehicle_id,
                    transactions.shift_id,
                    transactions.type
                ');
            }
        } else if ($object_report == 'route') {

            $join_raw[] = ['routes', 'routes.id', '=', 'shifts.route_id'];
            $or_Where[] = ['routes.company_id', $company_id];
            $orderBy_raw = 'shifts.route_id';

            if ($object_compare == 'all') {
                $select_raw = DB::raw('
                    shifts.route_id,
                    transactions.shift_id,
                    count(transactions.type) as count_type,
                    sum(transactions.amount) as total_amount
                ');
                $groupBy_raw = DB::raw('
                    shifts.route_id,
                    transactions.shift_id
                ');
            } else {
                $select_raw = DB::raw('
                    shifts.route_id,
                    transactions.shift_id,
                    transactions.type,
                    count(transactions.type) as count_type,
                    sum(transactions.amount) as total_amount
                ');
                $groupBy_raw = DB::raw('
                    shifts.route_id,
                    transactions.shift_id,
                    transactions.type
                ');
            }
        }
        // handle period now
        $now_shifts = DB::table('shifts')
            ->join($join_raw[0][0], $join_raw[0][1], $join_raw[0][2], $join_raw[0][3])
            ->join('transactions', 'shifts.id', '=', 'transactions.shift_id')
            ->where('transactions.company_id', $company_id)
            ->where('transactions.ticket_destroy', '!=', 1)
            ->whereIn('transactions.type', ['pos', 'deposit_month', 'charge', 'qrcode'])
            ->where([
                ['shifts.ended', '>=', $now_from_date],
                ['shifts.ended', '<=', $now_to_date],
                ['shifts.shift_destroy', '!=', 1]
            ])
            ->select($select_raw)->groupBy($groupBy_raw)->orderBy($orderBy_raw)
            ->get();

        if ($object_report == 'vehicle') {
            $now_shifts = collect($now_shifts)->groupBy('vehicle_id')->toArray();
        } else if ($object_report == 'route') {
            $now_shifts = collect($now_shifts)->groupBy('route_id')->toArray();
        }

        $data_period_now = [];
        if (count($now_shifts) > 0) {

            if ($object_compare == 'all') {

                foreach ($now_shifts as $now_shift) {

                    $vehicle = (isset($now_shift[0]->vehicle_id)) ? ($this->vehicles->getVehicleById($now_shift[0]->vehicle_id)) : null;
                    $route = (isset($now_shift[0]->route_id)) ? $this->routes->getRouteById($now_shift[0]->route_id) : null;

                    $period_now_tmp = [
                        'license_plates' => $vehicle->license_plates ?? '',
                        'route_number' =>  $route->number ?? '',
                        'now_count_shift' => 0,
                        'now_total_count_ticket' => 0,
                        'now_total_revenue_ticket' => 0,
                        'now_total_revenue_pos' => 0,
                        'now_total_revenue_charge' => 0,
                        'now_total_revenue_month' => 0,
                        'now_total_revenue_qr_code' => 0
                    ];

                    foreach ($now_shift as $vl_now_shift) {

                        $period_now_tmp['now_count_shift'] += 1;
                        $period_now_tmp['now_total_count_ticket'] += $vl_now_shift->count_type;
                        $period_now_tmp['now_total_revenue_ticket'] += $vl_now_shift->total_amount;
                    }

                    $data_period_now[] = $period_now_tmp;
                }
            } else {
                foreach ($now_shifts as $now_shift) {
                    $vehicle = (isset($now_shift[0]->vehicle_id)) ? ($this->vehicles->getVehicleById($now_shift[0]->vehicle_id)) : null;
                    $route = (isset($now_shift[0]->route_id)) ? $this->routes->getRouteById($now_shift[0]->route_id) : null;

                    $period_now_tmp = [
                        'license_plates' => $vehicle->license_plates ?? '',
                        'route_number' =>  $route->number ?? '',
                        'now_count_shift' => 0,
                        'now_total_count_ticket' => 0,
                        'now_total_revenue_ticket' => 0,
                        'now_total_revenue_pos' => 0,
                        'now_total_revenue_charge' => 0,
                        'now_total_revenue_month' => 0,
                        'now_total_revenue_qr_code' => 0
                    ];

                    $group_shifts = collect($now_shift)->groupBy('shift_id')->toArray();
                    foreach ($group_shifts as $group_shift) {

                        $period_now_tmp['now_count_shift'] += 1;

                        foreach ($group_shift as $vl_now_shift) {

                            $period_now_tmp['now_total_count_ticket'] += $vl_now_shift->count_type;

                            if ($vl_now_shift->type == 'pos') $period_now_tmp['now_total_revenue_pos'] += $vl_now_shift->total_amount;

                            if ($vl_now_shift->type == 'charge') $period_now_tmp['now_total_revenue_charge'] += $vl_now_shift->total_amount;

                            if ($vl_now_shift->type == 'deposit_month') $period_now_tmp['now_total_revenue_month'] += $vl_now_shift->total_amount;

                            if ($vl_now_shift->type == 'qrcode') $period_now_tmp['now_total_revenue_qr_code'] += $vl_now_shift->total_amount;

                            $period_now_tmp['now_total_revenue_ticket'] += $vl_now_shift->total_amount;
                        }
                    }

                    $data_period_now[] = $period_now_tmp;
                }
            }
        }

        // handle period last
        $last_shifts = DB::table('shifts')
            ->join($join_raw[0][0], $join_raw[0][1], $join_raw[0][2], $join_raw[0][3])
            ->join('transactions', 'shifts.id', '=', 'transactions.shift_id')
            ->where('transactions.company_id', $company_id)
            ->where('transactions.ticket_destroy', '!=', 1)
            ->whereIn('transactions.type', ['pos', 'deposit_month', 'charge', 'qrcode'])
            ->where([
                ['shifts.ended', '>=', $last_from_date],
                ['shifts.ended', '<=', $last_to_date],
                ['shifts.shift_destroy', '!=', 1]
            ])
            ->select($select_raw)->groupBy($groupBy_raw)->orderBy($orderBy_raw)
            ->get();

        if ($object_report == 'vehicle') {
            $last_shifts = collect($last_shifts)->groupBy('vehicle_id')->toArray();
        } else if ($object_report == 'route') {
            $last_shifts = collect($last_shifts)->groupBy('route_id')->toArray();
        }

        $data_period_last = [];
        if (count($last_shifts) > 0) {

            if ($object_compare == 'all') {

                foreach ($last_shifts as $last_shift) {

                    $vehicle = (isset($last_shift[0]->vehicle_id)) ? ($this->vehicles->getVehicleById($last_shift[0]->vehicle_id)) : null;
                    $route = (isset($last_shift[0]->route_id)) ? $this->routes->getRouteById($last_shift[0]->route_id) : null;

                    $period_last_tmp = [
                        'license_plates' => $vehicle->license_plates ?? '',
                        'route_number' =>  $route->number ?? '',
                        'last_count_shift' => 0,
                        'last_total_count_ticket' => 0,
                        'last_total_revenue_ticket' => 0,
                        'last_total_revenue_pos' => 0,
                        'last_total_revenue_charge' => 0,
                        'last_total_revenue_month' => 0,
                        'last_total_revenue_qr_code' => 0
                    ];

                    foreach ($last_shift as $vl_last_shift) {

                        $period_last_tmp['last_count_shift'] += 1;
                        $period_last_tmp['last_total_count_ticket'] += $vl_last_shift->count_type;
                        $period_last_tmp['last_total_revenue_ticket'] += $vl_last_shift->total_amount;
                    }

                    $data_period_last[] = $period_last_tmp;
                }
            } else {
                foreach ($last_shifts as $last_shift) {
                    $vehicle = (isset($last_shift[0]->vehicle_id)) ? ($this->vehicles->getVehicleById($last_shift[0]->vehicle_id)) : null;
                    $route = (isset($last_shift[0]->route_id)) ? $this->routes->getRouteById($last_shift[0]->route_id) : null;

                    $period_last_tmp = [
                        'license_plates' => $vehicle->license_plates ?? '',
                        'route_number' =>  $route->number ?? '',
                        'last_count_shift' => 0,
                        'last_total_count_ticket' => 0,
                        'last_total_revenue_ticket' => 0,
                        'last_total_revenue_pos' => 0,
                        'last_total_revenue_charge' => 0,
                        'last_total_revenue_month' => 0,
                        'last_total_revenue_qr_code' => 0
                    ];

                    $group_shifts = collect($last_shift)->groupBy('shift_id')->toArray();
                    foreach ($group_shifts as $group_shift) {

                        $period_last_tmp['last_count_shift'] += 1;

                        foreach ($group_shift as $vl_last_shift) {

                            $period_last_tmp['last_total_count_ticket'] += $vl_last_shift->count_type;

                            if ($vl_last_shift->type == 'pos') $period_last_tmp['last_total_revenue_pos'] += $vl_last_shift->total_amount;

                            if ($vl_last_shift->type == 'charge') $period_last_tmp['last_total_revenue_charge'] += $vl_last_shift->total_amount;

                            if ($vl_last_shift->type == 'deposit_month') $period_last_tmp['last_total_revenue_month'] += $vl_last_shift->total_amount;

                            if ($vl_last_shift->type == 'qrcode') $period_last_tmp['last_total_revenue_qr_code'] += $vl_last_shift->total_amount;

                            $period_last_tmp['last_total_revenue_ticket'] += $vl_last_shift->total_amount;
                        }
                    }
                    $data_period_last[] = $period_last_tmp;
                }
            }
        }

        $merge_data = array_merge($data_period_now, $data_period_last);

        $total_all = [
            'license_plates' => 'all',
            'now_count_shift' => 0,
            'now_total_count_ticket' => 0,
            'now_total_revenue_ticket' => 0,
            'now_total_revenue_pos' => 0,
            'now_total_revenue_charge' => 0,
            'now_total_revenue_month' => 0,
            'now_total_revenue_qr_code' => 0,

            'last_count_shift' => 0,
            'last_total_count_ticket' => 0,
            'last_total_revenue_ticket' => 0,
            'last_total_revenue_pos' => 0,
            'last_total_revenue_charge' => 0,
            'last_total_revenue_month' => 0,
            'last_total_revenue_qr_code' => 0,

            'compare_count_shift' => 0,
            'compare_total_count_ticket' => 0,
            'compare_total_revenue_ticket' => 0,
            'compare_total_revenue_pos' => 0,
            'compare_total_revenue_charge' => 0,
            'compare_total_revenue_month' => 0,
            'compare_total_revenue_qr_code' => 0,
        ];
        $group_data = [];

        if ($object_report == 'vehicle') {
            $group_data = collect($merge_data)->groupBy('license_plates')->toArray();
        } else if ($object_report == 'route') {
            $group_data = collect($merge_data)->groupBy('route_number')->toArray();
        }

        // return [$group_data];
        foreach ($group_data as $key => $values) {

            $arr_tmp = [
                'license_plates' => $values[0]['license_plates'] ?? '',
                'route_number' => $values[0]['route_number'] ?? 0,

                'now_count_shift' => 0,
                'now_total_count_ticket' => 0,
                'now_total_revenue_ticket' => 0,
                'now_total_revenue_pos' => 0,
                'now_total_revenue_charge' => 0,
                'now_total_revenue_month' => 0,
                'now_total_revenue_qr_code' => 0,

                'last_count_shift' => 0,
                'last_total_count_ticket' => 0,
                'last_total_revenue_ticket' => 0,
                'last_total_revenue_pos' => 0,
                'last_total_revenue_charge' => 0,
                'last_total_revenue_month' => 0,
                'last_total_revenue_qr_code' => 0,

                'compare_count_shift' => "-",
                'compare_total_count_ticket' => "-",
                'compare_total_revenue_ticket' => "-",
                'compare_total_revenue_pos' => "-",
                'compare_total_revenue_charge' => "-",
                'compare_total_revenue_month' => "-",
                'compare_total_revenue_qr_code' => "-",
            ];

            if (count($values) >= 2) {
                if ($values[1]['last_count_shift'] > 0)
                    $arr_tmp['compare_count_shift'] = round((($values[0]['now_count_shift'] - $values[1]['last_count_shift']) / $values[1]['last_count_shift']) * 100);

                if ($values[1]['last_total_count_ticket'] > 0)
                    $arr_tmp['compare_total_count_ticket'] = round((($values[0]['now_total_count_ticket'] - $values[1]['last_total_count_ticket']) / $values[1]['last_total_count_ticket']) * 100);

                if ($values[1]['last_total_revenue_pos'] > 0)
                    $arr_tmp['compare_total_revenue_pos'] = round((($values[0]['now_total_revenue_pos'] - $values[1]['last_total_revenue_pos']) / $values[1]['last_total_revenue_pos']) * 100);

                if ($values[1]['last_total_revenue_charge'] > 0)
                    $arr_tmp['compare_total_revenue_charge'] = round((($values[0]['now_total_revenue_charge'] - $values[1]['last_total_revenue_charge']) / $values[1]['last_total_revenue_charge']) * 100);

                if ($values[1]['last_total_revenue_month'] > 0)
                    $arr_tmp['compare_total_revenue_month'] = round((($values[0]['now_total_revenue_month'] - $values[1]['last_total_revenue_month']) / $values[1]['last_total_revenue_month']) * 100);

                if ($values[1]['last_total_revenue_qr_code'] > 0)
                    $arr_tmp['compare_total_revenue_qr_code'] = round((($values[0]['now_total_revenue_qr_code'] - $values[1]['last_total_revenue_qr_code']) / $values[1]['last_total_revenue_qr_code']) * 100);

                if ($values[1]['last_total_revenue_ticket'] > 0)
                    $arr_tmp['compare_total_revenue_ticket'] = round((($values[0]['now_total_revenue_ticket'] - $values[1]['last_total_revenue_ticket']) / $values[1]['last_total_revenue_ticket']) * 100);

                $arr_tmp['now_count_shift'] = $values[0]['now_count_shift'];
                $arr_tmp['now_total_count_ticket'] = $values[0]['now_total_count_ticket'];
                $arr_tmp['now_total_revenue_pos'] = $values[0]['now_total_revenue_pos'];
                $arr_tmp['now_total_revenue_charge'] = $values[0]['now_total_revenue_charge'];
                $arr_tmp['now_total_revenue_month'] = $values[0]['now_total_revenue_month'];
                $arr_tmp['now_total_revenue_qr_code'] = $values[0]['now_total_revenue_qr_code'];
                $arr_tmp['now_total_revenue_ticket'] = $values[0]['now_total_revenue_ticket'];

                $arr_tmp['last_count_shift'] = $values[1]['last_count_shift'];
                $arr_tmp['last_total_count_ticket'] = $values[1]['last_total_count_ticket'];
                $arr_tmp['last_total_revenue_pos'] = $values[1]['last_total_revenue_pos'];
                $arr_tmp['last_total_revenue_charge'] = $values[1]['last_total_revenue_charge'];
                $arr_tmp['last_total_revenue_month'] = $values[1]['last_total_revenue_month'];
                $arr_tmp['last_total_revenue_qr_code'] = $values[1]['last_total_revenue_qr_code'];
                $arr_tmp['last_total_revenue_ticket'] = $values[1]['last_total_revenue_ticket'];
            } else {

                if (isset($values[0]['now_count_shift'])) {

                    $arr_tmp['now_count_shift'] = $values[0]['now_count_shift'];
                    $arr_tmp['now_total_count_ticket'] = $values[0]['now_total_count_ticket'];
                    $arr_tmp['now_total_revenue_pos'] = $values[0]['now_total_revenue_pos'];
                    $arr_tmp['now_total_revenue_charge'] = $values[0]['now_total_revenue_charge'];
                    $arr_tmp['now_total_revenue_month'] = $values[0]['now_total_revenue_month'];
                    $arr_tmp['now_total_revenue_qr_code'] = $values[0]['now_total_revenue_qr_code'];
                    $arr_tmp['now_total_revenue_ticket'] = $values[0]['now_total_revenue_ticket'];
                } else if (isset($values[0]['last_count_shift'])) {

                    $arr_tmp['last_count_shift'] = $values[0]['last_count_shift'];
                    $arr_tmp['last_total_count_ticket'] = $values[0]['last_total_count_ticket'];
                    $arr_tmp['last_total_revenue_pos'] = $values[0]['last_total_revenue_pos'];
                    $arr_tmp['last_total_revenue_charge'] = $values[0]['last_total_revenue_charge'];
                    $arr_tmp['last_total_revenue_month'] = $values[0]['last_total_revenue_month'];
                    $arr_tmp['last_total_revenue_qr_code'] = $values[0]['last_total_revenue_qr_code'];
                    $arr_tmp['last_total_revenue_ticket'] = $values[0]['last_total_revenue_ticket'];
                }
            }

            // foreach ($values as $value) {
            //     if (
            //         isset($value['now_count_shift']) &&
            //         isset($value['now_total_count_ticket']) &&
            //         isset($value['now_total_revenue_pos']) &&
            //         isset($value['now_total_revenue_charge']) &&
            //         isset($value['now_total_revenue_month']) &&
            //         isset($value['now_total_revenue_qr_code']) &&
            //         isset($value['now_total_revenue_ticket'])
            //     ) {
            //         $arr_tmp['now_count_shift'] = $value['now_count_shift'];
            //         $arr_tmp['now_total_count_ticket'] = $value['now_total_count_ticket'];
            //         $arr_tmp['now_total_revenue_pos'] = $value['now_total_revenue_pos'];
            //         $arr_tmp['now_total_revenue_charge'] = $value['now_total_revenue_charge'];
            //         $arr_tmp['now_total_revenue_month'] = $value['now_total_revenue_month'];
            //         $arr_tmp['now_total_revenue_qr_code'] = $value['now_total_revenue_qr_code'];
            //         $arr_tmp['now_total_revenue_ticket'] = $value['now_total_revenue_ticket'];
            //     }

            //     if (
            //         isset($value['last_count_shift']) &&
            //         isset($value['last_total_count_ticket']) &&
            //         isset($value['last_total_revenue_pos']) &&
            //         isset($value['last_total_revenue_charge']) &&
            //         isset($value['last_total_revenue_month']) &&
            //         isset($value['last_total_revenue_qr_code']) &&
            //         isset($value['last_total_revenue_ticket'])
            //     ) {
            //         $arr_tmp['last_count_shift'] = $value['last_count_shift'];
            //         $arr_tmp['last_total_count_ticket'] = $value['last_total_count_ticket'];
            //         $arr_tmp['last_total_revenue_pos'] = $value['last_total_revenue_pos'];
            //         $arr_tmp['last_total_revenue_charge'] = $value['last_total_revenue_charge'];
            //         $arr_tmp['last_total_revenue_month'] = $value['last_total_revenue_month'];
            //         $arr_tmp['last_total_revenue_qr_code'] = $value['last_total_revenue_qr_code'];
            //         $arr_tmp['last_total_revenue_ticket'] = $value['last_total_revenue_ticket'];
            //     }

            //     if ($arr_tmp['now_count_shift'] && $arr_tmp['last_count_shift']) $arr_tmp['compare_count_shift'] = round(($arr_tmp['now_count_shift'] / $arr_tmp['last_count_shift']) * 100);

            //     if ($arr_tmp['now_total_count_ticket'] && $arr_tmp['last_total_count_ticket']) $arr_tmp['compare_total_count_ticket'] = round(($arr_tmp['now_total_count_ticket'] / $arr_tmp['last_total_count_ticket']) * 100);

            //     if ($arr_tmp['now_total_revenue_pos'] && $arr_tmp['last_total_revenue_pos']) $arr_tmp['compare_total_revenue_pos'] = round(($arr_tmp['now_total_revenue_pos'] / $arr_tmp['last_total_revenue_pos']) * 100);

            //     if ($arr_tmp['now_total_revenue_charge'] && $arr_tmp['last_total_revenue_charge']) $arr_tmp['compare_total_revenue_charge'] = round(($arr_tmp['now_total_revenue_charge'] / $arr_tmp['last_total_revenue_charge']) * 100);

            //     if ($arr_tmp['now_total_revenue_month'] && $arr_tmp['last_total_revenue_month']) $arr_tmp['compare_total_revenue_month'] = round(($arr_tmp['now_total_revenue_month'] / $arr_tmp['last_total_revenue_month']) * 100);

            //     if ($arr_tmp['now_total_revenue_qr_code'] && $arr_tmp['last_total_revenue_qr_code']) $arr_tmp['compare_total_revenue_qr_code'] = round(($arr_tmp['now_total_revenue_qr_code'] / $arr_tmp['last_total_revenue_qr_code']) * 100);

            //     if ($arr_tmp['now_total_revenue_ticket'] && $arr_tmp['last_total_revenue_ticket']) $arr_tmp['compare_total_revenue_ticket'] = round(($arr_tmp['now_total_revenue_ticket'] / $arr_tmp['last_total_revenue_ticket']) * 100);
            // }

            $total_all['now_count_shift'] += $arr_tmp['now_count_shift'];
            $total_all['now_total_count_ticket'] += $arr_tmp['now_total_count_ticket'];
            $total_all['now_total_revenue_pos'] += $arr_tmp['now_total_revenue_pos'];
            $total_all['now_total_revenue_charge'] += $arr_tmp['now_total_revenue_charge'];
            $total_all['now_total_revenue_month'] += $arr_tmp['now_total_revenue_month'];
            $total_all['now_total_revenue_qr_code'] += $arr_tmp['now_total_revenue_qr_code'];
            $total_all['now_total_revenue_ticket'] += $arr_tmp['now_total_revenue_ticket'];

            $total_all['last_count_shift'] += $arr_tmp['last_count_shift'];
            $total_all['last_total_count_ticket'] += $arr_tmp['last_total_count_ticket'];
            $total_all['last_total_revenue_pos'] += $arr_tmp['last_total_revenue_pos'];
            $total_all['last_total_revenue_charge'] += $arr_tmp['last_total_revenue_charge'];
            $total_all['last_total_revenue_month'] += $arr_tmp['last_total_revenue_month'];
            $total_all['last_total_revenue_qr_code'] += $arr_tmp['last_total_revenue_qr_code'];
            $total_all['last_total_revenue_ticket'] += $arr_tmp['last_total_revenue_ticket'];

            $total_all['compare_count_shift'] += round($arr_tmp['compare_count_shift'] / count($group_data));
            $total_all['compare_total_count_ticket'] += round($arr_tmp['compare_total_count_ticket'] / count($group_data));
            $total_all['compare_total_revenue_ticket'] += round($arr_tmp['compare_total_revenue_ticket'] / count($group_data));
            $total_all['compare_total_revenue_pos'] += round($arr_tmp['compare_total_revenue_pos'] / count($group_data));
            $total_all['compare_total_revenue_charge'] += round($arr_tmp['compare_total_revenue_charge'] / count($group_data));
            $total_all['compare_total_revenue_month'] += round($arr_tmp['compare_total_revenue_month'] / count($group_data));
            $total_all['compare_total_revenue_qr_code'] += round($arr_tmp['compare_total_revenue_qr_code'] / count($group_data));

            $result[] = $arr_tmp;
        }

        $result[] = $total_all;
        return $result;
    }


    public function viewInvoice($data)
    {
        //set property params request
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        // set date
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));
        //get list ticket types
        $ticket_types = $this->ticket_types->getTicketTypeByCompanyIdAndByType($company_id, -1);
        //set property
        $ticket_invoice_result = [];

        //check transactions first of company
        $date_first = DB::table('transactions')
                ->where('company_id', $company_id)
                ->where('ticket_destroy', '!=', 1)
                ->whereIn('type', ['charge','pos', 'qrcode', 'deposit_month'])
                ->orderBy('activated')
                ->first();

        foreach ($ticket_types as $key => $value) {

            // set property --------------------------------------------------//
            $ticket_price_id = $value['ticket_prices'][count($value['ticket_prices']) - 1]['id'];
            // $ticket_price_id = 12;

            $price = $value['ticket_prices'][count($value['ticket_prices']) - 1]['price'];
            // $price = 5000;

            $ticket_type_id = $value['id'];
            // $ticket_type_id = 12;

            $type_ticket = $value['type'];
            // $type_ticket = 0;

            $begin_period_group = [];
            $released_in_period_group = [];
            $released_in_period_group2 = [];
            $used_in_period_group = [];
            //end set property -----------------------------------------------//


            //--------handle select all data--------------------------------------//
            //select start, end ticket number begin period in table ticket allocate
            $tkt_allo_begin_period = DB::table('ticket_allocate')
                                ->where('company_id', $company_id)
                                ->where('ticket_price_id', $ticket_price_id)
                                ->where('ticket_type_id', $ticket_type_id)
                                ->whereRaw('(updated_at < ?)', [$from_date])
                                ->orderBy('device_id')
                                ->orderBy('end_number')
                                ->orderBy('start_number')
                                ->get();

            if (count($tkt_allo_begin_period) > 0) {

                $tkt_allo_begin_period = collect($tkt_allo_begin_period)->groupBy('device_id')->toArray();
                // return $tkt_allo_begin_period;
                foreach ($tkt_allo_begin_period as $keys => $values) {

                    foreach ($values as $k => $v) {

                        if ($k + 1 < count($values) && $values[$k]->end_number != $values[$k + 1]->end_number){

                            $begin_period_obj = new \stdClass;
                            $begin_period_obj->device_id = (int)$keys;

                            if (!empty($v->current_number)) {

                                if($v->current_number == $v->end_number) continue;

                                //handle begin
                                $begin_period_obj->start_num_begin_period = $v->current_number + 1;
                                $begin_period_obj->end_num_begin_period = $v->end_number;
                                $begin_period_obj->total_begin_in_period =  (int) $begin_period_obj->end_num_begin_period - (int) $begin_period_obj->start_num_begin_period + 1;

                                //handle last
                                $begin_period_obj->start_num_last_period = $v->current_number + 1;
                                $begin_period_obj->end_num_last_period = $v->end_number;
                                $begin_period_obj->total_last_period =  (int) $begin_period_obj->end_num_last_period - (int) $begin_period_obj->start_num_last_period + 1;

                                $begin_period_obj->start_num_in_period = '';
                                $begin_period_obj->end_num_in_period = '';
                                $begin_period_obj->total_used_in_period = 0;
                                $begin_period_obj->total_remove_in_period = 0;
                                $begin_period_obj->str_num_remove_in_period = '';
                                $begin_period_obj->total_die_in_period = 0;
                                $begin_period_obj->str_num_die_in_period = '';
                                $begin_period_obj->total_cancle_in_period = 0;
                                $begin_period_obj->str_num_cancle_in_period = '';
                                $begin_period_obj->total_type_all_in_period  = 0;
                                $begin_period_obj->start_num_type_all_in_period = '';
                                $begin_period_obj->end_num_type_all_in_period =  '';

                            } else {

                                // if($v->start_number == $v->end_number) continue;

                                //handle begin

                                $begin_period_obj->start_num_begin_period = $v->start_number;
                                $begin_period_obj->end_num_begin_period = $v->end_number;
                                $begin_period_obj->total_begin_in_period = (int) $begin_period_obj->end_num_begin_period - (int) $begin_period_obj->start_num_begin_period + 1;

                                //handle last
                                $begin_period_obj->start_num_last_period = $v->start_number;
                                $begin_period_obj->end_num_last_period = $v->end_number;
                                $begin_period_obj->total_last_period = (int) $begin_period_obj->end_num_last_period - (int) $begin_period_obj->start_num_last_period + 1;
                                $begin_period_obj->start_num_in_period = '';
                                $begin_period_obj->end_num_in_period = '';
                                $begin_period_obj->total_used_in_period = 0;
                                $begin_period_obj->total_remove_in_period = 0;
                                $begin_period_obj->str_num_remove_in_period = '';
                                $begin_period_obj->total_die_in_period = 0;
                                $begin_period_obj->str_num_die_in_period = '';
                                $begin_period_obj->total_cancle_in_period = 0;
                                $begin_period_obj->str_num_cancle_in_period = '';
                                $begin_period_obj->total_type_all_in_period  = 0;
                                $begin_period_obj->start_num_type_all_in_period = '';
                                $begin_period_obj->end_num_type_all_in_period =  '';

                            }

                            $begin_period_group[] =  $begin_period_obj;
                        }

                        if ($k + 1 == count($values) ) {

                            $begin_period_obj = new \stdClass;
                            $begin_period_obj->device_id = (int)$keys;

                            if (!empty($v->current_number)) {

                                if($v->current_number == $v->end_number) break;

                                //handle begin

                                $begin_period_obj->start_num_begin_period = $v->current_number + 1;
                                $begin_period_obj->end_num_begin_period = $v->end_number;
                                $begin_period_obj->total_begin_in_period =  (int) $begin_period_obj->end_num_begin_period - (int) $begin_period_obj->start_num_begin_period + 1;

                                //handle last
                                $begin_period_obj->start_num_last_period = $v->current_number + 1;
                                $begin_period_obj->end_num_last_period = $v->end_number;
                                $begin_period_obj->total_last_period =  (int) $begin_period_obj->end_num_last_period - (int) $begin_period_obj->start_num_last_period + 1;
                                $begin_period_obj->start_num_in_period = '';
                                $begin_period_obj->end_num_in_period = '';
                                $begin_period_obj->total_used_in_period = 0;
                                $begin_period_obj->total_remove_in_period = 0;
                                $begin_period_obj->str_num_remove_in_period = '';
                                $begin_period_obj->total_die_in_period = 0;
                                $begin_period_obj->str_num_die_in_period = '';
                                $begin_period_obj->total_cancle_in_period = 0;
                                $begin_period_obj->str_num_cancle_in_period = '';
                                $begin_period_obj->total_type_all_in_period  = 0;
                                $begin_period_obj->start_num_type_all_in_period = '';
                                $begin_period_obj->end_num_type_all_in_period =  '';
                            } else {

                                if($v->start_number == $v->end_number) continue;

                                //handle begin
                                $begin_period_obj->start_num_begin_period = $v->start_number;
                                $begin_period_obj->end_num_begin_period = $v->end_number;
                                $begin_period_obj->total_begin_in_period = (int) $begin_period_obj->end_num_begin_period - (int) $begin_period_obj->start_num_begin_period + 1;

                                //handle last
                                $begin_period_obj->start_num_last_period = $v->start_number;
                                $begin_period_obj->end_num_last_period = $v->end_number;
                                $begin_period_obj->total_last_period = (int) $begin_period_obj->end_num_last_period - (int) $begin_period_obj->start_num_last_period + 1;
                                $begin_period_obj->start_num_in_period = '';
                                $begin_period_obj->end_num_in_period = '';
                                $begin_period_obj->total_used_in_period = 0;
                                $begin_period_obj->total_remove_in_period = 0;
                                $begin_period_obj->str_num_remove_in_period = '';
                                $begin_period_obj->total_die_in_period = 0;
                                $begin_period_obj->str_num_die_in_period = '';
                                $begin_period_obj->total_cancle_in_period = 0;
                                $begin_period_obj->str_num_cancle_in_period = '';
                                $begin_period_obj->total_type_all_in_period  = 0;
                                $begin_period_obj->start_num_type_all_in_period = '';
                                $begin_period_obj->end_num_type_all_in_period =  '';
                            }

                            $begin_period_group[] =  $begin_period_obj;
                        }
                    }
                }
            }

            //select start, end ticket number released in period  in table ticket allocate
            $tkt_allo_released_in_period = DB::table('ticket_allocate')
                        ->where('company_id', $company_id)
                        ->where('ticket_price_id', $ticket_price_id)
                        ->where('ticket_type_id', $ticket_type_id)
                        ->whereRaw('(created_at >= ? or updated_at >= ?) and (created_at <= ? or updated_at <= ?)',
                            [$from_date, $from_date, $to_date, $to_date]
                        )
                        ->orderBy('device_id')
                        ->orderBy('end_number')
                        ->orderBy('start_number')
                        ->get();
            //handle start, end ticket released in period
            if (count($tkt_allo_released_in_period) > 0) {

                $tkt_allo_released_in_period = collect($tkt_allo_released_in_period)->groupBy('device_id')->toArray();

                foreach ($tkt_allo_released_in_period as $keys => $values) {

                    foreach ($values as $k => $vl) {

                        //bao cao cho loai ve luot
                        if($type_ticket == 0){

                            if(((( (int)$vl->end_number - (int)$vl->start_number) + 1) == 300) && $vl->current_number != null){

                                $released_in_period_obj = new \stdClass;
                                $released_in_period_obj->device_id = (int)$keys;
                                $released_in_period_obj->start_num_in_period = $vl->start_number;
                                $released_in_period_obj->end_num_in_period = $vl->end_number;
                                $released_in_period_obj->start_num_last_period = $released_in_period_obj->start_num_in_period;
                                $released_in_period_obj->end_num_last_period = $released_in_period_obj->end_num_in_period;
                                $released_in_period_obj->total_last_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->total_begin_in_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->start_num_begin_period = '';
                                $released_in_period_obj->end_num_begin_period = '';
                                $released_in_period_obj->start_num_type_all_in_period = '';
                                $released_in_period_obj->end_num_type_all_in_period = '';
                                $released_in_period_obj->total_type_all_in_period = 0;
                                $released_in_period_obj->total_used_in_period = 0;
                                $released_in_period_obj->total_remove_in_period = 0;
                                $released_in_period_obj->str_num_remove_in_period = '';
                                $released_in_period_obj->total_die_in_period = 0;
                                $released_in_period_obj->str_num_die_in_period = '';
                                $released_in_period_obj->total_cancle_in_period = 0;
                                $released_in_period_obj->str_num_cancle_in_period = '';

                                $released_in_period_group[] =  $released_in_period_obj;
                            }

                            if(((((int)$vl->end_number - (int)$vl->start_number) + 1) == 300) && $vl->current_number == null){

                                $released_in_period_obj = new \stdClass;
                                $released_in_period_obj->price = $price;
                                $released_in_period_obj->device_id = (int)$keys;
                                $released_in_period_obj->start_num_in_period = $vl->start_number;
                                $released_in_period_obj->end_num_in_period = $vl->end_number;
                                $released_in_period_obj->start_num_last_period = $released_in_period_obj->start_num_in_period;
                                $released_in_period_obj->end_num_last_period = $released_in_period_obj->end_num_in_period;
                                $released_in_period_obj->total_last_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->total_begin_in_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->start_num_begin_period = '';
                                $released_in_period_obj->end_num_begin_period = '';
                                $released_in_period_obj->start_num_type_all_in_period = '';
                                $released_in_period_obj->end_num_type_all_in_period = '';
                                $released_in_period_obj->total_type_all_in_period = 0;
                                $released_in_period_obj->total_used_in_period = 0;
                                $released_in_period_obj->total_remove_in_period = 0;
                                $released_in_period_obj->str_num_remove_in_period = '';
                                $released_in_period_obj->total_die_in_period = 0;
                                $released_in_period_obj->str_num_die_in_period = '';
                                $released_in_period_obj->total_cancle_in_period = 0;
                                $released_in_period_obj->str_num_cancle_in_period = '';

                                $released_in_period_group2[] =  $released_in_period_obj;
                            }
                        }

                        //bao cao cho loai ve the thang
                        if($type_ticket == 1){

                            if(((((int)$vl->end_number - (int)$vl->start_number) + 1) == 100) && $vl->current_number != null){
                                $released_in_period_obj = new \stdClass;
                                // $released_in_period_obj->ticket_type_code = substr($value['order_code'], 0, 6);
                                // $released_in_period_obj->ticket_type_description = $value['description'];
                                // $released_in_period_obj->ticket_type_order_code = $value['order_code'];
                                // $released_in_period_obj->ticket_type_sign = $value['sign'];
                                // $released_in_period_obj->ticket_price_id = $ticket_price_id;
                                // $released_in_period_obj->price = $price;
                                $released_in_period_obj->device_id = (int)$keys;
                                $released_in_period_obj->start_num_in_period = $vl->start_number;
                                $released_in_period_obj->end_num_in_period = $vl->end_number;
                                $released_in_period_obj->start_num_last_period = $released_in_period_obj->start_num_in_period;
                                $released_in_period_obj->end_num_last_period = $released_in_period_obj->end_num_in_period;
                                $released_in_period_obj->total_last_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->total_begin_in_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->start_num_begin_period = '';
                                $released_in_period_obj->end_num_begin_period = '';
                                $released_in_period_obj->start_num_type_all_in_period = '';
                                $released_in_period_obj->end_num_type_all_in_period = '';
                                $released_in_period_obj->total_type_all_in_period = 0;
                                $released_in_period_obj->total_used_in_period = 0;
                                $released_in_period_obj->total_remove_in_period = 0;
                                $released_in_period_obj->str_num_remove_in_period = '';
                                $released_in_period_obj->total_die_in_period = 0;
                                $released_in_period_obj->str_num_die_in_period = '';
                                $released_in_period_obj->total_cancle_in_period = 0;
                                $released_in_period_obj->str_num_cancle_in_period = '';
                                $released_in_period_group[] =  $released_in_period_obj;
                            }

                            if(((((int)$vl->end_number - (int)$vl->start_number) + 1) == 100) && $vl->current_number == null){
                                $released_in_period_obj = new \stdClass;

                                $released_in_period_obj->device_id = (int)$keys;
                                $released_in_period_obj->start_num_in_period = $vl->start_number;
                                $released_in_period_obj->end_num_in_period = $vl->end_number;
                                $released_in_period_obj->start_num_last_period = $released_in_period_obj->start_num_in_period;
                                $released_in_period_obj->end_num_last_period = $released_in_period_obj->end_num_in_period;
                                $released_in_period_obj->total_last_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->total_begin_in_period = (int)$released_in_period_obj->end_num_last_period - $released_in_period_obj->start_num_last_period + 1;
                                $released_in_period_obj->start_num_begin_period = '';
                                $released_in_period_obj->end_num_begin_period = '';
                                $released_in_period_obj->start_num_type_all_in_period = '';
                                $released_in_period_obj->end_num_type_all_in_period = '';
                                $released_in_period_obj->total_type_all_in_period = 0;
                                $released_in_period_obj->total_used_in_period = 0;
                                $released_in_period_obj->total_remove_in_period = 0;
                                $released_in_period_obj->str_num_remove_in_period = '';
                                $released_in_period_obj->total_die_in_period = 0;
                                $released_in_period_obj->str_num_die_in_period = '';
                                $released_in_period_obj->total_cancle_in_period = 0;
                                $released_in_period_obj->str_num_cancle_in_period = '';

                                $released_in_period_group2[] =  $released_in_period_obj;
                            }
                        }
                    }
                }
            }

            //select start, end ticket number used in the period
            $tkt_used_transactions =  collect(DB::select('
                SELECT *
                FROM transactions
                WHERE transactions.company_id = ' . (int) $company_id . '
                AND transactions.ticket_price_id = ' . (int) $ticket_price_id . '
                AND transactions.type in ("charge","pos", "qrcode", "deposit_month")
                AND transactions.activated >= "'.$from_date.'"
                AND transactions.activated <= "'.$to_date .'"
                order by cast(transactions.ticket_number as unsigned)
            '))->groupBy('device_id')->toArray();
            //handle start, end ticket number used in the period
            if (count($tkt_used_transactions) > 0) {

                foreach ($tkt_used_transactions as $key_transaction => $tkt_used_transaction) {

                    $used_in_period_obj = new \stdClass;
                    $used_in_period_obj->device_id = $key_transaction;
                    $used_in_period_obj->total_begin_in_period = 0;
                    $used_in_period_obj->start_num_begin_period = '';
                    $used_in_period_obj->end_num_begin_period = '';
                    $used_in_period_obj->start_num_in_period = '';
                    $used_in_period_obj->end_num_in_period = '';
                    $used_in_period_obj->total_used_in_period = 0;
                    $used_in_period_obj->total_remove_in_period = 0;
                    $used_in_period_obj->str_num_remove_in_period = '';
                    $used_in_period_obj->total_die_in_period = 0;
                    $used_in_period_obj->str_num_die_in_period = '';
                    $used_in_period_obj->total_cancle_in_period = 0;
                    $used_in_period_obj->str_num_cancle_in_period = '';
                    $used_in_period_obj->start_num_last_period = '';
                    $used_in_period_obj->end_num_last_period = '';
                    $used_in_period_obj->total_last_period = 0;
                    $used_in_period_obj->total_type_all_in_period  = 0;
                    $used_in_period_obj->start_num_type_all_in_period = '';
                    $used_in_period_obj->end_num_type_all_in_period =  '';

                    $n = true;
                    $tmp_number = 0;

                    for ($i = 0; $i < count($tkt_used_transaction); $i++) {

                        //handle count array = 1 item
                        if (count($tkt_used_transaction) == 1) {

                            $used_in_period_obj->start_num_type_all_in_period = $tkt_used_transaction[$i]->ticket_number;
                            $used_in_period_obj->end_num_type_all_in_period =  $tkt_used_transaction[$i]->ticket_number;
                            $used_in_period_obj->total_type_all_in_period  = 1;

                            if ($tkt_used_transaction[$i]->ticket_destroy == 1) {
                                $used_in_period_obj->total_remove_in_period += 1;
                                $used_in_period_obj->str_num_remove_in_period .= $tkt_used_transaction[$i]->ticket_number . ';';
                            } else {
                                $used_in_period_obj->total_used_in_period += 1;
                            }

                            if ($used_in_period_obj->str_num_remove_in_period != '') {
                                $used_in_period_obj->str_num_remove_in_period = substr_replace($used_in_period_obj->str_num_remove_in_period, "", -1);
                            }

                            $used_in_period_obj->start_num_last_period = (int) $tkt_used_transaction[$i]->ticket_number + 1;

                            $used_in_period_group[] = $used_in_period_obj;
                        } else if (count($tkt_used_transaction) > 1) {



                            if ($tkt_used_transaction[$i]->ticket_destroy == 1) {
                                $used_in_period_obj->total_remove_in_period += 1;
                                $used_in_period_obj->str_num_remove_in_period .= $tkt_used_transaction[$i]->ticket_number . ';';
                            } else {
                                $used_in_period_obj->total_used_in_period += 1;
                            }

                            if ($n) {
                                $used_in_period_obj->start_num_type_all_in_period = $tkt_used_transaction[$i]->ticket_number;
                                $n = false;
                            }

                            $used_in_period_obj->end_num_type_all_in_period =  $tkt_used_transaction[$i]->ticket_number;

                            //gay khuc
                            $j = $i + 1;

                            if (($j < count($tkt_used_transaction)) && ((int) $tkt_used_transaction[$j]->ticket_number - (int) $tkt_used_transaction[$i]->ticket_number) == 1){

                                if($type_ticket == 0){

                                    if((int)$tkt_used_transaction[$i]->ticket_number % 300 == 0){

                                        $used_in_period_obj->total_type_all_in_period  = (int) $used_in_period_obj->end_num_type_all_in_period - (int) $used_in_period_obj->start_num_type_all_in_period + 1;
                                        $used_in_period_obj->start_num_last_period = (int) $tkt_used_transaction[$i]->ticket_number + 1;

                                        if ($used_in_period_obj->str_num_remove_in_period != '') {
                                            $used_in_period_obj->str_num_remove_in_period = substr_replace($used_in_period_obj->str_num_remove_in_period, "", -1);
                                        }
                                        $used_in_period_group[] = $used_in_period_obj;

                                        $used_in_period_obj = new \stdClass;
                                        $used_in_period_obj->device_id = $key_transaction;
                                        $used_in_period_obj->total_begin_in_period = 0;
                                        $used_in_period_obj->start_num_begin_period = '';
                                        $used_in_period_obj->end_num_begin_period = '';
                                        $used_in_period_obj->start_num_in_period = '';
                                        $used_in_period_obj->end_num_in_period = '';
                                        $used_in_period_obj->total_used_in_period = 0;
                                        $used_in_period_obj->total_remove_in_period = 0;
                                        $used_in_period_obj->str_num_remove_in_period = '';
                                        $used_in_period_obj->total_die_in_period = 0;
                                        $used_in_period_obj->str_num_die_in_period = '';
                                        $used_in_period_obj->total_cancle_in_period = 0;
                                        $used_in_period_obj->str_num_cancle_in_period = '';
                                        $used_in_period_obj->start_num_last_period = '';
                                        $used_in_period_obj->end_num_last_period = '';
                                        $used_in_period_obj->total_last_period = 0;
                                        $used_in_period_obj->total_type_all_in_period  = 0;
                                        $used_in_period_obj->start_num_type_all_in_period = '';
                                        $used_in_period_obj->end_num_type_all_in_period =  '';
                                    }
                                }elseif($type_ticket == 1){

                                    if((int)$tkt_used_transaction[$i]->ticket_number % 100 == 0){

                                        $used_in_period_obj->total_type_all_in_period  = (int) $used_in_period_obj->end_num_type_all_in_period - (int) $used_in_period_obj->start_num_type_all_in_period + 1;
                                        $used_in_period_obj->start_num_last_period = (int) $tkt_used_transaction[$i]->ticket_number + 1;

                                        if ($used_in_period_obj->str_num_remove_in_period != '') {
                                            $used_in_period_obj->str_num_remove_in_period = substr_replace($used_in_period_obj->str_num_remove_in_period, "", -1);
                                        }
                                        $used_in_period_group[] = $used_in_period_obj;

                                        $used_in_period_obj = new \stdClass;
                                        $used_in_period_obj->device_id = $key_transaction;
                                        $used_in_period_obj->total_begin_in_period = 0;
                                        $used_in_period_obj->start_num_begin_period = '';
                                        $used_in_period_obj->end_num_begin_period = '';
                                        $used_in_period_obj->start_num_in_period = '';
                                        $used_in_period_obj->end_num_in_period = '';
                                        $used_in_period_obj->total_used_in_period = 0;
                                        $used_in_period_obj->total_remove_in_period = 0;
                                        $used_in_period_obj->str_num_remove_in_period = '';
                                        $used_in_period_obj->total_die_in_period = 0;
                                        $used_in_period_obj->str_num_die_in_period = '';
                                        $used_in_period_obj->total_cancle_in_period = 0;
                                        $used_in_period_obj->str_num_cancle_in_period = '';
                                        $used_in_period_obj->start_num_last_period = '';
                                        $used_in_period_obj->end_num_last_period = '';
                                        $used_in_period_obj->total_last_period = 0;
                                        $used_in_period_obj->total_type_all_in_period  = 0;
                                        $used_in_period_obj->start_num_type_all_in_period = '';
                                        $used_in_period_obj->end_num_type_all_in_period =  '';
                                    }
                                }
                            }

                            if (($j < count($tkt_used_transaction)) && ((int) $tkt_used_transaction[$j]->ticket_number - (int) $tkt_used_transaction[$i]->ticket_number) != 1) {

                                $tmp_number += 1;

                                $used_in_period_obj->total_type_all_in_period  = (int) $used_in_period_obj->end_num_type_all_in_period - (int) $used_in_period_obj->start_num_type_all_in_period + 1;
                                $used_in_period_obj->start_num_last_period = (int) $tkt_used_transaction[$i]->ticket_number + 1;

                                if ($used_in_period_obj->str_num_remove_in_period != '') {
                                    $used_in_period_obj->str_num_remove_in_period = substr_replace($used_in_period_obj->str_num_remove_in_period, "", -1);
                                }
                                $used_in_period_group[] = $used_in_period_obj;
                                $n = true;

                                $used_in_period_obj = new \stdClass;
                                $used_in_period_obj->device_id = $key_transaction;
                                $used_in_period_obj->total_begin_in_period = 0;
                                $used_in_period_obj->start_num_begin_period = '';
                                $used_in_period_obj->end_num_begin_period = '';
                                $used_in_period_obj->start_num_in_period = '';
                                $used_in_period_obj->end_num_in_period = '';
                                $used_in_period_obj->total_used_in_period = 0;
                                $used_in_period_obj->total_remove_in_period = 0;
                                $used_in_period_obj->str_num_remove_in_period = '';
                                $used_in_period_obj->total_die_in_period = 0;
                                $used_in_period_obj->str_num_die_in_period = '';
                                $used_in_period_obj->total_cancle_in_period = 0;
                                $used_in_period_obj->str_num_cancle_in_period = '';
                                $used_in_period_obj->start_num_last_period = '';
                                $used_in_period_obj->end_num_last_period = '';
                                $used_in_period_obj->total_last_period = 0;
                                $used_in_period_obj->total_type_all_in_period  = 0;
                                $used_in_period_obj->start_num_type_all_in_period = '';
                                $used_in_period_obj->end_num_type_all_in_period =  '';
                            }

                            if ($j == count($tkt_used_transaction)) {

                                if (((int) $tkt_used_transaction[$i - 1]->ticket_number - (int) $tkt_used_transaction[$i]->ticket_number) != 1) {

                                    $used_in_period_obj->total_type_all_in_period  = (int) $used_in_period_obj->end_num_type_all_in_period - (int) $used_in_period_obj->start_num_type_all_in_period + 1;
                                    $used_in_period_obj->start_num_last_period = (int) $tkt_used_transaction[$i]->ticket_number + 1;
                                    if ($used_in_period_obj->str_num_remove_in_period != '') {
                                        $used_in_period_obj->str_num_remove_in_period = substr_replace($used_in_period_obj->str_num_remove_in_period, "", -1);
                                    }
                                    $used_in_period_group[] = $used_in_period_obj;

                                    $used_in_period_obj = new \stdClass;
                                    // $used_in_period_obj->ticket_type_code = substr($value['order_code'], 0, 6);
                                    // $used_in_period_obj->ticket_type_description = $value['description'];
                                    // $used_in_period_obj->ticket_type_order_code = $value['order_code'];
                                    // $used_in_period_obj->ticket_type_sign = $value['sign'];
                                    // $used_in_period_obj->ticket_price_id = $ticket_price_id;
                                    // $used_in_period_obj->price = $value['ticket_prices'][0]['price'];
                                    $used_in_period_obj->device_id = $key_transaction;

                                    $used_in_period_obj->total_begin_in_period = 0;

                                    $used_in_period_obj->start_num_begin_period = '';
                                    $used_in_period_obj->end_num_begin_period = '';

                                    $used_in_period_obj->start_num_in_period = '';
                                    $used_in_period_obj->end_num_in_period = '';

                                    $used_in_period_obj->total_type_all_in_period  = 0;
                                    $used_in_period_obj->start_num_type_all_in_period = '';
                                    $used_in_period_obj->end_num_type_all_in_period =  '';

                                    $used_in_period_obj->total_used_in_period = 0;

                                    $used_in_period_obj->total_remove_in_period = 0;
                                    $used_in_period_obj->str_num_remove_in_period = '';

                                    $used_in_period_obj->total_die_in_period = 0;
                                    $used_in_period_obj->str_num_die_in_period = '';

                                    $used_in_period_obj->total_cancle_in_period = 0;
                                    $used_in_period_obj->str_num_cancle_in_period = '';

                                    $used_in_period_obj->start_num_last_period = '';
                                    $used_in_period_obj->end_num_last_period = '';
                                    $used_in_period_obj->total_last_period = 0;
                                }
                                $n = true;
                            }
                        }
                    }
                }
            }

            //--------end handle select all data----------------------------------//

            //----------------++++++++++++++++++ version 2 . handle data result
            $tmp_arr_merges = array_merge($used_in_period_group,$begin_period_group,$released_in_period_group);
            $tmp_arr_merges = collect($tmp_arr_merges)->groupBy('device_id')->toArray();
            $max_begin_tkt = '1';
            $max_last_tkt = '1';
            $tmp_released_not_used_all= [];
            $tmp_begin_period_not_used_all= [];

            foreach ($tmp_arr_merges as $merge_key => $merge_values) {

                $tmp_pushs1 = [];
                $tmp_begin_period_not_used_only = [];

                $tmp_pushs2 = [];
                $tmp_released_not_used_only = [];

                foreach ($merge_values as $merge_k => $merge_v) {

                    if($merge_v->start_num_type_all_in_period != '' || $merge_v->start_num_type_all_in_period != null){
                        $tmp_pushs1[]= $merge_v;
                        $tmp_pushs2[]= $merge_v;
                    }

                    if($merge_v->start_num_type_all_in_period == '' || $merge_v->start_num_type_all_in_period == null){

                        if($merge_v->start_num_begin_period != '' || $merge_v->start_num_begin_period != null){

                            $tmp_begin_period_not_used_only[$merge_k]= $merge_v;

                            if(count($tmp_pushs1)){

                                foreach ($tmp_pushs1 as $push_k => $push_v) {

                                    if(
                                        ($push_v->start_num_type_all_in_period >= $merge_v->start_num_begin_period &&
                                        $push_v->start_num_type_all_in_period <= $merge_v->end_num_begin_period) ||
                                        ($push_v->end_num_type_all_in_period >= $merge_v->start_num_begin_period &&
                                        $push_v->end_num_type_all_in_period <= $merge_v->end_num_begin_period)
                                    ){

                                        //remove value in arr used
                                        unset($tmp_begin_period_not_used_only[$merge_k]);

                                        $obj_merge = new \stdClass;
                                        $obj_merge->ticket_type_code = substr($value['order_code'], 0, 6);
                                        $obj_merge->ticket_type_description = $value['description'];
                                        $obj_merge->ticket_type_order_code = $value['order_code'];
                                        $obj_merge->ticket_type_sign = $value['sign'];
                                        $obj_merge->ticket_price_id = $ticket_price_id;
                                        $obj_merge->price = $price;
                                        $obj_merge->device_id = (int)$merge_key;

                                        //bao cao lan dau
                                        if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){

                                            $obj_merge->start_num_begin_period = '';
                                            $obj_merge->end_num_begin_period = '';

                                            $obj_merge->start_num_in_period = $merge_v->start_num_begin_period;
                                            $obj_merge->end_num_in_period = $merge_v->end_num_begin_period;

                                            if($push_v->start_num_type_all_in_period != $merge_v->start_num_begin_period){
                                                $obj_merge->start_num_in_period = $push_v->start_num_type_all_in_period;
                                            }
                                            $obj_merge->total_begin_in_period = (int)$obj_merge->end_num_in_period -  (int)$obj_merge->start_num_in_period + 1;

                                            $obj_merge->start_num_type_all_in_period = $push_v->start_num_type_all_in_period;
                                            $obj_merge->end_num_type_all_in_period = $push_v->end_num_type_all_in_period;
                                            $obj_merge->total_type_all_in_period = $push_v->total_type_all_in_period;
                                            $obj_merge->total_used_in_period = $push_v->total_used_in_period;
                                            $obj_merge->total_remove_in_period = $push_v->total_remove_in_period;
                                            $obj_merge->str_num_remove_in_period = $push_v->str_num_remove_in_period;
                                            $obj_merge->total_die_in_period = $push_v->total_die_in_period;
                                            $obj_merge->str_num_die_in_period = $push_v->str_num_die_in_period;
                                            $obj_merge->total_cancle_in_period = $push_v->total_cancle_in_period;
                                            $obj_merge->str_num_cancle_in_period = $push_v->str_num_cancle_in_period;

                                            if ($obj_merge->end_num_type_all_in_period ==  $merge_v->end_num_begin_period) {
                                                $obj_merge->start_num_last_period = '';
                                                $obj_merge->end_num_last_period = '';
                                                $obj_merge->total_last_period = 0;
                                            } else {
                                                $obj_merge->start_num_last_period =  $obj_merge->end_num_type_all_in_period + 1;
                                                $obj_merge->end_num_last_period =  $merge_v->end_num_begin_period;
                                                $obj_merge->total_last_period = (int) $obj_merge->end_num_last_period - (int) $obj_merge->start_num_last_period + 1;
                                            }

                                            //check numbe max of price
                                            if ((int) $obj_merge->end_num_in_period >= (int) $max_begin_tkt) {
                                                $max_begin_tkt = $obj_merge->end_num_in_period + 1;
                                            }

                                            if ((int) $obj_merge->end_num_last_period >= (int) $max_last_tkt) {
                                                $max_last_tkt = $obj_merge->end_num_last_period + 1;
                                            }
                                        }
                                        //bao cao tren lan 2
                                        else{

                                            $obj_merge->start_num_begin_period = $merge_v->start_num_begin_period;
                                            $obj_merge->end_num_begin_period = $merge_v->end_num_begin_period;

                                            $obj_merge->start_num_in_period = '';
                                            $obj_merge->end_num_in_period = '';

                                            if($push_v->start_num_type_all_in_period != $merge_v->start_num_begin_period){
                                                $obj_merge->start_num_begin_period = $push_v->start_num_type_all_in_period;
                                            }
                                            $obj_merge->total_begin_in_period = (int)$obj_merge->end_num_begin_period -  (int)$obj_merge->start_num_begin_period + 1;

                                            $obj_merge->start_num_type_all_in_period = $push_v->start_num_type_all_in_period;
                                            $obj_merge->end_num_type_all_in_period = $push_v->end_num_type_all_in_period;
                                            $obj_merge->total_type_all_in_period = $push_v->total_type_all_in_period;
                                            $obj_merge->total_used_in_period = $push_v->total_used_in_period;
                                            $obj_merge->total_remove_in_period = $push_v->total_remove_in_period;
                                            $obj_merge->str_num_remove_in_period = $push_v->str_num_remove_in_period;
                                            $obj_merge->total_die_in_period = $push_v->total_die_in_period;
                                            $obj_merge->str_num_die_in_period = $push_v->str_num_die_in_period;
                                            $obj_merge->total_cancle_in_period = $push_v->total_cancle_in_period;
                                            $obj_merge->str_num_cancle_in_period = $push_v->str_num_cancle_in_period;

                                            if ($obj_merge->end_num_type_all_in_period ==  $merge_v->end_num_begin_period) {
                                                $obj_merge->start_num_last_period = '';
                                                $obj_merge->end_num_last_period = '';
                                                $obj_merge->total_last_period = 0;
                                            } else {
                                                $obj_merge->start_num_last_period =  $obj_merge->end_num_type_all_in_period + 1;
                                                $obj_merge->end_num_last_period =  $merge_v->end_num_begin_period;
                                                $obj_merge->total_last_period = (int) $obj_merge->end_num_last_period - (int) $obj_merge->start_num_last_period + 1;
                                            }

                                            //check numbe max of price
                                            if ((int) $obj_merge->end_num_begin_period >= (int) $max_begin_tkt) {
                                                $max_begin_tkt = $obj_merge->end_num_begin_period + 1;
                                            }

                                            if ((int) $obj_merge->end_num_last_period >= (int) $max_last_tkt) {
                                                $max_last_tkt = $obj_merge->end_num_last_period + 1;
                                            }
                                        }

                                        $ticket_invoice_result[] = $obj_merge;
                                        unset($tmp_pushs1[$push_k]);
                                    }
                                }
                            }

                            if(count($tmp_begin_period_not_used_only) > 0 ) {
                                $tmp_begin_period_not_used_all[] = $tmp_begin_period_not_used_only;
                            }
                        }

                        if($merge_v->start_num_in_period != '' || $merge_v->start_num_in_period != null){

                            $tmp_released_not_used_only[$merge_k]= $merge_v;

                            if(count($tmp_pushs2)){

                                foreach ($tmp_pushs2 as $push_k => $push_v) {

                                    if(
                                        ((int)$push_v->start_num_type_all_in_period >= (int)$merge_v->start_num_in_period &&
                                        (int)$push_v->start_num_type_all_in_period <= (int)$merge_v->end_num_in_period) ||
                                        ((int)$push_v->end_num_type_all_in_period >= (int)$merge_v->start_num_in_period &&
                                        (int)$push_v->end_num_type_all_in_period <= (int)$merge_v->end_num_in_period)
                                    ){

                                        //remove value in arr used
                                        unset($tmp_released_not_used_only[$merge_k]);

                                        $obj_merge = new \stdClass;
                                        $obj_merge->ticket_type_code = substr($value['order_code'], 0, 6);
                                        $obj_merge->ticket_type_description = $value['description'];
                                        $obj_merge->ticket_type_order_code = $value['order_code'];
                                        $obj_merge->ticket_type_sign = $value['sign'];
                                        $obj_merge->ticket_price_id = $ticket_price_id;
                                        $obj_merge->price = $price;
                                        $obj_merge->device_id = (int)$merge_key;

                                        //bao cao lan dau
                                        if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){

                                            $obj_merge->start_num_begin_period = '';
                                            $obj_merge->end_num_begin_period = '';

                                            $obj_merge->start_num_in_period = $merge_v->start_num_in_period;
                                            $obj_merge->end_num_in_period = $merge_v->end_num_in_period;

                                            if($push_v->start_num_type_all_in_period != $merge_v->start_num_begin_period){
                                                $obj_merge->start_num_in_period = $push_v->start_num_type_all_in_period;
                                            }
                                            $obj_merge->total_begin_in_period = (int)$obj_merge->end_num_in_period -  (int)$obj_merge->start_num_in_period + 1;

                                            $obj_merge->start_num_type_all_in_period = $push_v->start_num_type_all_in_period;
                                            $obj_merge->end_num_type_all_in_period = $push_v->end_num_type_all_in_period;
                                            $obj_merge->total_type_all_in_period = $push_v->total_type_all_in_period;
                                            $obj_merge->total_used_in_period = $push_v->total_used_in_period;
                                            $obj_merge->total_remove_in_period = $push_v->total_remove_in_period;
                                            $obj_merge->str_num_remove_in_period = $push_v->str_num_remove_in_period;
                                            $obj_merge->total_die_in_period = $push_v->total_die_in_period;
                                            $obj_merge->str_num_die_in_period = $push_v->str_num_die_in_period;
                                            $obj_merge->total_cancle_in_period = $push_v->total_cancle_in_period;
                                            $obj_merge->str_num_cancle_in_period = $push_v->str_num_cancle_in_period;

                                            if ($obj_merge->end_num_type_all_in_period ==  $merge_v->end_num_in_period) {
                                                $obj_merge->start_num_last_period = '';
                                                $obj_merge->end_num_last_period = '';
                                                $obj_merge->total_last_period = 0;
                                            } else {
                                                $obj_merge->start_num_last_period =  $obj_merge->end_num_type_all_in_period + 1;
                                                $obj_merge->end_num_last_period =  $merge_v->end_num_in_period;
                                                $obj_merge->total_last_period = (int) $obj_merge->end_num_last_period - (int) $obj_merge->start_num_last_period + 1;
                                            }

                                            //check numbe max of price
                                            if ((int) $obj_merge->end_num_in_period > (int) $max_begin_tkt) {
                                                $max_begin_tkt = $obj_merge->end_num_in_period;
                                            }

                                            if ((int) $obj_merge->end_num_last_period > (int) $max_last_tkt) {
                                                $max_last_tkt = $obj_merge->end_num_last_period;
                                            }
                                        }
                                        //bao cao lan 2 tro di
                                        else{

                                            $obj_merge->start_num_begin_period = $merge_v->start_num_in_period;
                                            $obj_merge->end_num_begin_period = $merge_v->end_num_in_period;

                                            $obj_merge->start_num_in_period = '';
                                            $obj_merge->end_num_in_period = '';

                                            if($push_v->start_num_type_all_in_period != $merge_v->start_num_begin_period){
                                                $obj_merge->start_num_begin_period = $push_v->start_num_type_all_in_period;
                                            }
                                            $obj_merge->total_begin_in_period = (int)$obj_merge->end_num_begin_period -  (int)$obj_merge->start_num_begin_period + 1;

                                            $obj_merge->start_num_type_all_in_period = $push_v->start_num_type_all_in_period;
                                            $obj_merge->end_num_type_all_in_period = $push_v->end_num_type_all_in_period;
                                            $obj_merge->total_type_all_in_period = $push_v->total_type_all_in_period;
                                            $obj_merge->total_used_in_period = $push_v->total_used_in_period;
                                            $obj_merge->total_remove_in_period = $push_v->total_remove_in_period;
                                            $obj_merge->str_num_remove_in_period = $push_v->str_num_remove_in_period;
                                            $obj_merge->total_die_in_period = $push_v->total_die_in_period;
                                            $obj_merge->str_num_die_in_period = $push_v->str_num_die_in_period;
                                            $obj_merge->total_cancle_in_period = $push_v->total_cancle_in_period;
                                            $obj_merge->str_num_cancle_in_period = $push_v->str_num_cancle_in_period;

                                            if ($obj_merge->end_num_type_all_in_period ==  $merge_v->end_num_in_period) {
                                                $obj_merge->start_num_last_period = '';
                                                $obj_merge->end_num_last_period = '';
                                                $obj_merge->total_last_period = 0;
                                            } else {
                                                $obj_merge->start_num_last_period =  $obj_merge->end_num_type_all_in_period + 1;
                                                $obj_merge->end_num_last_period =  $merge_v->end_num_in_period;
                                                $obj_merge->total_last_period = (int) $obj_merge->end_num_last_period - (int) $obj_merge->start_num_last_period + 1;
                                            }

                                            //check numbe max of price
                                            if ((int) $obj_merge->end_num_begin_period > (int) $max_begin_tkt) {
                                                $max_begin_tkt = $obj_merge->end_num_begin_period;
                                            }

                                            if ((int) $obj_merge->end_num_last_period > (int) $max_last_tkt) {
                                                $max_last_tkt = $obj_merge->end_num_last_period;
                                            }
                                        }

                                        $ticket_invoice_result[] = $obj_merge;

                                        //remove value checked
                                        unset($tmp_pushs2[$push_k]);
                                    }
                                }
                            }

                            if(count($tmp_released_not_used_only) > 0 ) {
                                $tmp_released_not_used_all[] = $tmp_released_not_used_only;
                            }
                        }
                    }
                }
            }

            //hadle ticket allocate released , not used in priod
            if(count($released_in_period_group2) > 0){

                $released_in_period_group2 = collect($released_in_period_group2)->groupBy('device_id')->toArray();

                foreach ($released_in_period_group2 as $keys => $values) {

                    foreach ($values as $k => $val) {

                        $obj_released_not_used = new \stdClass;
                        $obj_released_not_used->ticket_type_code = substr($value['order_code'], 0, 6);
                        $obj_released_not_used->ticket_type_description = $value['description'];
                        $obj_released_not_used->ticket_type_order_code = $value['order_code'];
                        $obj_released_not_used->ticket_type_sign = $value['sign'];
                        $obj_released_not_used->ticket_price_id = $ticket_price_id;
                        $obj_released_not_used->price = $price;
                        $obj_released_not_used->device_id = (int)$keys;

                        //bao cao lan dau
                        if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){
                            $obj_released_not_used->start_num_begin_period = '';
                            $obj_released_not_used->end_num_begin_period = '';
                            $obj_released_not_used->start_num_in_period = $val->start_num_in_period;
                            $obj_released_not_used->end_num_in_period = $val->end_num_in_period;
                            $obj_released_not_used->total_begin_in_period = (int)$obj_released_not_used->end_num_in_period -  (int)$obj_released_not_used->start_num_in_period + 1;
                            $obj_released_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                            $obj_released_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                            $obj_released_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                            $obj_released_not_used->total_used_in_period = $val->total_used_in_period;
                            $obj_released_not_used->total_remove_in_period = $val->total_remove_in_period;
                            $obj_released_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                            $obj_released_not_used->total_die_in_period = $val->total_die_in_period;
                            $obj_released_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                            $obj_released_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                            $obj_released_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                            $obj_released_not_used->start_num_last_period =  $val->start_num_last_period;
                            $obj_released_not_used->end_num_last_period =  $val->end_num_last_period;
                            $obj_released_not_used->total_last_period = (int) $obj_released_not_used->end_num_last_period - (int) $obj_released_not_used->start_num_last_period + 1;

                            //check numbe max of price
                            if ((int) $obj_released_not_used->end_num_in_period >= (int) $max_begin_tkt)
                                $max_begin_tkt = $obj_released_not_used->end_num_in_period + 1;

                            if ((int) $obj_released_not_used->end_num_last_period >= (int) $max_last_tkt)
                                $max_last_tkt = $obj_released_not_used->end_num_last_period + 1;
                        }

                        //bao cao lan 2 tro di
                        else{

                            $obj_released_not_used->start_num_begin_period = $val->start_num_in_period;
                            $obj_released_not_used->end_num_begin_period = $val->end_num_in_period;
                            $obj_released_not_used->start_num_in_period = '';
                            $obj_released_not_used->end_num_in_period = '';
                            $obj_released_not_used->total_begin_in_period = (int)$obj_released_not_used->end_num_begin_period -  (int)$obj_released_not_used->start_num_begin_period + 1;
                            $obj_released_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                            $obj_released_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                            $obj_released_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                            $obj_released_not_used->total_used_in_period = $val->total_used_in_period;
                            $obj_released_not_used->total_remove_in_period = $val->total_remove_in_period;
                            $obj_released_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                            $obj_released_not_used->total_die_in_period = $val->total_die_in_period;
                            $obj_released_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                            $obj_released_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                            $obj_released_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                            $obj_released_not_used->start_num_last_period =  $val->start_num_last_period;
                            $obj_released_not_used->end_num_last_period =  $val->end_num_last_period;
                            $obj_released_not_used->total_last_period = (int) $obj_released_not_used->end_num_last_period - (int) $obj_released_not_used->start_num_last_period + 1;

                            //check numbe max of price
                            if ((int) $obj_released_not_used->end_num_begin_period >= (int) $max_begin_tkt) {
                                $max_begin_tkt = $obj_released_not_used->end_num_begin_period + 1;
                            }

                            if ((int) $obj_released_not_used->end_num_last_period >= (int) $max_last_tkt) {
                                $max_last_tkt = $obj_released_not_used->end_num_last_period + 1;
                            }
                        }

                        $ticket_invoice_result[] = $obj_released_not_used;
                    }
                }
            }

            //hadle ticket allocate released , not used in priod
            if(count($tmp_released_not_used_all) > 0){

                $tmp_released_not_used_all_handle = [];
                foreach ($tmp_released_not_used_all as $keys => $values) {
                    foreach ($values as $k => $val) {
                        $tmp_released_not_used_all_handle[] = $val;
                    }
                }

                // return  $tmp_released_not_used_all_handle;

                //handle array data result
                $tmp_released_not_used_all_handle = $this->unique_multidim_array($tmp_released_not_used_all_handle,'end_num_in_period');
                foreach ($tmp_released_not_used_all_handle as $k => $val) {

                    $obj_tmp_arr_not_used = new \stdClass;
                    $obj_tmp_arr_not_used->ticket_type_code = substr($value['order_code'], 0, 6);
                    $obj_tmp_arr_not_used->ticket_type_description = $value['description'];
                    $obj_tmp_arr_not_used->ticket_type_order_code = $value['order_code'];
                    $obj_tmp_arr_not_used->ticket_type_sign = $value['sign'];
                    $obj_tmp_arr_not_used->ticket_price_id = $ticket_price_id;
                    $obj_tmp_arr_not_used->price = $price;
                    $obj_tmp_arr_not_used->device_id = (int)$val->device_id;

                    //bao cao lan dau
                    if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){

                        $obj_tmp_arr_not_used->start_num_begin_period = '';
                        $obj_tmp_arr_not_used->end_num_begin_period = '';
                        $obj_tmp_arr_not_used->start_num_in_period = $val->start_num_in_period;
                        $obj_tmp_arr_not_used->end_num_in_period = $val->end_num_in_period;
                        $obj_tmp_arr_not_used->total_begin_in_period = (int)$obj_tmp_arr_not_used->end_num_in_period -  (int)$obj_tmp_arr_not_used->start_num_in_period + 1;
                        $obj_tmp_arr_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                        $obj_tmp_arr_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                        $obj_tmp_arr_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                        $obj_tmp_arr_not_used->total_used_in_period = $val->total_used_in_period;
                        $obj_tmp_arr_not_used->total_remove_in_period = $val->total_remove_in_period;
                        $obj_tmp_arr_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                        $obj_tmp_arr_not_used->total_die_in_period = $val->total_die_in_period;
                        $obj_tmp_arr_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                        $obj_tmp_arr_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                        $obj_tmp_arr_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                        $obj_tmp_arr_not_used->start_num_last_period =  $val->start_num_last_period;
                        $obj_tmp_arr_not_used->end_num_last_period =  $val->end_num_last_period;
                        $obj_tmp_arr_not_used->total_last_period = (int) $obj_tmp_arr_not_used->end_num_last_period - (int) $obj_tmp_arr_not_used->start_num_last_period + 1;

                        //check numbe max of price
                        if ((int) $obj_tmp_arr_not_used->end_num_in_period >= (int) $max_begin_tkt)
                            $max_begin_tkt = $obj_tmp_arr_not_used->end_num_in_period + 1;

                        if ((int) $obj_tmp_arr_not_used->end_num_last_period >= (int) $max_last_tkt)
                            $max_last_tkt = $obj_tmp_arr_not_used->end_num_last_period + 1;
                    }

                    //bao cao lan 2 tro di
                    else{

                        $obj_tmp_arr_not_used->start_num_begin_period = $val->start_num_in_period;
                        $obj_tmp_arr_not_used->end_num_begin_period = $val->end_num_in_period;
                        $obj_tmp_arr_not_used->start_num_in_period = '';
                        $obj_tmp_arr_not_used->end_num_in_period = '';
                        $obj_tmp_arr_not_used->total_begin_in_period = (int)$obj_tmp_arr_not_used->end_num_begin_period -  (int)$obj_tmp_arr_not_used->start_num_begin_period + 1;
                        $obj_tmp_arr_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                        $obj_tmp_arr_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                        $obj_tmp_arr_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                        $obj_tmp_arr_not_used->total_used_in_period = $val->total_used_in_period;
                        $obj_tmp_arr_not_used->total_remove_in_period = $val->total_remove_in_period;
                        $obj_tmp_arr_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                        $obj_tmp_arr_not_used->total_die_in_period = $val->total_die_in_period;
                        $obj_tmp_arr_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                        $obj_tmp_arr_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                        $obj_tmp_arr_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                        $obj_tmp_arr_not_used->start_num_last_period =  $val->start_num_last_period;
                        $obj_tmp_arr_not_used->end_num_last_period =  $val->end_num_last_period;
                        $obj_tmp_arr_not_used->total_last_period = (int) $obj_tmp_arr_not_used->end_num_last_period - (int) $obj_tmp_arr_not_used->start_num_last_period + 1;
                        // $obj_tmp_arr_not_used->total_last_period = (int) $val->total_last_period;

                        //check numbe max of price
                        if ((int) $obj_tmp_arr_not_used->end_num_begin_period >= (int) $max_begin_tkt) {
                            $max_begin_tkt = $obj_tmp_arr_not_used->end_num_begin_period + 1;
                        }

                        if ((int) $obj_tmp_arr_not_used->end_num_last_period >= (int) $max_last_tkt) {
                            $max_last_tkt = $obj_tmp_arr_not_used->end_num_last_period + 1;
                        }
                    }

                    $ticket_invoice_result[] = $obj_tmp_arr_not_used;
                }
            }

            //hadle ticket allocate begin priod , not used
            if(count($tmp_begin_period_not_used_all) > 0){

                $tmp_begin_period_not_used_all_handle = [];
                foreach ($tmp_begin_period_not_used_all as $keys => $values) {
                    $values = $this->unique_multidim_array($values,'end_num_begin_period');
                    foreach ($values as $k => $val) {
                        $tmp_begin_period_not_used_all_handle[] = $val;
                    }
                }

                $tmp_begin_period_not_used_all_handle = $this->unique_multidim_array($tmp_begin_period_not_used_all_handle,'end_num_begin_period');
                foreach ($tmp_begin_period_not_used_all_handle as $k => $val) {

                    $obj_tmp_arr_not_used = new \stdClass;
                    $obj_tmp_arr_not_used->ticket_type_code = substr($value['order_code'], 0, 6);
                    $obj_tmp_arr_not_used->ticket_type_description = $value['description'];
                    $obj_tmp_arr_not_used->ticket_type_order_code = $value['order_code'];
                    $obj_tmp_arr_not_used->ticket_type_sign = $value['sign'];
                    $obj_tmp_arr_not_used->ticket_price_id = $ticket_price_id;
                    $obj_tmp_arr_not_used->price = $price;
                    $obj_tmp_arr_not_used->device_id = (int)$val->device_id;

                    //bao cao lan dau
                    if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){

                        $obj_tmp_arr_not_used->start_num_begin_period = '';
                        $obj_tmp_arr_not_used->end_num_begin_period = '';
                        $obj_tmp_arr_not_used->start_num_in_period = $val->start_num_begin_period;
                        $obj_tmp_arr_not_used->end_num_in_period = $val->end_num_begin_period;
                        $obj_tmp_arr_not_used->total_begin_in_period = (int)$obj_tmp_arr_not_used->end_num_in_period -  (int)$obj_tmp_arr_not_used->start_num_in_period + 1;
                        $obj_tmp_arr_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                        $obj_tmp_arr_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                        $obj_tmp_arr_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                        $obj_tmp_arr_not_used->total_used_in_period = $val->total_used_in_period;
                        $obj_tmp_arr_not_used->total_remove_in_period = $val->total_remove_in_period;
                        $obj_tmp_arr_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                        $obj_tmp_arr_not_used->total_die_in_period = $val->total_die_in_period;
                        $obj_tmp_arr_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                        $obj_tmp_arr_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                        $obj_tmp_arr_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                        $obj_tmp_arr_not_used->start_num_last_period =  $val->start_num_last_period;
                        $obj_tmp_arr_not_used->end_num_last_period =  $val->end_num_last_period;
                        $obj_tmp_arr_not_used->total_last_period = (int) $obj_tmp_arr_not_used->end_num_last_period - (int) $obj_tmp_arr_not_used->start_num_last_period + 1;

                        //check numbe max of price
                        if ((int) $obj_tmp_arr_not_used->end_num_in_period >= (int) $max_begin_tkt)
                            $max_begin_tkt = $obj_tmp_arr_not_used->end_num_in_period + 1;

                        if ((int) $obj_tmp_arr_not_used->end_num_last_period >= (int) $max_last_tkt)
                            $max_last_tkt = $obj_tmp_arr_not_used->end_num_last_period + 1;
                    }

                    //bao cao lan 2 tro di
                    else{

                        $obj_tmp_arr_not_used->start_num_begin_period = $val->start_num_begin_period;
                        $obj_tmp_arr_not_used->end_num_begin_period = $val->end_num_begin_period;
                        $obj_tmp_arr_not_used->start_num_in_period = '';
                        $obj_tmp_arr_not_used->end_num_in_period = '';
                        $obj_tmp_arr_not_used->total_begin_in_period = (int)$obj_tmp_arr_not_used->end_num_begin_period -  (int)$obj_tmp_arr_not_used->start_num_begin_period + 1;
                        $obj_tmp_arr_not_used->start_num_type_all_in_period = $val->start_num_type_all_in_period;
                        $obj_tmp_arr_not_used->end_num_type_all_in_period = $val->end_num_type_all_in_period;
                        $obj_tmp_arr_not_used->total_type_all_in_period = $val->total_type_all_in_period;
                        $obj_tmp_arr_not_used->total_used_in_period = $val->total_used_in_period;
                        $obj_tmp_arr_not_used->total_remove_in_period = $val->total_remove_in_period;
                        $obj_tmp_arr_not_used->str_num_remove_in_period = $val->str_num_remove_in_period;
                        $obj_tmp_arr_not_used->total_die_in_period = $val->total_die_in_period;
                        $obj_tmp_arr_not_used->str_num_die_in_period = $val->str_num_die_in_period;
                        $obj_tmp_arr_not_used->total_cancle_in_period = $val->total_cancle_in_period;
                        $obj_tmp_arr_not_used->str_num_cancle_in_period = $val->str_num_cancle_in_period;
                        $obj_tmp_arr_not_used->start_num_last_period =  $val->start_num_last_period;
                        $obj_tmp_arr_not_used->end_num_last_period =  $val->end_num_last_period;
                        $obj_tmp_arr_not_used->total_last_period = (int) $obj_tmp_arr_not_used->end_num_last_period - (int) $obj_tmp_arr_not_used->start_num_last_period + 1;

                        //check numbe max of price
                        if ((int) $obj_tmp_arr_not_used->end_num_begin_period >= (int) $max_begin_tkt) {
                            $max_begin_tkt = $obj_tmp_arr_not_used->end_num_begin_period + 1;
                        }

                        if ((int) $obj_tmp_arr_not_used->end_num_last_period >= (int) $max_last_tkt) {
                            $max_last_tkt = $obj_tmp_arr_not_used->end_num_last_period + 1;
                        }
                    }

                    $ticket_invoice_result[] = $obj_tmp_arr_not_used;
                }
            }
            //----------------++++++++++++++++++ end version 2 . handle data result

            //new record for ticket price limit
            $result_invoice_obj = new \stdClass;
            $result_invoice_obj->ticket_type_code = substr($value['order_code'], 0, 6);
            $result_invoice_obj->ticket_type_description = $value['description'];
            $result_invoice_obj->ticket_type_order_code = $value['order_code'];
            $result_invoice_obj->ticket_type_sign = $value['sign'];
            $result_invoice_obj->ticket_price_id = $ticket_price_id;
            $result_invoice_obj->price = $price;
            $result_invoice_obj->device_id = '';

            //bao cao quy dau tien
            if(date("Y-m-d 00:00:00", strtotime($from_date)) <= date('Y-m-d 00:00:00', strtotime($date_first->activated))){

                $result_invoice_obj->start_num_begin_period = '';
                $result_invoice_obj->end_num_begin_period = '';

                if ((int) $max_begin_tkt == (int) $value['ticket_prices'][0]['limit_number']) {
                    $result_invoice_obj->start_num_in_period = '';
                    $result_invoice_obj->end_num_in_period = '';
                    $result_invoice_obj->total_begin_in_period = 0;
                } else {
                    $result_invoice_obj->start_num_in_period = $max_begin_tkt;
                    $result_invoice_obj->end_num_in_period = $value['ticket_prices'][0]['limit_number'];
                    $result_invoice_obj->total_begin_in_period = (int) $value['ticket_prices'][0]['limit_number'] - (int) $max_begin_tkt + 1;
                }
            }
            //bao cao tu quy thu 2
            else{
                $result_invoice_obj->start_num_in_period = '';
                $result_invoice_obj->end_num_in_period = '';

                if ((int) $max_begin_tkt == (int) $value['ticket_prices'][0]['limit_number']) {
                    $result_invoice_obj->start_num_begin_period = '';
                    $result_invoice_obj->end_num_begin_period = '';
                    $result_invoice_obj->total_begin_in_period = 0;
                } else {
                    $result_invoice_obj->start_num_begin_period = $max_begin_tkt;
                    $result_invoice_obj->end_num_begin_period = $value['ticket_prices'][0]['limit_number'];
                    $result_invoice_obj->total_begin_in_period = (int) $value['ticket_prices'][0]['limit_number'] - (int) $max_begin_tkt + 1;
                }
            }

            $result_invoice_obj->start_num_type_all_in_period = '';
            $result_invoice_obj->end_num_type_all_in_period = '';
            $result_invoice_obj->total_type_all_in_period = 0;

            $result_invoice_obj->total_used_in_period = 0;
            $result_invoice_obj->total_remove_in_period = 0;
            $result_invoice_obj->str_num_remove_in_period = '';

            $result_invoice_obj->total_die_in_period = 0;
            $result_invoice_obj->str_num_die_in_period = '';

            $result_invoice_obj->total_cancle_in_period = 0;
            $result_invoice_obj->str_num_cancle_in_period = '';

            if ((int) $max_last_tkt == (int) $value['ticket_prices'][0]['limit_number']) {
                $result_invoice_obj->start_num_last_period = '';
                $result_invoice_obj->end_num_last_period = '';
                $result_invoice_obj->total_last_period = 0;
            } else {
                $result_invoice_obj->start_num_last_period = $max_last_tkt;
                $result_invoice_obj->end_num_last_period = $value['ticket_prices'][0]['limit_number'];
                $result_invoice_obj->total_last_period = (int) $value['ticket_prices'][0]['limit_number'] - (int) $max_last_tkt + 1;
            }
            //---------end handle map total array $begin_period_group, $released_in_period_group, $used_in_period_group ---------------//

            //push array result
            $ticket_invoice_result[] = $result_invoice_obj;
        }

        return $ticket_invoice_result;
    }

    public function viewCardMonthForGeneral($data)
    {

        $barcode = $data['barcode'] ?? null;
        $company_id = $data['company_id'];
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $result = [];
        $transactions = [];
        if (!empty($barcode)) {

            $rfid_arr =  $this->rfidcards->getRfidCardByLikeBarcode($barcode, 'rfid');

            $transactions =  DB::table('transactions')
                ->where('transactions.company_id', $company_id)
                ->where('transactions.ticket_destroy', '!=', 1)
                ->whereIn('transactions.type', ['deposit_month', 'charge_month'])
                ->whereIn('transactions.rfid', $rfid_arr)
                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->where('shifts.ended', '!=', NULL)
                ->where('shifts.ended', '>=', $from_date)
                ->where('shifts.ended', '<=', $to_date)
                ->where('shifts.shift_destroy', '!=', 1)
                ->select('transactions.*')
                ->get();
        } else {

            $transactions = DB::table('transactions')
                ->where('transactions.company_id', $company_id)
                ->where('transactions.ticket_destroy', '!=', 1)
                ->whereIn('transactions.type', ['deposit_month', 'charge_month'])
                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->where('shifts.ended', '!=', NULL)
                ->where('shifts.ended', '>=', $from_date)
                ->where('shifts.ended', '<=', $to_date)
                ->where('shifts.shift_destroy', '!=', 1)
                ->select('transactions.*')
                ->get();
        }
        if (count($transactions) > 0) {
            $total_last = ['total_last' => 0];
            $transactions = collect($transactions)->groupBy('rfid')->toArray();
            foreach ($transactions as $key => $values) {

                $rfidcard =  $this->rfidcards->getRfidCardByRfid($key);
                if (empty($rfidcard)) {
                    continue;
                }

                $membership = $this->memberships->getMembershipByRfidcardId($rfidcard->id);
                if (empty($membership)) {
                    continue;
                }

                $obj_mbs = new \stdClass;
                $obj_mbs->fullname = $membership->fullname;
                $obj_mbs->barcode = $rfidcard->barcode;
                $obj_mbs->rfid = $rfidcard->rfid;
                $obj_mbs->total_amount = 0;
                $obj_mbs->transactions_deposit = [];
                $obj_mbs->transactions_charge = [];

                foreach ($values as $vl) {

                    if (!empty($vl->type == 'deposit_month')) {

                        $obj_mbs->total_amount += $vl->amount;

                        $obj = new \stdClass;
                        $obj->activated = $vl->activated;
                        $obj->ticket_number =  $vl->ticket_number;
                        $obj->barcode =  $rfidcard->barcode;
                        $obj->amount =  $vl->amount;
                        $obj->revenue_date =  substr($vl->created_at, 0, 7);
                        $obj_mbs->transactions_deposit[] = (array) $obj;
                        $obj_mbs->expiration_date = $obj->revenue_date;
                    }

                    if (!empty($vl->type == 'charge_month')) {

                        $obj = new \stdClass;
                        $obj->activated = $vl->activated;
                        $obj->ticket_number =  $vl->ticket_number;
                        $obj->barcode =  $rfidcard->barcode;
                        $obj_mbs->transactions_charge[] = (array) $obj;
                    }
                }
                $total_last['total_last'] += $obj_mbs->total_amount;
                $result[] = (array) $obj_mbs;
            }
            $result[] = $total_last;
        }
        return $result;
    }

    public function viewCardMonthForRevenue($data)
    {

        $company_id = $data['company_id'];
        $route_id = (int)$data['route_id'] ?? 0;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $result = [];

        if ($route_id == 0) {

            $transactions = DB::table('transactions')
                ->where([
                    ['transactions.company_id', $company_id],
                    ['transactions.type', 'charge_month'],
                    ['transactions.ticket_destroy', '!=', 1]
                ])->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->where([
                    ['shifts.ended', '!=', NULL],
                    ['shifts.shift_destroy', '!=', 1],
                    ['shifts.ended', '>=', $from_date],
                    ['shifts.ended', '<=', $to_date]
                ])->select('shifts.route_id', 'transactions.*')
                ->get();

            if (count($transactions) > 0) {

                $obj_mbs_route_all = new \stdClass;
                $obj_mbs_route_all->route_number = 'all';
                $obj_mbs_route_all->price = 0;
                $obj_mbs_route_all->count_number_only = 0;
                $obj_mbs_route_all->count_number_all = 0;
                $obj_mbs_route_all->revenue = 0;

                $gr_tran_routes = collect($transactions)->groupBy('route_id')->toArray();

                foreach ($gr_tran_routes as $key_route => $values_route) {

                    $route = $this->routes->getRouteById((int) $key_route);

                    $obj_mbs_route = new \stdClass;
                    $obj_mbs_route->route_number = $route ? $route->number : '';
                    $obj_mbs_route->count_number_only = 0;
                    $obj_mbs_route->count_number_all = 0;
                    $obj_mbs_route->revenue = 0;
                    $obj_mbs_route->price = 0;

                    $gr_tran_rfid = collect($values_route)->groupBy('rfid')->toArray();
                    foreach ($gr_tran_rfid as $key_rfid => $values_rfid) {

                        $rfidcard =  $this->rfidcards->getRfidCardByRfid($key_rfid);
                        if (empty($rfidcard))  continue;

                        $membership = $this->memberships->getMembershipByRfidcardId($rfidcard->id);
                        if (empty($membership)) continue;

                        $gr_tran_tkt_nums = collect($values_rfid)->groupBy('ticket_number')->toArray();

                        foreach ($gr_tran_tkt_nums as $key => $vls) {

                            //count tkt number by options
                            $count_number_all = DB::table('transactions')
                                ->where('transactions.type', 'charge_month')
                                ->where('transactions.rfid', $key_rfid)
                                ->where('ticket_destroy', '!=', 1)
                                ->where('transactions.ticket_number', $key)
                                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                ->where([
                                    ['shifts.ended', '!=', NULL],
                                    ['shifts.shift_destroy', '!=', 1],
                                    ['shifts.ended', '>=', $from_date],
                                    ['shifts.ended', '<=', $to_date]
                                ])
                                ->count();

                            $obj_mbs_route->count_number_only += count($vls);
                            $obj_mbs_route->count_number_all += $count_number_all;
                            $obj_mbs_route->revenue += $vls[0]->amount / $count_number_all * count($vls);

                            //count tkt number by options
                            $price_tkt_number = Transaction::where('ticket_number', $key)
                                        ->where('transactions.rfid', $key_rfid)
                                        ->where('type', 'deposit_month')
                                        ->where('ticket_destroy', '!=', 1)
                                        ->where('ticket_price_id', $vls[0]->ticket_price_id)
                                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                        ->where('shifts.route_id', (int)$key_route)
                                        ->first();
                            $obj_mbs_route->price += ($price_tkt_number->amount ?? 0);
                        }
                    }

                    $result[] = $obj_mbs_route;

                    $obj_mbs_route_all->count_number_only += $obj_mbs_route->count_number_only;
                    $obj_mbs_route_all->count_number_all += $obj_mbs_route->count_number_all;
                    $obj_mbs_route_all->revenue += $obj_mbs_route->revenue;
                    $obj_mbs_route_all->price += $obj_mbs_route->price;
                }
                $obj_mbs_route_all->revenue = round($obj_mbs_route_all->revenue, 2);
                $result[] = $obj_mbs_route_all;
            }
        }

        if ($route_id > 0) {


            $transactions = DB::table('transactions')
                ->where([
                    ['transactions.company_id', $company_id],
                    ['transactions.type', 'charge_month'],
                    ['transactions.ticket_destroy', '!=', 1]
                ])->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->where([
                    ['shifts.ended', '!=', NULL],
                    ['shifts.shift_destroy', '!=', 1],
                    ['shifts.route_id', $route_id],
                    ['shifts.ended', '>=', $from_date],
                    ['shifts.ended', '<=', $to_date]
                ])->select('shifts.route_id', 'transactions.*')
                ->get();

            if (count($transactions) > 0) {

                $gr_tran_routes = collect($transactions)->groupBy('route_id')->toArray();

                foreach ($gr_tran_routes as $key_route => $values_route) {

                    $obj_mbs_route = new \stdClass;
                    $obj_mbs_route->route_number = '';
                    $obj_mbs_route->count_number_only = 0;
                    $obj_mbs_route->count_number_all = 0;
                    $obj_mbs_route->price = 0;
                    $obj_mbs_route->revenue = 0;

                    $gr_tran_rfid = collect($values_route)->groupBy('rfid')->toArray();
                    foreach ($gr_tran_rfid as $key_rfid => $values_rfid) {

                        $rfidcard =  $this->rfidcards->getRfidCardByRfid($key_rfid);
                        if (empty($rfidcard)) {
                            continue;
                        }

                        $membership = $this->memberships->getMembershipByRfidcardId($rfidcard->id);
                        if (empty($membership)) {
                            continue;
                        }

                        $gr_tran_tkt_nums = collect($values_rfid)->groupBy('ticket_number')->toArray();
                        foreach ($gr_tran_tkt_nums as $key => $vls) {

                            $obj_mbs = new \stdClass;
                            $obj_mbs->fullname = $membership->fullname;
                            $obj_mbs->barcode = $rfidcard->barcode;
                            $obj_mbs->ticket_number = $key;
                            $route = $this->routes->getRouteById((int) $key_route);
                            $obj_mbs->route_number = $route ? $route->number : '';

                            $price_tkt_number = Transaction::where('ticket_number',$key)
                                                    ->where('transactions.rfid', $key_rfid)
                                                    ->where('ticket_destroy','!=', 1)
                                                    ->where('type', 'deposit_month')
                                                    ->where('ticket_price_id', $vls[0]->ticket_price_id)
                                                    ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                                    ->where('shifts.route_id', (int)$key_route)
                                                    // ->where('created_at','>=', substr($from_date,0,7).'-01 00:00:00')
                                                    // ->where('created_at','<=', $to_date)
                                                    ->first();

                            $obj_mbs->price = ($price_tkt_number->amount  ?? 0);
                            $obj_mbs->count_number_only = 0;
                            $obj_mbs->revenue = 0;

                            foreach ($vls as  $vl) {

                                $obj_mbs->count_number_only += 1;
                                $obj_mbs->station_data = 'Tất cả các tuyến';

                                if (!empty($vl->station_data)) {
                                    $stations = json_decode($vl->station_data);
                                    $obj_mbs->station_data = $stations[0] . ' - ' . $stations[1];
                                }
                            }

                            //count tkt number by options
                            $obj_mbs->count_number_all = DB::table('transactions')
                                                    ->where('transactions.type','charge_month')
                                                    ->where('transactions.rfid', $key_rfid)
                                                    ->where('ticket_destroy', '!=', 1)
                                                    ->where('transactions.ticket_number', $key)
                                                    ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                                    ->where([
                                                        ['shifts.ended', '!=', NULL],
                                                        ['shifts.shift_destroy', '!=', 1],
                                                        ['shifts.ended', '>=', $from_date],
                                                        ['shifts.ended', '<=', $to_date]
                                                    ])
                                                    ->count();

                            $obj_mbs->revenue = $vls[0]->amount / $obj_mbs->count_number_all * $obj_mbs->count_number_only;

                            $result[] = $obj_mbs;

                            $obj_mbs_route->route_number = $obj_mbs->route_number;
                            $obj_mbs_route->count_number_only += $obj_mbs->count_number_only;
                            $obj_mbs_route->count_number_all += $obj_mbs->count_number_all;
                            $obj_mbs_route->price += $obj_mbs->price;
                            $obj_mbs_route->revenue += $obj_mbs->revenue;
                        }
                    }
                    $obj_mbs_route->revenue = round($obj_mbs_route->revenue, 2);
                    $result[] = $obj_mbs_route;
                }
            }
        }

        return $result;
    }

    public function viewCardMonthByStaff($data)
    {

        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'] ?? 0;
        $user_id = (int) $data['user_id'] ?? 0;
        $barcode = $data['barcode'] ?? null;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        if ($user_id > 0) {

            $data_result = [];

            $user = $this->users->getUserById($user_id);
            if ($user) {

                $role_name = '';

                $transactions = [];

                $wheres_transaction = [
                    ['transactions.company_id', $company_id],
                    ['transactions.type', 'charge_month'],
                    ['transactions.ticket_destroy', '!=', 1]
                ];

                $wheres_shift = [
                    ['shifts.ended', '!=', NULL],
                    ['shifts.shift_destroy', '!=', 1],
                    ['shifts.ended', '>=', $from_date],
                    ['shifts.ended', '<=', $to_date],
                    ['shifts.ended', '!=', NULL]
                ];

                if ($user->role->name == 'driver') {
                    $role_name = 'Tài xế';
                    $wheres_shift[] = ['shifts.user_id', $user_id];
                }

                if ($user->role->name == 'subdriver') {
                    $role_name = 'Phụ xe';
                    $wheres_shift[] = ['shifts.subdriver_id', $user_id];
                }

                if ($route_id > 0) $wheres_shift[] = ['shifts.route_id', $route_id];

                if (!empty($barcode)) {

                    $rfid_arr = $this->rfidcards->getRfidCardByLikeBarcode($barcode,'rfid');
                    if (count($rfid_arr))
                        $transactions = DB::table('transactions')
                            ->where('transactions.rfid', $rfid_arr)
                            ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                            ->join('routes', 'routes.id', '=', 'shifts.route_id')
                            ->where($wheres_transaction)
                            ->where($wheres_shift)
                            ->select(
                                'transactions.*',
                                'shifts.route_id',
                                'shifts.user_id',
                                'shifts.subdriver_id',
                                'routes.name as route_name',
                                'routes.number as route_number'
                            )
                            ->orderBy('transactions.activated')
                            ->get();
                } else {
                    $transactions = DB::table('transactions')
                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                        ->join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->where($wheres_transaction)
                        ->where($wheres_shift)
                        ->select(
                            'transactions.*',
                            'shifts.route_id',
                            'shifts.user_id',
                            'shifts.subdriver_id',
                            'routes.name as route_name',
                            'routes.number as route_number'
                        )
                        ->orderBy('transactions.activated')
                        ->get();
                }

                if (count($transactions) > 0) {

                    //total driver
                    $obj_total_only = new \stdClass;
                    $obj_total_only->count_times = 0;
                    $obj_total_only->total_revenue = 0;
                    $obj_total_only->role_name = 'only';

                    $transactions = collect($transactions)->groupBy('route_id')->toArray();

                    foreach ($transactions as $tran_key => $tran_values) {

                        //group data by rfid
                        $rfid_values = collect($tran_values)->groupBy('rfid')->toArray();

                        foreach ($rfid_values as $rfid_k => $rfid_vls) {

                            //get info rfidcard
                            $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_k);
                            // $mbs_price = $this->memberships->getMembershipByRfidcardId($rfidcard->id);

                            $obj = new \stdClass;
                            $obj->fullname = $user->fullname ?? '';
                            $obj->role_name = $role_name ?? '';
                            $obj->role_id = $user->role->id ?? 0;

                            $obj->route_name = $rfid_vls[0]->route_name;
                            $obj->route_number = $rfid_vls[0]->route_number;
                            $obj->route_id = $rfid_vls[0]->route_id;

                            $obj->barcode = $rfidcard ? $rfidcard->barcode : '';

                            $obj->count_times = 0;
                            $obj->total_revenue = 0;

                            $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();

                            foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                $obj->count_times += count($v_tkt_num);

                                $count_route_all = DB::table('transactions')
                                    ->where($wheres_transaction)
                                    ->where('transactions.rfid', $rfid_k)
                                    ->where('transactions.ticket_number', (string)$k_tkt_num)
                                    ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                    ->where([
                                        ['shifts.ended', '!=', NULL],
                                        ['shifts.shift_destroy', '!=', 1],
                                        ['shifts.ended', '>=', $from_date],
                                        ['shifts.ended', '<=', $to_date]
                                    ])
                                    ->count();

                                $obj->total_revenue +=  $v_tkt_num[0]->amount / $count_route_all * count($v_tkt_num);
                            }

                            $data_result[] = $obj;

                            $obj_total_only->count_times += $obj->count_times;
                            $obj_total_only->total_revenue += $obj->total_revenue;
                        }
                    }
                    $obj_total_only->total_revenue = round($obj_total_only->total_revenue, 2);
                }

                return $data_result;
            }
        }

        if ($user_id == 0) {

            $data_result = [];

            $transactions = [];

            $wheres_transaction = [
                ['transactions.company_id', $company_id],
                ['transactions.type', 'charge_month'],
                ['transactions.ticket_destroy', '!=', 1]
            ];

            $wheres_shift = [
                ['shifts.ended', '!=', NULL],
                ['shifts.shift_destroy', '!=', 1],
                ['shifts.ended', '>=', $from_date],
                ['shifts.ended', '<=', $to_date]
            ];

            if ($route_id > 0) $wheres_shift[] = ['shifts.route_id', $route_id];

            if (!empty($barcode)) {

                $rfid_arr = $this->rfidcards->getRfidCardByLikeBarcode($barcode, 'rfid');

                if (count($rfid_arr))
                    $transactions = DB::table('transactions')
                        ->where($wheres_transaction)
                        ->where('rfid', $rfid_arr)
                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                        ->where($wheres_shift)
                        ->join('routes', 'routes.id', '=', 'shifts.route_id')
                        ->orderBy('transactions.activated')
                        ->select(
                                'shifts.route_id',
                                'shifts.user_id',
                                'shifts.subdriver_id',
                                'routes.name as route_name',
                                'routes.number as route_number',
                                'transactions.type',
                                'transactions.amount',
                                'transactions.ticket_number',
                                'transactions.rfid',
                                'transactions.ticket_price_id'
                        )
                        ->get();
            } else {
                $transactions = DB::table('transactions')
                    ->where($wheres_transaction)
                    ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                    ->where($wheres_shift)
                    ->join('routes', 'routes.id', '=', 'shifts.route_id')
                    ->orderBy('transactions.activated')
                    ->select(
                            'shifts.route_id',
                            'shifts.user_id',
                            'shifts.subdriver_id',
                            'routes.name as route_name',
                            'routes.number as route_number',
                            'transactions.type',
                            'transactions.amount',
                            'transactions.ticket_number',
                            'transactions.rfid',
                            'transactions.ticket_price_id'
                    )
                    ->get();
            }

            if (count($transactions) > 0) {

                //total driver
                $obj_total_driver = new \stdClass;
                $obj_total_driver->count_times = 0;
                $obj_total_driver->total_revenue = 0;
                $obj_total_driver->role_name = 'Tổng cộng toàn bộ cho Tài xế';

                //total subdriver
                $obj_total_subdriver = new \stdClass;
                $obj_total_subdriver->count_times = 0;
                $obj_total_subdriver->total_revenue = 0;
                $obj_total_subdriver->role_name = 'Tổng cộng toàn bộ cho Phụ xe';

                //total subdriver
                $obj_total_all = new \stdClass;
                $obj_total_all->count_times = 0;
                $obj_total_all->total_revenue = 0;
                $obj_total_all->role_name = 'all';

                //group data by route_id
                $transactions = collect($transactions)->groupBy('route_id')->toArray();

                foreach ($transactions as $tran_key => $tran_values) {

                    //handle total role driver for route
                    $role_obj = new \stdClass;
                    $role_obj->role_name = '';
                    $role_obj->count_times = 0;
                    $role_obj->total_revenue = 0;

                    //group data by user_id
                    $user_values =  collect($tran_values)->groupBy('user_id')->toArray();

                    foreach ($user_values as $user_key => $user_vls) {

                        if(!empty($user_key)){
                            //group data by rfid
                            $rfid_values = collect($user_vls)->groupBy('rfid')->toArray();

                            foreach ($rfid_values as $rfid_k => $rfid_vls) {

                                //get info rfidcard
                                $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_k);
                                // $mbs_price = $this->memberships->getMembershipByRfidcardId($rfidcard->id);

                                //get info user
                                $user = (object)$this->users->getUserById((int)$user_key);

                                $obj = new \stdClass;
                                $obj->fullname = $user ? $user->fullname : '';
                                $obj->role_name = "Tài xế";
                                $obj->role_id = $user ? $user->role->id : 0;

                                $obj->route_name = $rfid_vls[0]->route_name;
                                $obj->route_number = $rfid_vls[0]->route_number;
                                $obj->route_id = $rfid_vls[0]->route_id;

                                $obj->barcode = $rfidcard ? $rfidcard->barcode : '';

                                $obj->count_times = 0;
                                $obj->total_revenue = 0;

                                $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();

                                foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                    $obj->count_times += count($v_tkt_num);

                                    $count_route_all = DB::table('transactions')
                                        ->where($wheres_transaction)
                                        ->where('transactions.rfid', $rfid_k)
                                        ->where('transactions.ticket_number', (string)$k_tkt_num)
                                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                        ->where([
                                            ['shifts.ended', '!=', NULL],
                                            ['shifts.shift_destroy', '!=', 1],
                                            ['shifts.ended', '>=', $from_date],
                                            ['shifts.ended', '<=', $to_date]
                                        ])
                                        ->count();

                                    $obj->total_revenue += $v_tkt_num[0]->amount / $count_route_all * count($v_tkt_num);
                                }

                                $data_result[] = $obj;

                                //handle total by role for route
                                $role_obj->role_name = $obj->role_name;
                                $role_obj->count_times += $obj->count_times;
                                $role_obj->total_revenue += $obj->total_revenue;

                                //hadle total all of driver
                                $obj_total_driver->count_times += $obj->count_times;
                                $obj_total_driver->total_revenue += $obj->total_revenue;
                                //hadle total all by driver
                                $obj_total_all->count_times += $obj->count_times;
                                $obj_total_all->total_revenue += $obj->total_revenue;
                            }
                        }else{
                            continue;
                        }
                    }
                    $role_obj->total_revenue = round($role_obj->total_revenue, 2);
                    $data_result[] = $role_obj;

                    //new object total role subdriver for route
                    $role_obj = new \stdClass;
                    $role_obj->role_name = '';
                    $role_obj->count_times = 0;
                    $role_obj->total_revenue = 0;

                    //group data by subdriver_id
                    $subdriver_values =  collect($tran_values)->groupBy('subdriver_id')->toArray();
                    foreach ($subdriver_values as $user_key => $user_vls) {

                        if(!empty($user_key)){
                            //group data by rfid
                            $rfid_values = collect($user_vls)->groupBy('rfid')->toArray();

                            foreach ($rfid_values as $rfid_k => $rfid_vls) {

                                //get info rfidcard
                                $rfidcard = $this->rfidcards->getRfidCardByRfid($rfid_k);
                                // $mbs_price = $this->memberships->getMembershipByRfidcardId($rfidcard->id);

                                //get info user
                                $user = (object)$this->users->getUserById((int)$user_key);

                                $obj = new \stdClass;
                                $obj->fullname = $user ? $user->fullname : '';
                                $obj->role_name = "Phụ xe";
                                $obj->role_id = $user ? $user->role->id : 0;

                                $obj->route_name = $rfid_vls[0]->route_name;
                                $obj->route_number = $rfid_vls[0]->route_number;
                                $obj->route_id = $rfid_vls[0]->route_id;

                                $obj->barcode = $rfidcard ? $rfidcard->barcode : '';

                                $obj->count_times = 0;
                                $obj->total_revenue = 0;

                                $group_tkt_number = collect($rfid_vls)->groupBy('ticket_number')->toArray();

                                foreach ($group_tkt_number as $k_tkt_num => $v_tkt_num) {

                                    $obj->count_times += count($v_tkt_num);

                                    $count_route_all = DB::table('transactions')
                                        ->where($wheres_transaction)
                                        ->where('transactions.rfid', $rfid_k)
                                        ->where('transactions.ticket_number', $k_tkt_num)
                                        ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                                        ->where([
                                            ['shifts.ended', '!=', NULL],
                                            ['shifts.shift_destroy', '!=', 1],
                                            ['shifts.ended', '>=', $from_date],
                                            ['shifts.ended', '<=', $to_date]
                                        ])
                                        ->count();

                                    $obj->total_revenue += $v_tkt_num[0]->amount / $count_route_all * count($v_tkt_num);
                                }

                                $data_result[] = $obj;

                                //handle total by role for subdriver
                                $role_obj->role_name = $obj->role_name;
                                $role_obj->count_times += $obj->count_times;
                                $role_obj->total_revenue += $obj->total_revenue;

                                //hadle total all for subdriver
                                $obj_total_subdriver->count_times += $obj->count_times;
                                $obj_total_subdriver->total_revenue += $obj->total_revenue;
                            }
                        }else{
                            continue;
                        }
                    }
                    $role_obj->total_revenue = round($role_obj->total_revenue, 2);
                    $data_result[] = $role_obj;
                }

                $obj_total_driver->total_revenue = round($obj_total_driver->total_revenue, 2);
                $obj_total_subdriver->total_revenue = round($obj_total_subdriver->total_revenue, 2);
                $obj_total_all->total_revenue = round($obj_total_all->total_revenue, 2);

                $data_result[] = $obj_total_subdriver;
                $data_result[] = $obj_total_driver;
                $data_result[] = $obj_total_all;
            }

            return $data_result;
        }
    }

    public function viewCardMonthByGroupBusStation($data)
    {

        $company_id = $data['company_id'];
        $user_id = (int) $data['user_id'] ?? 0;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $where = [
            ['shifts.ended', '!=', NULL],
            ['shifts.shift_destroy', '!=', 1],
            ['shifts.ended', '>=', $from_date],
            ['shifts.ended', '<=', $to_date],
            ['transactions.type', '=', 'deposit_month'],
            ['transactions.company_id', '=', $company_id],
            ['transactions.ticket_destroy', '!=', 1],
            ['transactions.rfid', '!=', NULL]
        ];


        if($user_id > 0) $where[] = ['transactions.user_id' ,'=', $user_id];

        $transactions = Transaction::join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                        ->where($where)
                        ->join('rfidcards', 'rfidcards.rfid', '=', 'transactions.rfid')
                        ->join('memberships', 'memberships.id', '=', 'rfidcards.target_id')
                        ->join('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                        ->join('ticket_types', 'ticket_types.id', '=', 'ticket_prices.ticket_type_id')
                        ->leftJoin('group_bus_stations', 'group_bus_stations.id', '=', 'memberships.gr_bus_station_id')
                        ->select(
                            'transactions.ticket_number',
                            'transactions.amount',
                            'transactions.activated',
                            'group_bus_stations.name as group_bus_station_name',
                            'group_bus_stations.id as group_bus_station_id' ,
                            'ticket_types.order_code as order_code',
                            'memberships.station_data'
                        )
                        ->orderBy('group_bus_stations.id')
                        ->orderBy('transactions.activated')
                        ->get();

        $transactions = collect($transactions)->groupBy('group_bus_station_id')->toArray();

        $results = [];

        if(count($transactions) > 0){

            $obj_total = new \stdClass;
            $obj_total->group_bus_station_id = 0;
            $obj_total->count_ticket = 0;
            $obj_total->total_amount = 0;

            foreach ($transactions as $key => $values) {

                $obj = new \stdClass;
                $obj->group_bus_station_id = -1;
                $obj->group_bus_station_name = '';
                $obj->count_ticket = 0;
                $obj->total_amount = 0;

                foreach ($values as $v) {

                    if($v['group_bus_station_id'] == null){
                        $tmp_station =  collect(json_decode($v['station_data'], true))->groupBy('group_bus_station_id')->toArray();
                        if(count($tmp_station) > 0){
                            foreach($tmp_station as $tmp_v){
                                $v['group_bus_station_name'] = $tmp_v[0]['name'].' - '.$tmp_v[1]['name'];
                            break;
                            }
                        }
                        $v['group_bus_station_id'] = -2;
                    }
                    $results[] = $v;

                    $obj->count_ticket += 1;
                    $obj->group_bus_station_name = $v['group_bus_station_name'];
                    $obj->total_amount += $v['amount'];

                    $obj_total->count_ticket += 1;
                    $obj_total->total_amount += $v['amount'];
                }

                $results[] = $obj;
            }

            $results[] = $obj_total;
        }
        return $results;
    }

    public function viewTripTimes($data)
    {

        $company_id = (int) $data['company_id'];
        $route_id = (int) $data['route_id'];
        $user_id = (int) $data['user_id'];
        $type_opt = (int) $data['type_opt'];
        $position = $data['position'];

        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $result = [];
        //$type_opt == 0 : all routes
        if ($type_opt == 0) {

            $where = [];
            if ($route_id == 0) {
                $where = [
                    ['routes.company_id', '=', $company_id],
                    ['shifts.ended', '>=', $from_date],
                    ['shifts.ended', '<=', $to_date],
                    ['shift_destroy', '!=', 1]
                ];
            } else {
                $where = [
                    ['routes.company_id', '=', $company_id],
                    ['shifts.ended', '>=', $from_date],
                    ['shifts.ended', '<=', $to_date],
                    ['shift_destroy', '!=', 1],
                    ['shifts.route_id', '=', $route_id]
                ];
            }

            $shifts = Shift::join('routes', 'shifts.route_id', '=', 'routes.id')
                ->where($where)
                ->selectRaw('routes.number as route_number, routes.id, count(routes.id) as count_route_number')
                ->groupBy('routes.number', 'routes.id')
                ->orderByRaw('cast(routes.number as unsigned)')
                ->get();

            $total_route_number = collect($shifts)->sum('count_route_number');
            $shifts[] = ['total_route_number' => $total_route_number];
            $result = $shifts;
            return $result;
        }
        //$type_opt == 1 : only route
        if ($type_opt == 1) {

            $where = [
                ['routes.company_id', '=', $company_id],
                ['shifts.ended', '>=', $from_date],
                ['shifts.ended', '<=', $to_date],
                ['shift_destroy', '!=', 1]
            ];

            if ($route_id > 0) $where[] = ['shifts.route_id', '=', $route_id];

            if ($position == "driver") {

                if ($user_id > 0) $where[] = ['shifts.user_id', '=', $user_id];

                $data_driver = [];
                $total_all = [
                    'position_name' => "all",
                    'count_route_number' => 0
                ];
                $shifts = Shift::join('routes', 'shifts.route_id', '=', 'routes.id')
                    ->join('vehicles','shifts.vehicle_id','=','vehicles.id')
                    ->where($where)
                    ->selectRaw('shifts.route_id as route_id,
                             routes.number as route_number,
                             routes.name as route_name,
                             shifts.user_id,
                             shifts.subdriver_id,
                             shifts.ended,
                             shifts.started,
                             vehicles.license_plates,
                             shifts.vehicle_id')
                    ->orderByRaw('cast(routes.number as unsigned)')
                    ->get();

                if (count($shifts) > 0) {
                    $shifts = collect($shifts)->groupBy('route_id');
                    foreach ($shifts as $key => $shift) {

                        $total_count_route_number = [
                            'count_route_number' => 0,
                            'position_name' => "Tài xế"
                        ];

                        $shift = collect($shift)->groupBy('user_id');
                        foreach ($shift as $vls) {

                            $driver_tmp = [
                                'route_name' => $vls[0]->route_name,
                                'route_id' => $vls[0]->route_id,
                                'fullname' => "",
                                'position_name' => "Tài xế",
                                'role_id' => 0,
                                'route_number' => $vls[0]->route_number,
                                'count_route_number' => count($vls),
                                'data_shift' => []
                            ];

                            $total_count_route_number['count_route_number'] += $driver_tmp['count_route_number'];

                            foreach ($vls as $vl) {

                                if (round($vl->user_id) > 0) {
                                    $driver = $this->users->getUserByKey('id', $vl->user_id, $company_id);
                                    $driver_tmp['fullname'] = $driver['fullname'];
                                    $driver_tmp['role_id'] = $driver['role_id'];
                                }
                                $driver_tmp['data_shift'][] = [
                                    'date_details' => $vl->started . ' <=> ' . $vl->ended,
                                    'vehicle_details' => $vl->license_plates,
                                    'route_name_details' => $vl->route_name,
                                    'route_number_details' => $vl->route_number
                                ];
                            }
                            $data_driver[] = $driver_tmp;
                        }
                        $data_driver[] = $total_count_route_number;
                        $total_all['count_route_number'] += $total_count_route_number['count_route_number'];
                    }
                    $data_driver[] = $total_all;
                }
                return $data_driver;

            } elseif ($position == "subdriver") {
                if ($user_id > 0) $where[] = ['shifts.subdriver_id', '=', $user_id];

                $data_subdriver = [];
                $total_all = [
                    'position_name' => "all",
                    'count_route_number' => 0
                ];
                $shifts = Shift::join('routes', 'shifts.route_id', '=', 'routes.id')
                    ->join('vehicles','shifts.vehicle_id','=','vehicles.id')
                    ->where($where)
                    ->selectRaw('shifts.route_id as route_id,
                                 routes.number as route_number,
                                 routes.name as route_name,
                                 shifts.user_id,
                                 shifts.subdriver_id,
                                 shifts.ended,
                                 shifts.started,
                                 vehicles.license_plates,
                                 shifts.vehicle_id')
                    ->orderByRaw('cast(routes.number as unsigned)')
                    ->get();

                if (count($shifts) > 0) {
                    $shifts = collect($shifts)->groupBy('route_id');
                    foreach ($shifts as $key => $shift) {

                        $total_count_route_number = [
                            'count_route_number' => 0,
                            'position_name' => "Phụ xe"
                        ];

                        $shift = collect($shift)->groupBy('subdriver_id');
                        foreach ($shift as $vls) {

                            $subdriver_tmp = [
                                'route_name' => $vls[0]->route_name,
                                'route_id' => $vls[0]->route_id,
                                'fullname' => "",
                                'position_name' => "Phụ xe",
                                'role_id' => 0,
                                'route_number' => $vls[0]->route_number,
                                'count_route_number' => count($vls),
                                'data_shift' => []
                            ];

                            $total_count_route_number['count_route_number'] += $subdriver_tmp['count_route_number'];

                            foreach ($vls as $vl) {

                                if (round($vl->subdriver_id) > 0) {
                                    $subdriver = $this->users->getUserByKey('id', $vl->subdriver_id, $company_id);
                                    $subdriver_tmp['fullname'] = $subdriver['fullname'];
                                    $subdriver_tmp['role_id'] = $subdriver['role_id'];
                                }
                                $subdriver_tmp['data_shift'][] = [
                                    'date_details' => $vl->started . ' <=> ' . $vl->ended,
                                    'vehicle_details' => $vl->license_plates,
                                    'route_name_details' => $vl->route_name,
                                    'route_number_details' => $vl->route_number
                                ];
                            }
                            $data_subdriver[] = $subdriver_tmp;
                        }
                        $data_subdriver[] = $total_count_route_number;
                        $total_all['count_route_number'] += $total_count_route_number['count_route_number'];
                    }
                    $data_subdriver[] = $total_all;
                }
                return $data_subdriver;
            } else {
                $result = [];
                $shifts = Shift::join('routes', 'shifts.route_id', '=', 'routes.id')
                    ->join('vehicles','shifts.vehicle_id','=','vehicles.id')
                    ->where($where)
                    ->selectRaw('shifts.route_id as route_id,
                         routes.number as route_number,
                         routes.name as route_name,
                         shifts.user_id,
                         shifts.subdriver_id,
                         shifts.ended,
                         shifts.started,
                         shifts.vehicle_id
                         ')
                    ->orderByRaw('cast(routes.number as unsigned)')
                    ->get();

                if (count($shifts) > 0) {
                    $data_tmp = [];

                    $total_all = [
                        'position_name' => "all",
                        'count_route_number' => 0
                    ];
                    $total_driver = [
                        'position_name'=> "Tổng cộng toàn bộ Tài xế",
                        'count_route_number'=> 0
                    ];
                    $total_subdriver = [
                        'position_name'=> "Tổng cộng toàn bộ Phụ xe",
                        'count_route_number'=> 0
                    ];

                    foreach ($shifts as $shift) {

                        if (round($shift->user_id) > 0) {
                            $tmp = [
                                'route_name' => $shift->route_name,
                                'route_id' => $shift->route_id,
                                'route_number' => $shift->route_number,
                                'fullname' => "",
                                'position_name' => "Tài xế",
                                'role_id' => 0,
                                'date_details' => $shift->started . ' <=> ' . $shift->ended,
                                'vehicle_details' => $shift->license_plates,
                                'route_name_details' => $shift->route_name,
                                'route_number_details' => $shift->route_number,
                            ];

                            $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                            $tmp['fullname'] = $driver['fullname'];
                            $tmp['role_name'] = $driver['role']->name;
                            $tmp['user_id'] = $shift->user_id;
                            $tmp['role_id'] = $driver['role_id'];
                            $data_tmp[] = $tmp;
                        }

                        if (round($shift->subdriver_id) > 0) {
                            $tmp = [
                                'route_name' => $shift->route_name,
                                'route_id' => $shift->route_id,
                                'route_number' => $shift->route_number,
                                'fullname' => "",
                                'position_name' => "Phụ xe",
                                'role_id' => 0,
                                'date_details' => $shift->started . ' <=> ' . $shift->ended,
                                'vehicle_details' => $shift->license_plates,
                                'route_name_details' => $shift->route_name,
                                'route_number_details' => $shift->route_number,
                            ];

                            $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                            $tmp['fullname'] = $subdriver['fullname'];
                            $tmp['role_name'] = $subdriver['role']->name;
                            $tmp['subdriver_id'] = $shift->subdriver_id;
                            $tmp['role_id'] = $subdriver['role_id'];
                            $data_tmp[] = $tmp;
                        }
                    }

                    $data_routes = collect($data_tmp)->groupBy('route_id')->toArray();
                    foreach ($data_routes as $key_route => $value_route) {
                        $data_roles = collect($value_route)->groupBy('role_name')->toArray();
                        foreach ($data_roles as $key_role => $value_role) {

                            if ($key_role == "driver") {
                                $value_role = collect($value_role)->groupBy('user_id')->toArray();
                            }elseif ($key_role == "subdriver") {
                                $value_role = collect($value_role)->groupBy('subdriver_id')->toArray();
                            }
                            $total_count_route_number = [
                                'count_route_number' => 0,
                                'position_name' => ''
                            ];
                            foreach ($value_role as $k => $vls) {
                                $result_tmp = [
                                    'fullname' => $vls[0]['fullname'],
                                    'position_name' => $vls[0]['position_name'],
                                    'role_id' =>  $vls[0]['role_id'],
                                    'route_number' => $vls[0]['route_number'],
                                    'route_name' => $vls[0]['route_name'],
                                    'route_id' => $vls[0]['route_id'],
                                    'count_route_number' => count($vls),
                                    'data_shift' => $vls
                                ];

                                // foreach($vls as $v) {

                                //     $result_tmp['data_shift'][] = [
                                //         'date_details' => $v['date_details'],
                                //         'vehicle_details' => $v['vehicle_details'],
                                //         'route_name_details' => $v['route_name'],
                                //         'route_number_details' => $v['route_number'],
                                //     ];
                                // }
                                $total_count_route_number['position_name'] = $result_tmp['position_name'];
                                $total_count_route_number['count_route_number'] += $result_tmp['count_route_number'];
                                $result[] = $result_tmp;
                            }
                            $result[] = $total_count_route_number;
                        }
                        $total_all['count_route_number'] += $total_count_route_number['count_route_number'];
                        $total_driver['count_route_number'] += $total_count_route_number['count_route_number'];
                        $total_subdriver['count_route_number'] += $total_count_route_number['count_route_number'];
                    }
                    $result[] = $total_all;
                    $result[] = $total_driver;
                    $result[] = $total_subdriver;
                }
                return $result;
            }
        }
    }

    public function viewTimeKeeping($data)
    {

        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $position = $data['position'] ?? 'all';
        $company_id = (int) $data['company_id'];
        $key_role = '';

        $optRoles = [];
        if ($position == 'all') {
            $key_role = 'role_all';
            $optRoles = $this->roles->getIdRolePluckByName(['driver', 'subdriver']);
        } else {
            $key_role = 'role_only';
            $optRoles = $this->roles->getIdRolePluckByName([$position]);
        }

        $result_arr = [];
        $number_day_in_month = cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($from_date)), date('Y',strtotime($to_date)));
        $users = $this->users->getUsersByRoleAndCompany($optRoles, $company_id);

        if (count($users) > 0) {

            foreach ($users as $user) {

                $wheres = [
                    [DB::raw('MONTH(ended)'), "=" , date('m',strtotime($from_date))],
                    [DB::raw('YEAR(ended)') , "="  , date('Y',strtotime($to_date)) ],
                    ['shift_destroy', '!=', 1]
                ];

                if ($user->role->name == 'subdriver') {
                    $position_name = 'Phụ xe';
                    $wheres[] = ["subdriver_id", "=", $user->id];
                }

                if ($user->role->name == 'driver') {
                    $position_name = 'Lái xe';
                    $wheres[] = ["user_id", "=", $user->id];
                }

                $tmp = $this->shifts->getShiftsByOptionAndCompanyId($wheres, $company_id)->toArray();

                if (count($tmp) > 0) {

                    $groups = collect($tmp)->groupBy('route_id')->toArray();

                    foreach ($groups as $key => $shifts) {

                        $route = $this->routes->getRouteById($key);

                        $obj = new \stdClass;
                        $obj->position_name = $position_name;
                        $obj->route_name = $route ? $route->name : '';
                        $obj->route_id = $key;
                        $obj->role_id = $user->role->id;
                        $obj->fullname = $user->fullname;
                        $obj->count_shift = count($shifts);
                        $tmp_shifts = $shifts;

                        for ($i=1; $i <= $number_day_in_month ; $i++) {
                            $fr_day = date('Y-m-'.sprintf("%02d",$i).' 00:00:00',strtotime($from_date));
                            $to_day = date('Y-m-'.sprintf("%02d",$i).' 23:59:59',strtotime($to_date));
                            $count = 0;
                            foreach ($tmp_shifts as $k => $v) {
                               if ($v['ended'] >= $fr_day && $v['ended'] <= $to_day) {
                                 $count++;
                                 unset($tmp_shifts[$k]);
                               }
                            }

                            $day = 'day'.$i;
                            $obj->$day = $count;
                        }
                        $result_arr[] = $obj;
                    }
                }
            }
        }

        return $result_arr;
    }

    public function viewOutputByVehicle($data)
    {

        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];
        $vehicle_id = $data['vehicle_id'] ?? 0;

        if ($vehicle_id != 0) {

            $shifts_arr = [];
            $result = [];

            $shifts = Shift::where([
                ['shifts.ended', '>=', $from_date],
                ['shifts.ended', '<=', $to_date],
                ['shifts.ended', '!=', NULL],
                ['shifts.shift_destroy', '!=', 1],
                ['vehicles.company_id', '=', $company_id],
                ['shifts.vehicle_id', '=', $vehicle_id]
            ])
                ->leftJoin('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
                ->leftJoin('routes', 'routes.id', '=', 'shifts.route_id')
                ->select('shifts.*', 'vehicles.license_plates', 'routes.name as route_name', 'routes.number as route_number')
                ->get();

            $obj_total = new \stdClass;
            $obj_total->total_ticket_student = 0;
            $obj_total->total_ticket_price = 0;
            $obj_total->total_ticket_month = 0;
            $obj_total->total_ticket_free = 0;
            $obj_total->total_ticket_worker = 0;
            $obj_total->total_ticket_qrcode = 0;
            $obj_total->total_ticket_charge = 0;
            $obj_total->count_total_trip = 0;

            if (count($shifts) > 0) {

                foreach ($shifts as $shift) {

                    $obj = new \stdClass;
                    $obj->license_plate = $shift->license_plates;
                    $obj->driver_name = '';
                    $obj->subdriver_name = '';
                    $obj->station_start = '';
                    $obj->started = date("d-m-Y H:i:s", strtotime($shift->started));
                    $obj->ended = empty($shift->ended) ? '' : date("d-m-Y H:i:s", strtotime($shift->ended));
                    $obj->sign = '';
                    $obj->count_ticket_student = 0;
                    $obj->count_ticket_total = 0;
                    $obj->count_ticket_month = 0;
                    $obj->count_ticket_free = 0;
                    $obj->count_ticket_worker = 0;
                    $obj->count_ticket_qrcode = 0;
                    $obj->count_ticket_charge = 0;
                    $obj->start_number = 0;
                    $obj->end_number = 0;
                    $obj->ticket_number = 0;
                    $obj->type = '';
                    $obj->route_name = $shift->route_name;
                    $obj->route_number = $shift->route_number;

                    //get driverdriver
                    if (!empty($shift->user_id)) {
                        $user_id = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                        if (!empty($user_id)) {
                            $obj->driver_name = $user_id['fullname'];
                        }
                    }

                    // get subdriver
                    if (!empty($shift->subdriver_id)) {
                        $subdriver_id = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                        if (!empty($subdriver_id)) {
                            $obj->subdriver_name = $subdriver_id['fullname'];
                        }
                    }

                    //get bus station
                    $bus_station = $this->bus_stations->getDataBusStationById($shift->station_id);

                    if ($bus_station) {
                        $obj->station_start = $bus_station->name;
                    }

                    // handle transactions
                    $transactions = Transaction::where([
                        ['shift_id', $shift->id],
                        ['company_id', $company_id],
                        ['ticket_destroy', '!=', 1],

                    ])->whereIn('type', ['pos', 'charge', 'qrcode', 'deposit_month'])
                        ->orderByRaw('cast(ticket_number as UNSIGNED)')
                        ->get();

                    if (count($transactions) > 0) {

                        $gr_trans_by_ticket_price_id = collect($transactions)->groupBy('ticket_price_id');

                        foreach ($gr_trans_by_ticket_price_id as $keys => $values) {

                            $obj->start_number = empty($values[0]->ticket_number) ? 0 : $values[0]->ticket_number;
                            $obj->end_number = $values[count($values) - 1]->ticket_number;
                            $obj->count_ticket_total = (($values[count($values) - 1]->ticket_number) - ($values[0]->ticket_number)) + 1;

                            foreach ($values as $k => $v) {

                                $obj->type = $v->type;
                                $obj->ticket_number = $v->ticket_number;
                                $obj->sign = $v->sign;

                                if ($obj->type == 'pos' && $obj->ticket_number != '') {
                                    $obj->count_ticket_student += 1;
                                }
                                if ($obj->type == 'charge' && $obj->ticket_number != '') {
                                    $obj->count_ticket_charge += 1;
                                }
                                if ($obj->type == 'qrcode' && $obj->ticket_number != '') {
                                    $obj->count_ticket_qrcode += 1;
                                }
                                if ($obj->type == 'charge_free' && $obj->ticket_number != '') {
                                    $obj->count_ticket_free += 1;
                                }
                                if ($obj->type == 'deposit_month' && $obj->ticket_number != '') {
                                    $obj->count_ticket_month += 1;
                                }
                            }
                            break;
                        }
                    }
                    // push data
                    array_push($shifts_arr, $obj);

                    $obj_total->total_ticket_student += $obj->count_ticket_student;
                    $obj_total->total_ticket_price += $obj->count_ticket_total;
                    $obj_total->total_ticket_month += $obj->count_ticket_month;
                    $obj_total->total_ticket_free += $obj->count_ticket_free;
                    $obj_total->total_ticket_worker += $obj->count_ticket_worker;
                    $obj_total->total_ticket_qrcode += $obj->count_ticket_qrcode;
                    $obj_total->total_ticket_charge += $obj->count_ticket_charge;
                    $obj_total->count_total_trip += count($shift);
                }
            }

            $result['obj_total'] = $obj_total;
            $result['result_arr'] = $shifts_arr;
            // $result['isCheckModuleApp'] = $isCheckModuleApp;
            $result['isCheckModuleApp'] = $this->isCheckModuleApp($company_id);

            return $result;
        }
    }

    public function viewShiftSupervisor($data)
    {
        $company_id = $data['company_id'];
        $user_id = (int)$data['user_id'] ?? 0;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $results = [];

        $where = [
          ['shift_supervisor.ended', '>=', $from_date],
          ['shift_supervisor.ended', '<=', $to_date],
          ['shift_supervisor.ended', '!=', NULL]
        ];

        if($user_id > 0) $where[] = ['shift_supervisor.user_id', '=', $user_id];

        $shift_superviser = ShiftSupervisor::join('shifts', 'shifts.id', '=', 'shift_supervisor.shift_id')
                          ->join('users', 'users.id', '=', 'shift_supervisor.user_id')
                          ->join('vehicles', 'vehicles.id', '=', 'shifts.vehicle_id')
                          ->join('routes', 'routes.id', '=', 'shifts.route_id')
                          ->where($where)
                          ->select(
                            'shift_supervisor.*',
                            'users.fullname',
                            'vehicles.license_plates as license_plates',
                            'routes.name as route_name'
                          )->orderBy('shift_supervisor.user_id')
                          ->get();

        if(count($shift_superviser) > 0){

            foreach ($shift_superviser as $value) {

                $value = (object) $value;
                $obj =  new \stdClass;
                $obj->fullname = $value->fullname;
                $obj->license_plates = $value->license_plates;
                $obj->route_name = $value->route_name;
                $obj->start_end = $value->started. ' <=> '.$value->ended;
                $obj->station_up = '';
                $obj->station_down = '';

                if(!empty($value->station_up_id)){
                  $bus_station = $this->bus_stations->getDataBusStationById($value->station_up_id);
                  $obj->station_up = $bus_station ? $bus_station->name : '';
                }

                if(!empty($value->station_down_id)){
                  $bus_station = $this->bus_stations->getDataBusStationById($value->station_down_id);
                  $obj->station_down = $bus_station ? $bus_station->name : '';
                }

                $results[] = $obj;
            }
        }

        return $results;
    }

    //-----------------------------------------export------------------------------------//
    //export detail transaction in receipt
    public function exportTransaction($data)
    {

        $shift_id = $data['shift_id'];
        $company_id = $data['company_id'];

        $company_name = '';
        $company_address = '';
        $driver_name = '';
        $subdriver_name = '';

        $type = '';
        $ticket_number = '';
        $amount = 0;
        $created_at = '';

        $shift_detail = [];
        $trans_arr = [];
        $prices_arr = [];
        $types = [];
        $deposit = 0;
        $charge = 0;

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        //get shifts
        $shift = $this->shifts->getShiftsById($shift_id);

        //get driver
        if ($shift->user_id) {
            $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
            $driver_name = $driver['fullname'];
        }

        // get subdriver
        if ($shift->subdriver_id) {
            $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
            $subdriver_name = $subdriver['fullname'];
        }

        // get transactions
        $transactions = $this->transactions->getTransactionByOptions([
            ['shift_id', $shift_id],
            ['company_id', $company_id],
            ['ticket_destroy', '!=', 1]
        ]);

        // path root
        $file_name = 'ChiTietPhieuThu_GiaoDich_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BẢNG CHI TIẾT PHIẾU THU - GIAO DỊCH',
            'quarter' => $driver_name . " - " . $subdriver_name,
        ];

        //merges array
        $merges_arr = ['A1:C1', 'A2:C2', 'A4:F4', 'A5:F5'];

        // table
        $a7_to_f7 = [
            ['STT', 'LOẠI VÉ', 'ĐỊA ĐIỂM GIAO DỊCH', 'MÃ SỐ VÉ', 'SỐ TIỀN (VNĐ)', 'TẠO LÚC'],
        ];
        $spread_sheet->getActiveSheet()->fromArray($a7_to_f7, NULL, 'A7');
        $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_all_borders);

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        // set data
        $lines = 1;
        $cell = 7;
        $cell_value = $lines + $cell;

        if (count($transactions) > 0) {

            foreach ($transactions as $transaction) {

                $station_name = '';

                //get bus station
                $bus_station = $this->bus_stations->getDataBusStationById($transaction->station_id);

                if ($bus_station) {
                    $station_name = $bus_station->name;
                }

                $cell_value = $lines + $cell;
                $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                if ($transaction->type == 'pos') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tiền mặt');
                }
                if ($transaction->type == 'app:1') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Online');
                }
                if ($transaction->type == 'charge') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Quẹt thẻ');
                }
                if ($transaction->type == 'deposit') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Nạp thẻ');
                }
                if ($transaction->type == 'pos_goods') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Hàng hóa (tiền mặt)');
                }
                if ($transaction->type == 'charge_goods') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Hàng hóa (quẹt thẻ)');
                }
                if ($transaction->type == 'qrcode_goods') {
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Hàng hóa (online)');
                }
                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $station_name);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $transaction->ticket_number);
                $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $transaction->amount);
                $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $transaction->activated);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                $lines++;
            }
        }
        // save spread sheet
        $this->downloadFileTransaction($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Giao dich', 'by_shift', $data = [], false);
    }

    public function exportReceipt($data)
    {

        // sort shifts and get data
        sort($data['shifts']);
        $shifts = $data['shifts'];
        $date = $data['date'];
        $company_id = $data['company_id'];
        $company_name = '';
        $company_address = '';
        $from_to_date = '';
        $total_prices = 0;
        $total_charge = 0;
        $total_deposit = 0;
        $license_plate = '';
        $driver_name = '';
        $subdriver_name = '';
        $route_name = '';
        $station_name = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        //get datetime
        if (count($shifts) > 0) {

            $first_shift = $this->shifts->getShiftsById((int) current($shifts));

            if (count($shifts) > 1) {

                $last_shift = $this->shifts->getShiftsById((int) end($shifts));
                $from_to_date = 'từ ' . $first_shift->started . ' đến ' . $last_shift->ended;
            } else {
                $from_to_date = 'từ ' . $first_shift->started . ' đến ' . $first_shift->ended;
            }
        }

        // date text
        $date_explode = explode('-', $date);
        $date_text = 'Ngày ' . $date_explode[2] . ' tháng ' . $date_explode[1] . ' năm ' . $date_explode[0];

        // get total price
        foreach ($shifts as $id) {

            // get shift by id
            $shift = $this->shifts->getShiftsById((int) $id);

            if ($shift) {

                // get vihicel
                $vehicle = $this->vehicles->getVehicleById($shift->vehicle_id);

                if ($vehicle) {
                    $license_plate = $vehicle->license_plates;
                }

                //get driver
                if ($shift->user_id) {

                    $driver = $this->users->getUserByKey('id', $shift->user_id, $company_id);
                    $driver_name = $driver['fullname'];
                }

                // get subdriver
                if ($shift->subdriver_id) {

                    $subdriver = $this->users->getUserByKey('id', $shift->subdriver_id, $company_id);
                    $subdriver_name = $subdriver['fullname'];
                }

                // get route
                $route = $this->routes->getRouteById($shift->route_id);
                if ($route) {
                    $route_name = $route->name;
                }

                //get station name
                $bus_station = $this->bus_stations->getDataBusStationById($shift->station_id);
                if ($bus_station) {
                    $station_name = $bus_station->name;
                }

                $transactions = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id],
                    ['ticket_destroy', '!=', 1]
                ]);

                if (count($transactions) > 0) {

                    foreach ($transactions as $transaction) {

                        $type = $transaction->type;
                        $amount = $transaction->amount;

                        if ((float) $amount > 0) {
                            if ($type == 'charge' && $transaction->ticket_number != '') $total_charge += (float) $transaction->amount;
                            if ($type == 'deposit') $total_deposit += (float) $transaction->amount;
                            if ($type == 'pos' && $transaction->ticket_number != '') $total_prices += (float) $transaction->amount;
                        }
                    }
                }
            }
        }

        //payer text
        $payer_text = $license_plate . ' - ' . $driver_name . ' - ' . $subdriver_name;

        //total
        $total_all = $total_prices + $total_deposit;

        // path root
        $file_name = 'PhieuThu_' . date("d-m-Y", strtotime($date));

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A5);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle('Phieu Thu');

        //merges array
        $merges_arr = ['A1:H1', 'A2:H2', 'J1:M1', 'J2:M3', 'A5:M5', 'A6:M6', 'A8:B8', 'A9:B9', 'A10:B10', 'A11:B11', 'L17:M17', 'C18:D18', 'F18:G18', 'I18:J18', 'L18:M18'];

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $company_name);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $company_address);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('J1', 'Mẫu số 01-TT');
        $spread_sheet->getActiveSheet()->getStyle('J1')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->setCellValue('J2', '(Ban hành theo Thông tư số 200/2014/TT-
        BTC Ngày 22/12/2014 của Bộ Tài Chính)');
        $spread_sheet->getActiveSheet()->getStyle('J2')->applyFromArray($style_center);

        $spread_sheet->getActiveSheet()->setCellValue('A5', 'PHIẾU THU');
        $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(15);

        $spread_sheet->getActiveSheet()->setCellValue('A6', $date_text);
        $spread_sheet->getActiveSheet()->getStyle('A6')->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->getStyle('A6')->getFont()->setItalic(true);

        $spread_sheet->getActiveSheet()->setCellValue('J7', 'Số phiếu');
        $spread_sheet->getActiveSheet()->setCellValue('K7', '........./' . $date_explode[1]);

        $spread_sheet->getActiveSheet()->setCellValue('A8', 'Người nộp tiền: ');
        $spread_sheet->getActiveSheet()->setCellValue('C8', $payer_text);

        $spread_sheet->getActiveSheet()->setCellValue('A9', 'Tuyến: ');
        $spread_sheet->getActiveSheet()->setCellValue('C9', $route_name);

        $spread_sheet->getActiveSheet()->setCellValue('A10', 'Đi từ: ');

        if (!empty($station_name)) {
            $spread_sheet->getActiveSheet()->setCellValue('C10', 'Trạm ' . $station_name);
        }

        $spread_sheet->getActiveSheet()->setCellValue('A11', 'Lý do nộp: ');
        $spread_sheet->getActiveSheet()->setCellValue('C11', 'Thu tiền xe buýt ' . $from_to_date);

        $spread_sheet->getActiveSheet()->setCellValue('A12', 'Số tiền (tiền mặt): ');
        $spread_sheet->getActiveSheet()->setCellValue('E12', $total_prices);
        $spread_sheet->getActiveSheet()->getStyle('E12')->getNumberFormat()->setFormatCode('#,##0');
        $spread_sheet->getActiveSheet()->getStyle('E12')->getFont()->setBold(true);

        $spread_sheet->getActiveSheet()->setCellValue('G12', '(Bằng chữ : ' . convert_number_to_words((int) $total_prices) . '. )');
        $spread_sheet->getActiveSheet()->getStyle('G12')->getFont()->setItalic(true);

        $spread_sheet->getActiveSheet()->setCellValue('A13', 'Số tiền (nạp thẻ): ');
        $spread_sheet->getActiveSheet()->setCellValue('E13', $total_deposit);
        $spread_sheet->getActiveSheet()->getStyle('E13')->getNumberFormat()->setFormatCode('#,##0');
        $spread_sheet->getActiveSheet()->getStyle('E13')->getFont()->setBold(true);

        $spread_sheet->getActiveSheet()->setCellValue('G13', '(Bằng chữ : ' . convert_number_to_words((int) $total_deposit) . '. )');
        $spread_sheet->getActiveSheet()->getStyle('G13')->getFont()->setItalic(true);

        $spread_sheet->getActiveSheet()->setCellValue('A14', 'Tổng tiền cần nộp (tiền mặt + nạp thẻ): ');
        $spread_sheet->getActiveSheet()->setCellValue('E14',  $total_all);
        $spread_sheet->getActiveSheet()->getStyle('E14')->getNumberFormat()->setFormatCode('#,##0');
        $spread_sheet->getActiveSheet()->getStyle('E14')->getFont()->setBold(true);

        $spread_sheet->getActiveSheet()->setCellValue('G14', '(Bằng chữ : ' . convert_number_to_words((int) $total_all) . '. )');
        $spread_sheet->getActiveSheet()->getStyle('G14')->getFont()->setItalic(true);

        $spread_sheet->getActiveSheet()->setCellValue('A16', 'Kèm theo');
        $spread_sheet->getActiveSheet()->setCellValue('G16', 'Chứng từ gốc');

        $spread_sheet->getActiveSheet()->setCellValue('K19', 'Ngày .... tháng .... năm .......');

        $spread_sheet->getActiveSheet()->setCellValue('A20', 'GIÁM ĐỐC');
        $spread_sheet->getActiveSheet()->getStyle('A20')->getFont()->setBold(true);
        $spread_sheet->getActiveSheet()->setCellValue('C20', 'KẾ TOÁN TRƯỞNG');
        $spread_sheet->getActiveSheet()->getStyle('C20')->getFont()->setBold(true);
        $spread_sheet->getActiveSheet()->setCellValue('F20', 'NGƯỜI NỘP TIỀN');
        $spread_sheet->getActiveSheet()->getStyle('F20')->getFont()->setBold(true);
        $spread_sheet->getActiveSheet()->setCellValue('I20', 'NGƯỜI LẬP PHIẾU');
        $spread_sheet->getActiveSheet()->getStyle('I20')->getFont()->setBold(true);
        $spread_sheet->getActiveSheet()->setCellValue('L20', 'THỦ QUỸ');
        $spread_sheet->getActiveSheet()->getStyle('L20')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A21', '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A21')->getFont()->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('A21')->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->setCellValue('C21', '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('C21')->getFont()->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('C21')->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->setCellValue('F21', '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('F21')->getFont()->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('F21')->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->setCellValue('I21', '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('I21')->getFont()->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('I21')->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->setCellValue('L21', '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('L21')->getFont()->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('L21')->applyFromArray($style_center);

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, "Xls");
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: private");
        header("Content-Encoding: none");
        echo base64_encode($excel_output);
    }

    public function exportTickets($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'];
        $company_name = '';
        $company_address = '';

        $ticket_arr = [];

        // date
        if ($from_date) {
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        } else {
            $report_date = 'Tất cả thời gian';
        }

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $title = 'BÁO CÁO TỔNG HỢP SỐ LƯỢNG VÉ';
        $file_name = 'DoanhThuVeXe_' . date('Ymd');

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $ticket_arr =  $this->viewTicket($data);

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];
        $route_title = 'Tất cả các tuyến';
        if ($route_id > 0) {
            $route = $this->routes->getRouteById($route_id);
            if ($route) {
                $route_title = 'Tuyến : ' . $route->name;
            }
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => $route_title
        ];

        //merges array
        $merges_arr = ['A1:F1', 'A2:F2', 'A4:K4', 'A5:K5', 'A6:K6', 'A8:A9', 'B8:B9', 'C8:C9', 'D8:D9', 'E8:E9', 'F8:I8', 'J8:J9', 'K8:K9'];

        // table
        $a8_to_k9 = [
            ['STT', 'Mệnh giá','Mã hóa đơn', 'Tuyến', 'Tổng số lượng vé đã cấp', 'Tổng số lượng vé đã bán', NULL, NULL, NULL,'Tổng doanh thu', 'Thực thu'],
            [NULL, NULL, NULL, NULL, NULL, "Vé lượt", 'Vé thẻ', 'Vé tháng', 'Vé Momo', NULL, NULL]
        ];
        $spread_sheet->getActiveSheet()->fromArray($a8_to_k9, NULL, 'A8');
        $spread_sheet->getActiveSheet()->getStyle('A8:K9')->applyFromArray($style_center_bold);

        // set data
        $lines = 1;
        $cell = 9;
        $cell_value = $lines + $cell;

        if (count($ticket_arr) > 0) {

            foreach ($ticket_arr as $ticket) {

                $cell_value = $lines + $cell;

                if($ticket['route_number'] != 'all'){

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $ticket['price']);
                    $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $ticket['order_code']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $ticket['route_number']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $ticket['total_released']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $ticket['total_pos']);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $ticket['total_charge']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $ticket['total_deposit_month']);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $ticket['total_qrcode']);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $ticket['total_revenue']);
                    $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $ticket['total_collected']);
                    $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                }else{

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $ticket['total_released']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $ticket['total_pos']);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $ticket['total_charge']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $ticket['total_deposit_month']);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center_bold);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $ticket['total_qrcode']);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $ticket['total_revenue']);
                    $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $ticket['total_collected']);
                    $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold);
                }

                $lines++;
            }
        }

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        // save spread sheet
        $this->downloadFileTicket($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Ve Ban', 5);
    }

    public function exportTicketsByStation($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'];
        $company_name = '';
        $company_address = '';

        $ticket_arr = [];

        // date
        if ($from_date) {
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        } else {
            $report_date = 'Tất cả thời gian';
        }

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $title = 'BÁO CÁO SỐ LƯỢNG VÉ THEO TRẠM';
        $file_name = 'DoanhThuVeXe_' . date('Ymd');

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $ticket_arr =  $this->viewTicketByStation($data);
        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $route_title = 'Tất cả các tuyến';
        if ($route_id > 0) {
            $route = $this->routes->getRouteById($route_id);
            if ($route) {
                $route_title = 'Tuyến : ' . $route->name;
            }
        }
        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => $route_title
        ];

        //merges array
        $merges_arr = ['A1:E1', 'A2:E2', 'A4:I4', 'A5:I5', 'A6:I6', 'A8:A9', 'B8:B9', 'C8:C9', 'D8:D9', 'E8:E9', 'F8:I8'];

        // table
        $a8_to_h9 = [
            ['STT', 'Mệnh giá','Mã hóa đơn', 'Tuyến', 'Trạm', 'Tổng số lượng vé đã bán', NULL, NULL, NULL],
            [NULL, NULL, NULL, NULL, NULL, "Vé lượt", 'Vé thẻ', 'Vé tháng', 'Online']
        ];

        $spread_sheet->getActiveSheet()->fromArray($a8_to_h9, NULL, 'A8');
        $spread_sheet->getActiveSheet()->getStyle('A8:I9')->applyFromArray($style_center_bold);

        // set data
        $lines = 1;
        $cell = 9;
        $cell_value = $lines + $cell;
        if (isset($ticket_arr) > 0) {
            foreach ($ticket_arr as $ticket) {

                $cell_value = $lines + $cell;
                if (isset($ticket['price']) && isset($ticket['route_number']) && isset($ticket['station_name'])) {
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $ticket['price']);
                    $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $ticket['order_code']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $ticket['route_number']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $ticket['station_name']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $ticket['total_pos']);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $ticket['total_charge']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $ticket['total_deposit_month']);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $ticket['total_qrcode']);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $lines++;
                }
            }
            if (isset($ticket_arr[(count($ticket_arr)) - 1]['total_charge']) && isset($ticket_arr[(count($ticket_arr)) - 1]['total_deposit_month']) && isset($ticket_arr[(count($ticket_arr)) - 1]['total_qrcode']) && isset($ticket_arr[(count($ticket_arr)) - 1]['total_pos'])) {

                $cell_value = $lines + $cell;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $ticket_arr[(count($ticket_arr)) - 1]['total_pos']);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $ticket_arr[(count($ticket_arr)) - 1]['total_charge']);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $ticket_arr[(count($ticket_arr)) - 1]['total_deposit_month']);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $ticket_arr[(count($ticket_arr)) - 1]['total_qrcode']);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
            }
        }
        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }
        // save spread sheet
        $this->downloadFileTicketByStation($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Ve Ban', 5);
    }

    public function exportInvoice($data)
    {

        $company_id = $data['company_id'];
        $company_name = '';
        $company_address = '';
        $company_tax_code = '';

        $invoice_arr = $this->viewInvoice($data);
        // return $invoice_arr;
        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($data['from_date'])) . ' đến ngày ' . date("d/m/Y", strtotime($data['to_date']));

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
            $company_tax_code = $company->tax_code;
        }

        //set file_name
        $file_name = 'TinhHinhSuDungHoaDon_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        //$spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        //header excel
        $header_excel = [
            'title' => 'BÁO CÁO TÌNH HÌNH SỬ DỤNG HÓA ĐƠN (BC26/AC)',
            'date_from_to' => $report_date,
            'com_name' => $company_name,
            'tax_code' =>  $company_tax_code,
        ];

        //merges array
        $merges_arr = [
            'B1:X1', 'B2:X2', 'B4:D4', 'E4:J4', 'B5:D5', 'E5:H5', 'W5:X5', 'C9:E9', 'H9:L9',
            'B12:B15', 'C12:C15', 'D12:D15', 'E12:E15', 'F12:F15', 'G12:K12', 'L12:U12', 'V12:X12',
            'G13:G15', 'H13:I14', 'J13:K14', 'L13:N14', 'O13:U13', 'V13:V15', 'W13:W15', 'X13:X15',
            'O14:O15', 'P14:Q14', 'R14:S14', 'T14:U14',
        ];

        // table
        $b12_to_x16 = [
            [
                'STT', 'Loại hóa đơn', 'Tên loại hóa đơn', 'Ký hiệu hóa đơn ',
                'Ký hiệu hóa đơn', 'Số tồn đầu kỳ, mua/phát hành trong kỳ', NULL, NULL, NULL, NULL,
                'Số sử dụng, xóa bỏ, mất, hủy trong kỳ',  NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                'Tồn cuối kỳ', NULL, NULL
            ],
            [
                NULL, NULL, NULL, NULL, NULL, "Tổng số", "Số tồn đầu kỳ", NULL,
                "Số mua/phát hành trong kỳ ", NULL, "Tổng số sử dụng, xóa bỏ, mất, hủy", NULL, NULL,
                "Trong đó", NULL, NULL, NULL, NULL, NULL, NULL, "Từ số", "Đến số", "Số lượng"
            ],
            [
                NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                "Số lượng đã sử dụng ", "Xóa bỏ", NULL, "Mất", NULL, "Hủy", NULL
            ],
            [
                NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                "Số lượng", "Số", "Số lượng", "Số", "Số lượng", "Số", NULL, NULL, NULL
            ],
            [
                "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"
            ]
        ];

        $spread_sheet->getActiveSheet()->setCellValue('H15', 'Từ số ');
        $spread_sheet->getActiveSheet()->setCellValue('I15', 'Đến số ');
        $spread_sheet->getActiveSheet()->setCellValue('J15', 'Từ số ');
        $spread_sheet->getActiveSheet()->setCellValue('K15', 'Đến số ');
        $spread_sheet->getActiveSheet()->setCellValue('L15', 'Từ số ');
        $spread_sheet->getActiveSheet()->setCellValue('M15', 'Đến số ');
        $spread_sheet->getActiveSheet()->setCellValue('N15', 'Cộng');

        $spread_sheet->getActiveSheet()->fromArray($b12_to_x16, NULL, 'B12');
        $spread_sheet->getActiveSheet()->getStyle('B12:X16')->applyFromArray($style_center_bold);

        $lines = 1;
        $cell = 16;
        $cell_value = $lines + $cell;

        if (count($invoice_arr) > 0) {

            $ticket_invoice_total = [
                'total_begin_in_period' => 0,
                'total_type_all_in_period' => 0,
                'total_used_in_period' => 0,
                'total_remove_in_period' => 0,
                'total_die_in_period' => 0,
                'total_cancle_in_period' => 0,
                'total_last_period' => 0,
            ];

            foreach ($invoice_arr as $invoice) {

                $cell_value = $lines + $cell;

                // $spread_sheet->getActiveSheet()->setCellValue('A'.$cell_value, $invoice->device_id);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $invoice->ticket_type_code);
                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $invoice->ticket_type_description);
                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $invoice->ticket_type_order_code);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $invoice->ticket_type_sign);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $invoice->total_begin_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $invoice->start_num_begin_period);
                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $invoice->end_num_begin_period);
                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $invoice->start_num_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $invoice->end_num_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $invoice->start_num_type_all_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $invoice->end_num_type_all_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $invoice->total_type_all_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $invoice->total_used_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $invoice->total_remove_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $invoice->str_num_remove_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $invoice->total_die_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $invoice->str_num_die_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $invoice->total_cancle_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $invoice->str_num_cancle_in_period);
                $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $invoice->start_num_last_period);
                $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, $invoice->end_num_last_period);
                $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $invoice->total_last_period);

                //handle total all
                $ticket_invoice_total['total_begin_in_period'] += $invoice->total_begin_in_period;
                $ticket_invoice_total['total_type_all_in_period'] += $invoice->total_type_all_in_period;
                $ticket_invoice_total['total_die_in_period'] +=  $invoice->total_die_in_period;
                $ticket_invoice_total['total_cancle_in_period']  += $invoice->total_cancle_in_period;
                $ticket_invoice_total['total_last_period'] += $invoice->total_last_period;
                $ticket_invoice_total['total_remove_in_period'] +=  $invoice->total_remove_in_period;
                $ticket_invoice_total['total_used_in_period'] +=  $invoice->total_used_in_period;

                $lines++;
            }

            $invoice_total =  $ticket_invoice_total;
            $cell_value = $lines + $cell;
            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng cộng');
            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('B' . $cell_value . ':F' . $cell_value);
            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $invoice_total['total_begin_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $invoice_total['total_type_all_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $invoice_total['total_used_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $invoice_total['total_remove_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $invoice_total['total_die_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $invoice_total['total_cancle_in_period']);
            $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $invoice_total['total_last_period']);
            $spread_sheet->getActiveSheet()->getStyle('X' . $cell_value)->applyFromArray($style_bold);
        }
        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }
        // save spread sheet
        $this->downloadFileInvoice($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Hoa don');
    }

    //export route ( or daily)
    public function exportDaily($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $route_id = $data['route_id'];
        $company_id = $data['company_id'];

        $company_name = '';
        $company_address = '';

        $route_result = $this->viewDaily($data);
        $isCheckModuleApp = $route_result['isCheckModuleApp'];

        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $title = 'BẢNG TỔNG HỢP BÁO CÁO DOANH THU VÀ THU TIỀN HÀNG NGÀY';
        $file_name = 'DoanhThuTatCaTuyen_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $route_name_title = '';
        if ($route_id == 0) {
            $route_name_title = 'Tất cả các tuyến xe buýt';
        } else {

            $route = $this->routes->getRouteById($route_id);
            if ($route) {
                $route_name_title = "Tuyến: " . $route->name;
            }
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' =>  $route_name_title
        ];

        if ($isCheckModuleApp) {
            //merges array
            $merges_arr = [
                'A1:G1', 'A2:G2', 'A4:W4', 'A5:W5', 'A6:W6',
                'A8:A10', 'B8:E8', 'F8:F10', 'G8:O8', 'P8:R8', 'S8:S10', 'T8:T10', 'U8:U10', 'V8:V10', 'W8:W10',
                'B9:B10', 'C9:C10', 'D9:D10', 'E9:E10', 'G9:H9', 'I9:L9', 'H9:K9',
                'P9:P10', 'Q9:Q10', 'R9:R10','M9:N9'
            ];

            // table
            $a8_to_t11 = [
                ['STT', 'THÔNG TIN TUYẾN', NULL, NULL, NULL, 'Nợ ngày trước', 'DOANH THU VÉ XE', NULL, NULL, NULL, NULL, NULL,NULL,NULL,NULL,'TỔNG CỘNG', NULL, NULL, 'NẠP THẺ', 'Thu tiền', 'Còn nợ', 'Mã NV thu tiền', 'Ngày thu'],
                [NULL, 'Số xe', "Tài xế", 'Phụ xe', 'Mã tuyến', NULL, 'Vé lượt',NULL,NULL,'Thẻ',NULL,NULL,'MoMo', NULL,'Vé tháng','Tổng doanh thu','Tổng chiết khấu', 'Tổng thực thu', NULL, NULL, NULL, NULL, NULL],
                [NULL, NULL, NULL, NULL, NULL, NULL, 'SL', 'Doanh thu', 'SL', 'Doanh thu', 'Chiết khấu', 'Thực thu', 'SL', 'Doanh thu', 'Số lượt đi', NULL, NULL, NULL, NULL, NULL],
                ['A', 'B', 'C', 'D', 'E', '1', '2', '3', '4', '5', '6', '7', '8','9','10','11','12', '13', '14', '15','16', '17','18'],
            ];
            $spread_sheet->getActiveSheet()->fromArray($a8_to_t11, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:W11')->applyFromArray($style_center_bold);

            // set data
            $lines = 1;
            $cell = 11;
            $cell_value = $lines + $cell;

            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Nợ ngày trước toàn tuyến');
            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);
            if (!empty($route_result['route_group_debt_all'])) {
                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $route_result['route_group_debt_all']['total_debt']);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value.':F'.$cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            } else {
                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, 0);
                $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value.':F'.$cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            }

            $lines =  $lines + 1;

            if (count($route_result['route_detail']) > 0) {

                foreach ($route_result['route_detail'] as $key_detail => $route) {

                    $route = (array)$route;
                    $cell_value = $lines + $cell;

                    if (isset($route['license_plate']) && !isset($route['total_debt'])) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $key_detail + 1 );
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $route['license_plate']);
                        $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $route['driver_name']);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $route['subdriver_name']);

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $route['route_number']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $route['count_ticket_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $route['total_price_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $route['count_ticket_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $route['total_price_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $route['total_price_discount']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $route['total_price_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $route['count_ticket_online']);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $route['total_price_online']);
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $route['count_ticket_month']);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $route['count_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $route['count_discount_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $route['count_collected_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $route['total_price_deposit']);
                        $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $route['total_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $route['total_not_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $route['staff_collected']);

                        if ($route['date_collected']) {
                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, date("d/m/y h:m:s", strtotime($route['date_collected'])));
                        }
                    }

                    if (isset($route['license_plate']) && isset($route['total_debt']) ) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $key_detail + 1  );
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $route['license_plate']);
                        $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $route['driver_name']);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $route['subdriver_name']);

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value,  $route['route_number']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $route['total_debt']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value,  $route['total_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $route['staff_collected']);

                        if ($route['date_collected']) {
                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, date("d/m/y h:m:s", strtotime($route['date_collected'])));
                        }
                    }

                    if (!isset($route['license_plate']) && !isset($route['total_debt'])) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng tuyến ' . $route['route_number']);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':C' . $cell_value);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $route['title_debt_any']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $route['total_debt_any']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $route['total_debt_route']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $route['count_ticket_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $route['total_price_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $route['count_ticket_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $route['total_price_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $route['total_price_discount']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $route['total_price_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $route['count_ticket_online']);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $route['total_price_online']);
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $route['count_ticket_month']);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $route['count_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $route['count_discount_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $route['count_collected_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $route['total_price_deposit']);
                        $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $route['total_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $route['total_not_collected']);
                        $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_bold);
                        $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value.':U'.$cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                    }

                    $lines++;
                }
            }

            if (!empty($route_result['route_group_colected_all'])) {

                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng toàn tuyến');
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value,  $route_result['route_group_colected_all']['total_debt']);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $route_result['route_group_colected_all']['total_debt']);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $route_result['route_group_colected_all']['count_ticket_pos']);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $route_result['route_group_colected_all']['total_price_pos']);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $route_result['route_group_colected_all']['count_ticket_charge']);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $route_result['route_group_colected_all']['total_price_charge']);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $route_result['route_group_colected_all']['total_price_discount']);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $route_result['route_group_colected_all']['total_price_collected']);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $route_result['route_group_colected_all']['count_ticket_online']);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $route_result['route_group_colected_all']['total_price_online']);
                $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $route_result['route_group_colected_all']['count_ticket_month']);
                $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $route_result['route_group_colected_all']['count_revenue_ticket']);
                $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $route_result['route_group_colected_all']['count_discount_ticket']);
                $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $route_result['route_group_colected_all']['count_collected_ticket']);
                $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $route_result['route_group_colected_all']['total_price_deposit']);
                $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_bold);

                $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $route_result['route_group_colected_all']['total_collected']);
                $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $route_result['route_group_colected_all']['total_not_collected']);
                $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value.':U'.$cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            }

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // save spread sheet
            $this->downloadFileDaily($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp, $route_result['total_collecter']);
        } else {
          //
        }
    }

    public function exportStaff($data)
    {

        $route_id = (int) $data['route_id'];
        $position = $data['position'];
        $company_id = (int) $data['company_id'];
        $company_name = '';
        $company_address = '';
        $route_name_title = '';

        $staffs_arr = $this->viewStaff($data);
        $isCheckModuleApp = $staffs_arr['isCheckModuleApp'];

        $key_role = '';

        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($data['from_date'])) . ' đến ngày ' . date("d/m/Y", strtotime($data['to_date']));
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        if ($route_id == 0) {
            $route_name_title = 'Tất cả các tuyến';
        } else {
            $route_obj = $this->routes->getRouteById($route_id, $company_id);
            $route_name_title = $route_obj ? $route_obj->name : '';
        }

        if ($position == 'all') $key_role = 'role_all';
        else $key_role = 'role_only';

        if ($isCheckModuleApp) {
            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true]
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_all_borders = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
            ];

            //header excel
            $header_excel = [
                'com_name' => $company_name, 'com_addr' => $company_address,
                'title' => 'BẢNG TỔNG HỢP DOANH THU VÉ XE THEO NHÂN VIÊN',
                'quarter' => $report_date,
                'route' => 'Tuyến: ' . $route_name_title
            ];

            //merges array
            $merges_arr = ['A1:F1', 'A2:F2', 'A4:Q4', 'A5:Q5', 'A6:Q6', 'A8:A10', 'B8:D8', 'B9:B10', 'C9:C10', 'D9:D10', 'E8:M8', 'E9:F9', 'G9:J9', 'N8:P8', 'N9:N10', 'O9:O10', 'P9:P10', 'Q8:Q10','K9:L9'];

            // table
            $a8_to_q11 = [
                ['STT', 'THÔNG TIN TUYẾN', NULL, NULL, 'DOANH THU VÉ XE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,'TỔNG CỘNG',NULL, NULL, 'NẠP THẺ'],
                [NULL, 'Họ và tên', 'Tuyến', 'Chức vụ', 'Vé lượt', NULL, 'Thẻ',NULL,NULL,NULL,'MoMo',NULL,'Vé tháng','Tổng doanh thu','Tổng chiết khấu','Tổng thực thu', NULL],
                [NULL, NULL, NULL, NULL, 'SL', 'Doanh thu', 'SL', 'Doanh thu', 'Chiết khấu', 'Thực thu', 'SL','Doanh thu', 'Doanh thu', NULL],
                ['A', 'B', 'C', 'D', '1', '2', '3', '4', '5', '6','7','8','9', '10','11', '12','13']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a8_to_q11, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:Q11')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->getStyle('A8:Q11')->applyFromArray($style_all_borders);

            // set data
            $lines = 1;
            $cell = 11;
            $cell_value = $lines + $cell;

            if (count($staffs_arr['staffs_arr']) > 0) {

                foreach ($staffs_arr['staffs_arr'] as $staff) {

                    $staff = (object) $staff;

                    $cell_value = $lines + $cell;

                    if (!empty($staff->fullname))  $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $staff->fullname);

                    if (!empty($staff->position_name)) {
                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $staff->position_name);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);
                    }

                    if (!empty($staff->fullname) && !empty($staff->route_number) && !empty($staff->position_name)) {

                      $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                      $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                      $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $staff->route_number);

                      $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $staff->count_ticket_pos);
                      $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                      $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $staff->total_price_pos);
                      $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $staff->count_ticket_charge);
                      $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                      $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $staff->total_price_charge);
                      $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $staff->total_price_discount);
                      $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $staff->total_price_collected);
                      $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $staff->count_ticket_qrcode);
                      $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                      $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $staff->total_price_qrcode);
                      $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $staff->total_price_month);
                      $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');

                      $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $staff->count_revenue_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $staff->count_discount_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $staff->count_collected_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $staff->total_price_deposit);
                      $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    }else{

                      $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $staff->count_ticket_pos);
                      $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold);
                      $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $staff->total_price_pos);
                      $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);
                      $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $staff->count_ticket_charge);
                      $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold);
                      $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $staff->total_price_charge);
                      $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold);
                      $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $staff->total_price_discount);
                      $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_bold);
                      $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $staff->total_price_collected);
                      $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);
                      $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $staff->count_ticket_qrcode);
                      $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center_bold);
                      $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                      $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $staff->total_price_qrcode);
                      $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                      $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_bold);

                      $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $staff->total_price_month);
                      $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                      $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_bold);

                      $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $staff->count_revenue_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                      $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);

                      $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $staff->count_discount_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                      $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_bold);

                      $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $staff->count_collected_ticket);
                      $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                      $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_bold);

                      $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $staff->total_price_deposit);
                      $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                      $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_bold);
                    }

                    if (empty($staff->fullname) && empty($staff->route_number) && empty($staff->position_name)) {
                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                    }

                    $lines++;
                }
            }

            $data_total['data_total_only'] = $staffs_arr['data_total_only'];
            $data_total['data_total_driver'] = $staffs_arr['data_total_driver'];
            $data_total['data_total_subdriver'] = $staffs_arr['data_total_subdriver'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // path root
            $file_name = 'DoanhThuNhanVien_' . date('Ymd');

            // save spread sheet
            $this->downloadFileStaff($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $data_total, $key_role, $route_id, $isCheckModuleApp);
        } else {

            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true]
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_all_borders = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
            ];

            //header excel
            $header_excel = [
                'com_name' => $company_name, 'com_addr' => $company_address,
                'title' => 'BẢNG TỔNG HỢP DOANH THU VÉ XE THEO NHÂN VIÊN',
                'quarter' => $report_date,
                'route' => 'Tuyến: ' . $route_name_title
            ];

            if ($company_id) {

                //merges array
                $merges_arr = ['A1:C1', 'A2:C2', 'A4:F4', 'A5:F5', 'A6:F6'];

                // table
                $a8_to_f9 = [
                    ['STT', 'HỌ VÀ TÊN', 'TUYẾN', 'CHỨC VỤ', 'SỐ LƯỢNG VÉ', 'DOANH THU'],
                    ['A', 'B', 'C', 'D', '1', '2']
                ];
                $spread_sheet->getActiveSheet()->fromArray($a8_to_f9, NULL, 'A8');
                $spread_sheet->getActiveSheet()->getStyle('A8:F9')->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->getStyle('A8:F9')->applyFromArray($style_all_borders);

                // set data
                $lines = 1;
                $cell = 9;
                $cell_value = $lines + $cell;

                $data_total_only = [
                    'count_ticket_pos_only' => 0,
                    'total_ticket_pos_only' => 0
                ];

                $data_total_driver = [
                    'count_ticket_pos_driver' => 0,
                    'total_ticket_pos_driver' => 0
                ];

                $data_total_subdriver = [
                    'count_ticket_pos_subdriver' => 0,
                    'total_ticket_pos_subdriver' => 0
                ];

                if (count($staffs_arr['staffs_arr']) > 0) {

                    foreach ($staffs_arr['staffs_arr'] as $staff) {

                        $staff = (object) $staff;
                        $cell_value = $lines + $cell;

                        if (isset($staff->key_role) && $staff->key_role == 'role_only') {

                            $data_total_only['count_ticket_pos_only'] += $staff->count_ticket_pos;
                            $data_total_only['total_ticket_pos_only'] += $staff->total_price_pos;
                        }

                        if (isset($staff->key_role) && $staff->key_role == 'role_all') {

                            if ($staff->role_name == 'driver') {
                                $data_total_driver['count_ticket_pos_driver'] += $staff->count_ticket_pos;
                                $data_total_driver['total_ticket_pos_driver'] += $staff->total_price_pos;
                            }

                            if (isset($staff->role_name) && $staff->role_name == 'subdriver') {
                                $data_total_subdriver['count_ticket_pos_subdriver'] += $staff->count_ticket_pos;
                                $data_total_subdriver['total_ticket_pos_subdriver'] += $staff->total_price_pos;
                            }
                        }

                        if (!empty($staff->fullname) && !empty($staff->route_number) && !empty($staff->position_name)) {
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $staff->fullname);
                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $staff->route_number);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $staff->position_name);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $staff->count_ticket_pos);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $staff->total_price_pos);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            // $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, '');
                            $lines++;
                        }

                        if (empty($staff->fullname) && empty($staff->route_number) && empty($staff->position_name)) {
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                            $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $staff->count_ticket_pos);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $staff->total_price_pos);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                            $lines++;
                        }

                    }
                }

                $data_total['data_total_only'] = $data_total_only;
                $data_total['data_total_driver'] = $data_total_driver;
                $data_total['data_total_subdriver'] = $data_total_subdriver;
            }

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // path root
            $file_name = 'DoanhThuNhanVien_' . date('Ymd');

            // save spread sheet
            $this->downloadFileStaff($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $data_total, $key_role, $route_id, $isCheckModuleApp);
        }
    }

    public function exportVehicleByRoute($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = (int) $data['company_id'];
        $vehicle_id = (int) $data['vehicle_id'];
        $company_name = '';
        $company_address = '';
        $route_id = (int) $data['route_id'];

        $vehicles_arr = $this->viewVehicleByRoute($data);
        $isCheckModuleApp = $vehicles_arr['isCheckModuleApp'];

        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        // get all route of company
        if ($route_id > 0) {
            $title = 'BẢNG TỔNG HỢP DOANH THU VÉ XE THEO TUYẾN';
            $route = $this->routes->getRouteById($route_id, $company_id);
            $title_of_route =  $route ?  $route->name : '';
            $file_name = 'DoanhThuTuyen_' . date('Ymd');
        } else {
            $title = 'BẢNG TỔNG HỢP DOANH THU VÉ XE THEO TUYẾN';
            $title_of_route = 'Tất cả các tuyến xe buýt';
            $file_name = 'DoanhThuTatCaTuyen_' . date('Ymd');
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_left =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => $title_of_route
        ];

        if ($isCheckModuleApp) {

            //merges array
            $merges_arr = [];

            // table
            if ($route_id > 0) {
                //merges array
                $merges_arr = ['A1:E1', 'A2:E2', 'A4:R4', 'A5:R5', 'A6:R6', 'A8:A10', 'B8:E8', 'F8:N8', 'O8:Q8', 'R8:R10', 'B9:B10', 'C9:C10', 'D9:D10', 'E9:E10', 'F9:G9', 'H9:K9', 'L9:M9', 'O9:O10', 'P9:P10', 'Q9:Q10','S8:S10','T8:T10'];

                $a8_to_r11 = [
                    ['STT', 'THÔNG TIN TUYẾN', NULL, NULL, NULL, 'DOANH THU VÉ XE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TỔNG CỘNG', NULL, NULL, 'NẠP THẺ','THỜI GIAN ĐĂNG NHẬP','ĐI TỪ'],
                    [NULL, 'Số xe', 'Tài xế', 'Phụ xe', 'Tuyến', 'Vé lượt', NULL, 'Thẻ', NULL, NULL, NULL, 'Momo', NULL, 'Vé tháng', 'Tổng doanh thu', 'Tổng chiết khấu', 'Tổng thực thu'],
                    [NULL, NULL, NULL, NULL, NULL, 'SL', 'Doanh thu', 'SL', 'Doanh thu', 'Chiết khấu', 'Thực thu', 'SL', 'Doanh thu', 'Doanh thu', NULL, NULL, NULL, NULL],
                    ['A', 'B', 'C', 'D', 'E', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13','F','G'],
                ];

                $spread_sheet->getActiveSheet()->fromArray($a8_to_r11, NULL, 'A8');
                $spread_sheet->getActiveSheet()->getStyle('A8:T11')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 11;
                $cell_value = $lines + $cell;

                if (count($vehicles_arr['vehicles_arr']) > 0) {

                    foreach ($vehicles_arr['vehicles_arr'] as $vehicle) {

                        $vehicle = (array)$vehicle;
                        $cell_value = $lines + $cell;

                        if($vehicle['route_number'] != 'only'){

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plate']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['driver_name']);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['subdriver_name']);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['route_number']);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['count_ticket_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['total_price_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_price_discount']);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_price_collected']);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['count_ticket_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['total_price_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value) ->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['total_price_month']);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $vehicle['count_revenue_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $vehicle['count_discount_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $vehicle['count_collected_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $vehicle['total_price_deposit']);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $vehicle['started'] .' <=> '. $vehicle['ended']);
                            $spread_sheet->getActiveSheet()->getStyle('S')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $vehicle['station_name']);
                            $spread_sheet->getActiveSheet()->getStyle('T')->applyFromArray($style_center);
                        }

                        if($vehicle['route_number'] == 'only'){
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                            $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['count_ticket_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['total_price_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_price_discount']);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_price_collected']);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['count_ticket_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['total_price_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['total_price_month']);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $vehicle['count_revenue_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $vehicle['count_discount_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $vehicle['count_collected_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $vehicle['total_price_deposit']);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_bold);
                        }

                        $lines++;
                    }

                    // row last and sum price
                    // $cell_value = $lines + $cell;
                    // $sum_from = $cell + 1;
                    // $sum_to = $cell_value - 1;

                    // $last_row = [
                    //     [
                    //         '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                    //         '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                    //         '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                    //         '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                    //         '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                    //         '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                    //         '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                    //         '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                    //         '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                    //         '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                    //         '=SUM(P' . $sum_from . ':P' . $sum_to . ')',
                    //         '=SUM(Q' . $sum_from . ':Q' . $sum_to . ')',
                    //         '=SUM(R' . $sum_from . ':R' . $sum_to . ')',
                    //     ],
                    // ];
                    // $spread_sheet->getActiveSheet()->getStyle('F')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->getStyle('H')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->getStyle('L')->applyFromArray($style_center);

                    // $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'F' . $cell_value);
                    // $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value . ':R' . $cell_value)->getFont()->setBold(true);
                    // $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value . ':R' . $cell_value)->getNumberFormat()
                    //     ->setFormatCode('#,##0');
                }

                //merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicle($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp, 'only');
            }else {

                //merges array
                $merges_arr = ['A1:E1', 'A2:E2', 'A4:O4', 'A5:O5', 'A6:O6', 'A8:A10', 'B8:B10', 'C8:K8', 'L8:N8', 'O8:O10', 'C9:D9', 'E9:H9', 'I9:J9', 'L9:L10', 'M9:M10', 'N9:N10'];

                $a8_to_o11 = [
                    ['STT', 'TUYẾN', 'DOANH THU VÉ XE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TỔNG CỘNG', NULL, NULL, 'NẠP THẺ'],
                    [NULL, NULL, 'Vé lượt', NULL, 'Quẹt thẻ', NULL, NULL, NULL, 'Momo', NULL, 'Vé tháng', 'Tổng doanh thu', 'Tổng chiết khấu', 'Tổng thực thu'],
                    [NULL, NULL, 'SL', 'Doanh thu', 'SL', 'Doanh thu', 'Chiết khấu', 'Thực thu', 'SL', 'Doanh thu', 'Doanh thu', NULL, NULL, NULL, NULL],
                    ['A', 'B', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13'],
                ];

                $spread_sheet->getActiveSheet()->fromArray($a8_to_o11, NULL, 'A8');
                $spread_sheet->getActiveSheet()->getStyle('A8:O11')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 11;
                $cell_value = $lines + $cell;

                if (count($vehicles_arr['vehicles_arr']) > 0) {

                    foreach ($vehicles_arr['vehicles_arr'] as $vehicle) {

                        $vehicle = (array)$vehicle;
                        $cell_value = $lines + $cell;

                        if($vehicle['route_number'] != 'all'){

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['route_number']);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['count_ticket_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['total_price_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_discount']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['total_price_collected']);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['count_ticket_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_price_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_price_month']);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['count_revenue_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['count_discount_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['count_collected_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $vehicle['total_price_deposit']);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        }

                        if($vehicle['route_number'] == 'all'){

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                            $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['count_ticket_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['total_price_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_discount']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['total_price_collected']);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['count_ticket_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_price_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_price_month']);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['count_revenue_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['count_discount_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['count_collected_ticket']);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $vehicle['total_price_deposit']);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_bold);
                        }

                        $lines++;
                    }

                    // // row last and sum price
                    // $cell_value = $lines + $cell;
                    // $sum_from = $cell + 1;
                    // $sum_to = $cell_value - 1;

                    // $last_row = [
                    //     [
                    //         '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                    //         '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                    //         '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                    //         '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                    //         '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                    //         '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                    //         '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                    //         '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                    //         '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                    //         '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                    //         '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                    //         '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                    //         '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                    //     ],
                    // ];
                    // $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->getStyle('E')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->getStyle('I')->applyFromArray($style_center);

                    // $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                    // $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':O' . $cell_value)->getFont()->setBold(true);
                    // $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':O' . $cell_value)->getNumberFormat()
                    //     ->setFormatCode('#,##0');
                }

                //merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicle($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp, "all");
            }

        } else {

            if ($route_id > 0) {

                //merges array
                $merges_arr = ['A1:D1', 'A2:D2', 'A4:G4', 'A5:G5', 'A6:G6'];

                // table
                $a8_to_g9 = [
                    // ['STT', 'THÔNG TIN TUYẾN', NULL, NULL, NULL, 'DOANH THU VÉ XE', NULL, NULL, 'TỔNG CỘNG'],
                    ['STT', 'Số xe', "Tài xế", 'Phụ xe', 'Tuyến', 'Số lượng vé', 'Doanh thu'],
                    ['A', 'B', 'C', 'D', 'E', '1', '2'],
                ];
                $spread_sheet->getActiveSheet()->fromArray($a8_to_g9, NULL, 'A8');
                $spread_sheet->getActiveSheet()->getStyle('A8:G9')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 9;
                $cell_value = $lines + $cell;

                if (count($vehicles_arr['vehicles_arr']) > 0) {

                    foreach ($vehicles_arr['vehicles_arr'] as $vehicle) {

                        $vehicle = (array)$vehicle;
                        $cell_value = $lines + $cell;

                        if($vehicle['route_name'] != 'only'){
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plate']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['driver_name']);
                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['subdriver_name']);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['route_name']);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)
                                ->getNumberFormat()->setFormatCode('#,##0');
                        }

                        if($vehicle['route_name'] == 'only'){

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                            $spread_sheet->getActiveSheet()->mergeCells('A'. $cell_value.':E'.$cell_value);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold);
                        }

                        $lines++;
                    }

                    // // row last and sum price
                    // $cell_value = $lines + $cell;
                    // $sum_from = $cell + 1;
                    // $sum_to = $cell_value - 1;

                    // $last_row = [
                    //     [
                    //         '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                    //         '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                    //     ],
                    // ];
                    // $spread_sheet->getActiveSheet()->getStyle('F')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'F' . $cell_value);
                    // $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value . ':G' . $cell_value)->getFont()->setBold(true);
                    // $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value . ':G' . $cell_value)->getNumberFormat()
                    //     ->setFormatCode('#,##0');
                }

                //merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicle($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp, 'only');
            }
            else {

                //merges array
                $merges_arr = ['A1:C1', 'A2:C2', 'A4:D4', 'A5:D5', 'A6:D6'];

                // table
                $a8_to_d9 = [
                    ['STT', 'Tuyến', 'Số lượng vé', 'Doanh thu'],
                    ['A', 'B', '1', '2'],
                ];
                $spread_sheet->getActiveSheet()->fromArray($a8_to_d9, NULL, 'A8');
                $spread_sheet->getActiveSheet()->getStyle('A8:D9')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 9;
                $cell_value = $lines + $cell;

                if (count($vehicles_arr['vehicles_arr']) > 0) {

                    foreach ($vehicles_arr['vehicles_arr'] as $vehicle) {

                        $vehicle = (array)$vehicle;
                        $cell_value = $lines + $cell;

                        if($vehicle['route_name'] != 'all'){
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['route_name']);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)
                                ->getNumberFormat()->setFormatCode('#,##0');
                        }

                        if($vehicle['route_name'] == 'all'){

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                            $spread_sheet->getActiveSheet()->mergeCells('A'. $cell_value.':B'.$cell_value);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_ticket_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_price_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_bold);
                        }

                        $lines++;
                    }

                    // // row last and sum price
                    // $cell_value = $lines + $cell;
                    // $sum_from = $cell + 1;
                    // $sum_to = $cell_value - 1;

                    // $last_row = [
                    //     [
                    //         '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                    //         '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                    //     ],
                    // ];
                    // $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center);
                    // $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                    // $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':D' . $cell_value)->getFont()->setBold(true);
                    // $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':D' . $cell_value)->getNumberFormat()
                    //     ->setFormatCode('#,##0');
                }

                // merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicle($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp, 'all');
            }
        }
    }

    public function exportVehicleAll($data)
    {

        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];
        $isCheckModuleApp = $this->isCheckModuleApp($company_id);

        if ($data) {

            $vehicle_arr = $this->getReportVehicleAllByData($data);

            $company_name = '';
            $company_address = '';

            // date
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));

            // get company
            $company = $this->companies->getCompanyById($company_id);

            if ($company) {
                $company_name = mb_strtoupper($company->fullname, "UTF-8");
                $company_address = mb_strtoupper($company->address, "UTF-8");
            }

            $title = '';
            if (!$isCheckModuleApp) {
                $title = 'BẢNG KÊ DOANH THU BÁN VÉ BUS TOUR TRỰC TIẾP TRÊN XE';
            } else {
                $title = 'BẢNG TỔNG HỢP DOANH THU TOÀN BỘ XE BUÝT';
            }

            $file_name = 'DoanhThuToanBoXeBuyt_' . date('Ymd');

            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            //header excel
            $header_excel = [
                'com_name' => $company_name,
                'com_addr' => $company_address,
                'title' => $title,
                'quarter' => $report_date,
            ];

            if (!$isCheckModuleApp) {
                //merges array
                $merges_arr = ['A1:D1', 'A2:D2', 'A4:F4', 'A5:F5'];

                // table
                $a7_to_e8 = [
                    ['STT', 'SỐ XE', 'SỐ LƯỢNG', 'DOANH THU', 'KÝ NỘP', 'HỌ TÊN'],
                    ['A', 'B', '1', '2', 'C', 'D']
                ];
                $spread_sheet->getActiveSheet()->fromArray($a7_to_e8, NULL, 'A7');
                $spread_sheet->getActiveSheet()->getStyle('A7:F8')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 8;
                $cell_value = $lines + $cell;

                if (count($vehicle_arr) > 0) {

                    foreach ($vehicle_arr as $vehicle) {

                        $cell_value = $lines + $cell;

                        if($vehicle['license_plates'] !== 'all') {
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plates']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_tickets_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, '');
                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, '');
                        } else {
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plates']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['sum_count_tickets_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['sum_total_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, '');
                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, '');
                        }
                        $lines++;
                    }
                }

                //merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicleAll($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp);
            } else {

                //merges array
                $merges_arr = ['A7:A9','B7:B9','C7:J7','K7:N7','C8:D8','E8:G8','H8:I8','K8:K9','L8:L9','M8:M9','N8:N9'];

                // table
                $a7_to_n9 = [
                    ['STT','Biển số xe','DOANH THU VÉ XE',null,null,null,null,null,null,null,'TỔNG CỘNG'],
                    [null,null,'Vé lượt',null,'Vé trả trước',null,null,'Momo',null,'Vé tháng','Số lượt đi','Số lượng vé','Tổng doanh thu','Tổng thực thu'],
                    [null,null,'SL vé','Doanh thu','SL vé','Doanh thu','Thực thu','SL vé','Doanh thu','Số lượt đi']
                ];
                $spread_sheet->getActiveSheet()->fromArray($a7_to_n9, NULL, 'A7');
                $spread_sheet->getActiveSheet()->getStyle('A7:N9')->applyFromArray($style_center_bold);

                // set data
                $lines = 1;
                $cell = 9;
                $cell_value = $lines + $cell;

                if (count($vehicle_arr) > 0) {
                    foreach ($vehicle_arr as $vehicle) {

                        $cell_value = $lines + $cell;

                        if($vehicle['license_plates'] !== 'all') {
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plates']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_tickets_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');;

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['count_tickets_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('E')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['total_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');;

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['total_receipts']);
                            $spread_sheet->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');;

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['count_tickets_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('H')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['total_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('#,##0');;

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_shift']);
                            $spread_sheet->getActiveSheet()->getStyle('J')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_shift_last']);
                            $spread_sheet->getActiveSheet()->getStyle('K')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['total_count_ticket_last']);
                            $spread_sheet->getActiveSheet()->getStyle('L')->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['total_revenue_last']);
                            $spread_sheet->getActiveSheet()->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');;

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['total_receipts_last']);
                            $spread_sheet->getActiveSheet()->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');;
                        }else {
                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['sum_count_tickets_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['sum_total_pos']);
                            $spread_sheet->getActiveSheet()->getStyle('D')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['sum_count_tickets_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('E')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['sum_total_charge']);
                            $spread_sheet->getActiveSheet()->getStyle('F')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['sum_total_receipts']);
                            $spread_sheet->getActiveSheet()->getStyle('G')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['sum_count_tickets_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('H')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['sum_total_qrcode']);
                            $spread_sheet->getActiveSheet()->getStyle('I')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['sum_total_shift']);
                            $spread_sheet->getActiveSheet()->getStyle('J')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['sum_total_shift_last']);
                            $spread_sheet->getActiveSheet()->getStyle('K')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['sum_total_count_ticket_last']);
                            $spread_sheet->getActiveSheet()->getStyle('L')->applyFromArray($style_center_bold);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['sum_total_revenue_last']);
                            $spread_sheet->getActiveSheet()->getStyle('M')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['sum_total_receipts_last']);
                            $spread_sheet->getActiveSheet()->getStyle('N')->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                        }
                        $lines++;
                    }
                }
                //merges
                foreach ($merges_arr as $merge) {
                    $spread_sheet->getActiveSheet()->mergeCells($merge);
                }

                // save spread sheet
                $this->downloadFileVehicleAll($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp);
            }
        }
    }

    public function exportVehicleByPeriod($data)
    {

        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];

        $isCheckModuleApp =  $this->isCheckModuleApp($company_id);
        $vehicle_period_arr = $this->getReportVehicleByPeriod($data);

        $company_name = '';
        $company_address = '';

        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $title = 'BẢNG TỔNG HỢP BÁN HÀNG DỊCH VỤ HOP ON - HOP OFF TRỰC TIẾP';
        $file_name = 'BaoCaoDoanhThuXeBuytTheoKy_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
        ];

        if (!$isCheckModuleApp) {
            //merges array
            $merges_arr = ['A1:D1', 'A2:D2', 'A4:I4', 'A5:I5', 'A7:A8', 'B7:B8', 'C7:D7', 'E7:F7', 'G7:H7', 'I7:I8'];

            // table
            $a7_to_i8 = [
                ['Stt', 'Biển số xe', 'KỲ NÀY', NULL, 'CÙNG KỲ TRƯỚC', NULL, 'SO SÁNH KỲ NÀY/KỲ TRƯỚC (%)', NULL, 'Ghi chú'],
                [NULL, NULL, 'Tổng số vé', 'Tổng doanh thu', 'Tổng số vé', 'Tổng doanh thu', 'Tổng số vé', 'Tổng doanh thu', NULL],
                ['A', 'B', '1', '2', '3', '4', '5', '6', 'C']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_i8, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:I9')->applyFromArray($style_center_bold);

            // set data
            $lines = 1;
            $cell = 9;
            $cell_value = $lines + $cell;

            if (count($vehicle_period_arr) > 0) {

                foreach ($vehicle_period_arr as $vehicle) {

                    $cell_value = $lines + $cell;
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plates']);
                    $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_tickets']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_pos']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['count_tickets_last']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['total_pos_last']);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    if ($vehicle['count_tickets_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['count_tickets_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['count_tickets_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($vehicle['total_pos_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['total_pos_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['total_pos_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);
                    }

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, '');

                    $lines++;
                }

                // row last and sum price
                $cell_value = $lines + $cell;
                $sum_from = $cell + 1;
                $sum_to = $cell_value - 1;

                $last_row = [
                    [
                        '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                        '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                        '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                        '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                        '=(ROUND(SUM(C' . $sum_from . ':C' . $sum_to . ')/SUM(E' . $sum_from . ':E' . $sum_to . ')*100, 0))%',
                        '=(ROUND(SUM(D' . $sum_from . ':D' . $sum_to . ')/SUM(F' . $sum_from . ':F' . $sum_to . ')*100, 0))%'
                    ],
                ];
                $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':H' . $cell_value)->getFont()->setBold(true);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':C' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value . ':E' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value . ':G' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value . ':H' . $cell_value)->applyFromArray($style_center);
            }

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // save spread sheet
            $this->downloadFileVehiclePeriod($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp);
        } else {

            //merges array
            $merges_arr = [
                'A1:F1', 'A2:F2', 'A4:O4', 'A5:O5', 'A7:A9', 'B7:B9', 'C7:F7', 'G7:J7', 'K7:N7',
                'C8:E8', 'F8:F9', 'G8:I8', 'J8:J9', 'K8:M8', 'N8:N9', 'O7:O9'
            ];

            // table
            $a7_to_09 = [
                ['Stt', 'Biển số xe', 'KỲ NÀY', NULL, NULL, NULL, 'CÙNG KỲ TRƯỚC', NULL, NULL, NULL, 'SO SÁNH KỲ NÀY/KỲ TRƯỚC (%)', NULL, NULL, NULL, 'Ghi chú'],
                [NULL, NULL, 'DOANH THU VÉ XE', NULL, NULL, 'Doanh thu', 'DOANH THU VÉ XE', NULL, NULL, 'Doanh thu', 'DOANH THU VÉ XE', NULL, NULL, 'Doanh thu', NULL],
                [NULL, NULL, 'Số lượng vé', 'Vé lượt', 'Quẹt thẻ', NULL, 'Số lượng vé', 'Vé lượt', 'Quẹt thẻ', NULL, 'Số lượng vé', 'Vé lượt', 'Quẹt thẻ', NULL, NULL],
                ['A', 'B', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'C']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_09, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:O10')->applyFromArray($style_center_bold);

            // set data
            $lines = 1;
            $cell = 10;
            $cell_value = $lines + $cell;

            if (count($vehicle_period_arr) > 0) {

                foreach ($vehicle_period_arr as $vehicle) {

                    $cell_value = $lines + $cell;
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vehicle['license_plates']);
                    $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vehicle['count_tickets']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vehicle['total_pos']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vehicle['total_charge']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vehicle['total_revenue']);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vehicle['count_tickets_last']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $vehicle['total_pos_last']);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $vehicle['total_charge_last']);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $vehicle['total_revenue_last']);
                    $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    if ($vehicle['count_tickets_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['count_tickets_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['count_tickets_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($vehicle['total_pos_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $vehicle['total_pos_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_pos_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($vehicle['total_charge_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $vehicle['total_charge_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_charge_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($vehicle['total_revenue_percent'] == '-') {
                        $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $vehicle['total_revenue_percent']);
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center);
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $vehicle['total_revenue_percent'] . '%');
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);
                    }

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, '');

                    $lines++;
                }

                // row last and sum price
                $cell_value = $lines + $cell;
                $sum_from = $cell + 1;
                $sum_to = $cell_value - 1;

                $last_row = [
                    [
                        '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                        '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                        '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                        '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                        '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                        '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                        '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                        '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                        '=(ROUND(SUM(C' . $sum_from . ':C' . $sum_to . ')/SUM(G' . $sum_from . ':G' . $sum_to . ')*100, 0))%',
                        '=(ROUND(SUM(D' . $sum_from . ':D' . $sum_to . ')/SUM(H' . $sum_from . ':H' . $sum_to . ')*100, 0))%',
                        '=(ROUND(SUM(E' . $sum_from . ':E' . $sum_to . ')/SUM(I' . $sum_from . ':I' . $sum_to . ')*100, 0))%',
                        '=(ROUND(SUM(F' . $sum_from . ':F' . $sum_to . ')/SUM(J' . $sum_from . ':J' . $sum_to . ')*100, 0))%'
                    ],
                ];
                $spread_sheet->getActiveSheet()->getStyle('C')->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':N' . $cell_value)->getFont()->setBold(true);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value . ':G' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value . ':K' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value . ':L' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value . ':M' . $cell_value)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value . ':N' . $cell_value)->applyFromArray($style_center);
            }
            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // save spread sheet
            $this->downloadFileVehiclePeriod($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $isCheckModuleApp);
        }
    }

    private function downloadFileVehicleRoutePeriod($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $data)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $setWidth = $data['object_compare'] !== 'all' ? 12 : 17;
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('R')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('S')->setWidth($setWidth);
        $spread_sheet->getActiveSheet()->getColumnDimension('T')->setWidth($setWidth);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $title = $data['object_compare'] !== 'all' ? 'J4' : 'F4';
        $spread_sheet->getActiveSheet()->setCellValue($title, $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle($title)->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle($title)->applyFromArray($style_center_bold);

        $quarter = $data['object_compare'] !== 'all' ? 'J5' : 'F5';
        $spread_sheet->getActiveSheet()->setCellValue($quarter, $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle($quarter)->applyFromArray($style_center);

        //------------set border
        $all_border = $data['object_compare'] !== 'all' ? 'A7:T' : 'A7:K';
        $spread_sheet->getActiveSheet()->getStyle($all_border . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $tbdh = $data['object_compare'] !== 'all' ? ['R',':T'] : ['I',':K'];

        $spread_sheet->getActiveSheet()->setCellValue($tbdh[0] . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($tbdh[0] . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($tbdh[0] . $last_cell . $tbdh[1] . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, 'PHÒNG KÉ TOÁN');
        // $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold);
        // $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':I' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($tbdh[0] . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle($tbdh[0] . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($tbdh[0] . $last_cell . $tbdh[1] . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, '(Ký, họ tên)');
        // $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center);
        // $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':I' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($tbdh[0] . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($tbdh[0] . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($tbdh[0] . $last_cell . $tbdh[1] . $last_cell);


        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function exportTransactionOnline($data)
    {

        $company_id = $data['company_id'];

        $transaction_arr = $this->viewTransactionOnline($data);

        $company_name = '';
        $company_address = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        // path root
        $file_name = 'BaoCaoGiaoDichOnline_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BÁO CÁO GIAO DỊCH ONLINE',
            'quarter' => 'Từ ngày ' . date("d/m/Y", strtotime($data['from_date'])) . " đến ngày " . date("d/m/Y", strtotime($data['to_date']))
        ];

        if ($transaction_arr['isCheckModuleApp']) {
            //merges array
            $merges_arr = ['A1:C1', 'A2:C2', 'A4:F4', 'A5:F5'];

            // table
            $a7_to_f7  = [];

            if ($data['type'] == 'payment') {
                $a7_to_f7 = [
                    ['STT', 'HÌNH THỨC', 'MÃ ĐƠN HÀNG', 'SỐ TIỀN', 'NGÀY GIAO DỊCH', 'TRẠNG THÁI'],
                ];
            }

            if ($data['type'] == 'topup') {
                $a7_to_f7 = [
                    ['STT', 'HÌNH THỨC', 'MÃ GIAO DỊCH', 'SỐ TIỀN', 'NGÀY GIAO DỊCH', 'TRẠNG THÁI'],
                ];
            }

            $spread_sheet->getActiveSheet()->fromArray($a7_to_f7, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_all_borders);

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // set data
            $lines = 1;
            $cell = 7;
            $cell_value = $lines + $cell;

            if (count($transaction_arr['transactions']) > 0) {

                foreach ($transaction_arr['transactions'] as $transaction) {

                    $cell_value = $lines + $cell;

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    if ($transaction->type == 'qrcode' || $transaction->type == 'qrcode_taxi') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Thanh toán online');
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'qrcode' || $transaction->type == 'qrcode_taxi') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Nạp tiền online');
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);
                    }

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $transaction->transaction_code);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $transaction->amount);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $transaction->activated);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, 'Thành công');
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);
                    $lines++;
                }
            }
            // save spread sheet
            $this->downloadFileTransactionOnline($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Giao dịch online', $transaction_arr['total_onlines']);
        } else {

            //code..
        }
    }

    public function exportDetailTransactionSearch($data)
    {

        $transactions = $this->viewDetailTransactionSearch($data);
        $company_id = $data['company_id'];
        $company_name = '';
        $company_address = '';
        $isCheckModuleApp = $transactions['isCheckModuleApp'];

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        // path root
        $file_name = 'BaoCaoChiTietGiaoDich_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BÁO CÁO CHI TIẾT GIAO DỊCH',
            'quarter' => 'Từ ngày ' . date("d/m/Y", strtotime($data['date_from'])) . " đến ngày " . date("d/m/Y", strtotime($data['date_to']))
        ];

        if ($isCheckModuleApp) {
            //merges array
            $merges_arr = ['A1:C1', 'A2:C2', 'A4:H4', 'A5:H5'];

            // table
            $a7_to_h7 = [
                ['STT', 'LOẠI VÉ', 'MÃ SỐ VÉ', 'GIÁ VÉ (VNĐ)', 'CHIẾT KHẤU', 'THỰC THU',  'TẠO LÚC', 'BIỂN SỐ XE'],
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_h7, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:H7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A7:H7')->applyFromArray($style_all_borders);

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }
            // set data
            $lines = 1;
            $cell = 7;
            $cell_value = $lines + $cell;

            if (count($transactions) > 0) {
                $sumary_money_all = 0;
                foreach ($transactions['transactions'] as $transaction) {

                    $cell_value = $lines + $cell;

                    $spread_sheet->getActiveSheet()->getStyle('A7:H7')->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    if ($transaction->type == 'pos') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tiền mặt');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'pos_taxi') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tiền mặt taxi');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'app:1' || $transaction->type == 'qrcode') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Online');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'qrcode_taxi') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Online taxi');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'charge') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Quẹt thẻ');
                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, ($transaction->ticket_price) - ($transaction->amount));
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    if ($transaction->type == 'charge_taxi') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Quẹt thẻ taxi');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    if ($transaction->type == 'charge_free') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Miễn phí');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    if ($transaction->type == 'deposit') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Nạp thẻ NFC');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }

                    if ($transaction->type == 'topup_momo') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Nạp thẻ ví momo');
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }
                    $sumary_money_all += $transaction->amount;
                    $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $transaction->ticket_number);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $transaction->ticket_price);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $transaction->amount);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $transaction->activated);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $transaction->license_plates);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);
                    $lines++;
                }
                $sumary_all = $transactions['pos']['number'] + $transactions['pos_taxi']['number'] + $transactions['online']['number'] + $transactions['online_taxi']['number'] + $transactions['charge']['number'] + $transactions['charge_taxi']['number'] + $transactions['charge_free']['number'] + $transactions['deposit']['number'] + $transactions['topup_momo']['number'];

                $data_sumary = array(
                    "pos_num" =>   $transactions['pos']['number'],
                    "pos_taxi_num" => $transactions['pos_taxi']['number'],
                    "online_num" =>  $transactions['online']['number'],
                    "online_taxi_num" =>  $transactions['online_taxi']['number'],
                    "charge_num" =>  $transactions['charge']['number'],
                    "charge_taxi_num" =>  $transactions['charge_taxi']['number'],
                    "charge_free_num" =>  $transactions['charge_free']['number'],
                    "deposit_num" =>  $transactions['deposit']['number'],
                    "topup_momo_num" =>  $transactions['topup_momo']['number'],
                    "pos" => $transactions['pos']['total'],
                    "pos_taxi" => $transactions['pos_taxi']['total'],
                    "online" =>  $transactions['online']['total'],
                    "online_taxi" =>  $transactions['online_taxi']['total'],
                    "charge" => $transactions['charge']['total'],
                    "charge_taxi" => $transactions['charge_taxi']['total'],
                    "charge_free" => $transactions['charge_free']['total'],
                    "deposit" =>   $transactions['deposit']['total'],
                    "topup_momo" =>   $transactions['topup_momo']['total'],
                    "sumary_all" => $sumary_all,
                    "sumary_money_all" => $sumary_money_all,
                    "total_transactions" => $transactions['total_transactions']
                );
            }
                // return $data_sumary;
            // save spread sheet
            $this->downloadFileTransaction($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Chi tiết giao dịch', 'by_date', $data_sumary,  $isCheckModuleApp);
        } else {
            //merges array
            $merges_arr = ['A1:C1', 'A2:C2', 'A4:F4', 'A5:F5'];

            // table
            $a7_to_f7 = [
                ['STT', 'LOẠI VÉ', 'MÃ SỐ VÉ', 'SỐ TIỀN (VNĐ)', 'TẠO LÚC', 'BIỂN SỐ XE'],
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_f7, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_all_borders);

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            // set data
            $lines = 1;
            $cell = 7;
            $cell_value = $lines + $cell;

            if (count($transactions) > 0) {
                $sumary_money_all = 0;
                foreach ($transactions['transactions'] as $transaction) {

                    $cell_value = $lines + $cell;

                    $spread_sheet->getActiveSheet()->getStyle('A7:F7')->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    if ($transaction->type == 'pos') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tiền mặt');
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);
                    }
                    if ($transaction->type == 'app:1') {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Online');
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);
                    }

                    $sumary_money_all += $transaction->amount;

                    $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $transaction->ticket_number);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $transaction->amount);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $transaction->activated);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $transaction->license_plates);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                    $lines++;
                }

                $sumary_all = $transactions['pos']['number'] + $transactions['online']['number'];

                $data_sumary = array(
                    "pos_num" =>   $transactions['pos']['number'],
                    "online_num" =>  $transactions['online']['number'],
                    "pos" => $transactions['pos']['total'],
                    "online" =>  $transactions['online']['total'],
                    "sumary_all" => $sumary_all,
                    "sumary_money_all" => $sumary_money_all
                );
            }
            // save spread sheet
            $this->downloadFileTransaction($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Chi tiết giao dịch', 'by_date', $data_sumary, $isCheckModuleApp);
        }
    }

    public function exportCardMonthForGeneral($data)
    {

        $barcode = $data['barcode'] ?? null;
        $company_id = $data['company_id'];
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        if ($data) {
            $cardMonthForGeneral = $this->viewCardMonthForGeneral($data);

            $company_name = '';
            $company_address = '';
            // date
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
            // get company
            $company = $this->companies->getCompanyById($company_id);
            if ($company) {
                $company_name = mb_strtoupper($company->fullname, "UTF-8");
                $company_address = mb_strtoupper($company->address, "UTF-8");
            }

            $title = 'BÁO CÁO TỔNG HỢP VÉ THÁNG';
            $file_name = 'Thongketonghopvethang' . date('Ymd');

            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];
            $style_left =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $header_excel = [
                'com_name' => $company_name,
                'com_addr' => $company_address,
                'title' => $title,
                'quarter' => $report_date,
            ];
            //merges array
            $merges_arr = ['A1:C1', 'A2:C2', 'A4:E4', 'A5:E5'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }
            $a7_to_e7 = [
                ['Số', 'Tên khách hàng', 'Mã thẻ', 'Số tiền đã nạp', 'Hạn sử dụng']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_e7, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:E7')->applyFromArray($style_center_bold);

            $lines = 1;
            $cell = 7;
            $cell_value = $lines + $cell;

            if (count($cardMonthForGeneral) > 0) {
                foreach ($cardMonthForGeneral as $vl) {
                    $vl = (object) $vl;
                    $cell_value =  $lines + $cell;

                    if (!empty($vl->fullname) && !empty($vl->barcode) && !empty($vl->total_amount) && !empty($vl->expiration_date)) {
                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vl->fullname);
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_left);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vl->barcode);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vl->total_amount);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vl->expiration_date);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);
                    }
                    if (!empty($vl->total_last)) {
                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, "Tổng");
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':C' . $cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vl->total_last);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_bold);
                    }
                    $lines++;
                }
            }

            // save spread sheet
            $this->downloadFileCardMonthForGeneral($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh thu tong ve thang');
        }
    }

    public function exportCardMonthForRevenue($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $route_id = (int)$data['route_id'] ?? 0;

        $company_name = '';
        $company_address = '';

        $cardMonthRevenues = [];

        // date
        if ($from_date) {
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        } else {
            $report_date = 'Tất cả thời gian';
        }

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $title = 'BÁO CÁO DOANH THU VÉ THÁNG';
        $file_name = 'DoanhThuVeThang_' . date('Ymd');

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_left =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $route_title = 'Tất cả các tuyến';
        if ($route_id > 0) {
            $route = $this->routes->getRouteById($route_id);
            if ($route) $route_title = 'Tuyến : ' . $route->number;
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => $route_title
        ];

        // set data
        $lines = 1;
        $cell = 8;
        $cell_value = $lines + $cell;

        $cardMonthRevenues =  $this->viewCardMonthForRevenue($data);

        if ($route_id == 0) {
            // table
            $a8_to_f8 = [
                ['STT', 'Tuyến', 'Số tiền đã nộp', 'Số lượt đi theo tuyến', 'Số lượt đi tổng', 'Doanh thu'],
            ];

            //merges array
            $merges_arr = ['A1:D1', 'A2:D2', 'A4:F4', 'A5:F5', 'A6:F6'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $spread_sheet->getActiveSheet()->fromArray($a8_to_f8, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:F8')->applyFromArray($style_center_bold);

            if (count($cardMonthRevenues) > 0) {
                foreach ($cardMonthRevenues as $vl) {
                    $cell_value = $lines + $cell;

                    if ($vl->route_number != 'all') {

                        $spread_sheet->getActiveSheet()->setCellValue('A'.$cell_value, $lines);
                        $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B'.$cell_value, $vl->route_number);
                        $spread_sheet->getActiveSheet()->getStyle('B'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('C'.$cell_value, $vl->price);
                        $spread_sheet->getActiveSheet()->getStyle('C'.$cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('D'.$cell_value, $vl->count_number_only);
                        $spread_sheet->getActiveSheet()->getStyle('D'.$cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E'.$cell_value, $vl->count_number_all);
                        $spread_sheet->getActiveSheet()->getStyle('E'.$cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F'.$cell_value, $vl->revenue);
                        $spread_sheet->getActiveSheet()->getStyle('F'.$cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    if ($vl->route_number == 'all') {

                        $spread_sheet->getActiveSheet()->setCellValue('A'.$cell_value, 'Tổng cộng toàn tuyến');
                        $spread_sheet->getActiveSheet()->mergeCells('A'.$cell_value.':B'.$cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value.':B'.$cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('C'.$cell_value, $vl->price);
                        $spread_sheet->getActiveSheet()->getStyle('C'.$cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('C'.$cell_value)->applyFromArray($style_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('D'.$cell_value, $vl->count_number_only);
                        $spread_sheet->getActiveSheet()->getStyle('D'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E'.$cell_value, $vl->count_number_all);
                        $spread_sheet->getActiveSheet()->getStyle('E'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F'.$cell_value, $vl->revenue);
                        $spread_sheet->getActiveSheet()->getStyle('F'.$cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                        $spread_sheet->getActiveSheet()->getStyle('F'.$cell_value)->applyFromArray($style_bold);
                    }

                    $lines++;
                }
            }
        }

        if ($route_id > 0) {

            // table
            $a8_to_j8 = [
                ['STT', 'Tuyến', 'Chặng', 'Tên khách hàng', 'Mã thẻ', 'Mã vé', 'Số tiền đã nộp', 'Số lượt đi theo tuyến', 'Số lượt đi tổng', 'Doanh thu'],
            ];

            //merges array
            $merges_arr = ['A1:E1', 'A2:E2', 'A4:J4', 'A5:J5', 'A6:J6'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $spread_sheet->getActiveSheet()->fromArray($a8_to_j8, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:J8')->applyFromArray($style_center_bold);

            if (count($cardMonthRevenues) > 0) {

                foreach ($cardMonthRevenues as $vl) {

                    $cell_value = $lines + $cell;

                    if (!empty($vl->station_data) && !empty($vl->fullname) && !empty($vl->barcode) && !empty($vl->ticket_number)) {
                        $spread_sheet->getActiveSheet()->setCellValue('A'.$cell_value, $lines);
                        $spread_sheet->getActiveSheet()->getStyle('A'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B'.$cell_value, $vl->route_number);
                        $spread_sheet->getActiveSheet()->getStyle('B'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('C'.$cell_value, $vl->station_data);
                        $spread_sheet->getActiveSheet()->getStyle('C'.$cell_value)->applyFromArray($style_left);

                        $spread_sheet->getActiveSheet()->setCellValue('D'.$cell_value, $vl->fullname);
                        $spread_sheet->getActiveSheet()->getStyle('D'.$cell_value)->applyFromArray($style_left);

                        $spread_sheet->getActiveSheet()->setCellValue('E'.$cell_value, $vl->barcode);
                        $spread_sheet->getActiveSheet()->getStyle('E'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('F'.$cell_value, $vl->ticket_number);
                        $spread_sheet->getActiveSheet()->getStyle('F'.$cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('G'.$cell_value, $vl->price);
                        $spread_sheet->getActiveSheet()->getStyle('G'.$cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H'.$cell_value, $vl->count_number_only);
                        $spread_sheet->getActiveSheet()->getStyle('H'.$cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I'.$cell_value, $vl->count_number_all);
                        $spread_sheet->getActiveSheet()->getStyle('I'.$cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J'.$cell_value, $vl->revenue);
                        $spread_sheet->getActiveSheet()->getStyle('J'.$cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    if (empty($vl->station_data) && empty($vl->fullname) && empty($vl->barcode) && empty($vl->ticket_number) && $vl->route_number != 'all') {

                        $spread_sheet->getActiveSheet()->setCellValue('B'.$cell_value, 'Tổng cộng tuyến số '.$vl->route_number);
                        $spread_sheet->getActiveSheet()->mergeCells('B'.$cell_value.':F'.$cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('B'.$cell_value.':F'.$cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('G'.$cell_value, $vl->price);
                        $spread_sheet->getActiveSheet()->getStyle('G'.$cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('G'.$cell_value)->applyFromArray($style_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('H'.$cell_value, $vl->count_number_only);
                        $spread_sheet->getActiveSheet()->getStyle('H'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I'.$cell_value, $vl->count_number_all);
                        $spread_sheet->getActiveSheet()->getStyle('I'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J'.$cell_value, $vl->revenue);
                        $spread_sheet->getActiveSheet()->getStyle('J'.$cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                        $spread_sheet->getActiveSheet()->getStyle('J'.$cell_value)->applyFromArray($style_bold);

                    }

                    if($vl->route_number == 'all'){

                        $spread_sheet->getActiveSheet()->setCellValue('B'.$cell_value, 'Tổng cộng toàn tuyến');
                        $spread_sheet->getActiveSheet()->mergeCells('B'.$cell_value.':F'.$cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('B'.$cell_value.':F'.$cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('G'.$cell_value, $vl->price);
                        $spread_sheet->getActiveSheet()->getStyle('G'.$cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('G'.$cell_value)->applyFromArray($style_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('H'.$cell_value, $vl->count_number_only);
                        $spread_sheet->getActiveSheet()->getStyle('H'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I'.$cell_value, $vl->count_number_all);
                        $spread_sheet->getActiveSheet()->getStyle('I'.$cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J'.$cell_value, $vl->revenue);
                        $spread_sheet->getActiveSheet()->getStyle('J'.$cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                        $spread_sheet->getActiveSheet()->getStyle('J'.$cell_value)->applyFromArray($style_bold);
                    }

                    $lines++;
                }
            }
        }

        // save spread sheet
        $this->downloadFileCardMonth($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh thu ve thang', $route_id);
    }

    public function exportCardMonthByStaff($data)
    {
        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'] ?? 0;
        $user_id = (int) $data['user_id'] ?? 0;
        $barcode = $data['barcode'] ?? null;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        if ($data) {
            $cardMonthByStaff = $this->viewCardMonthByStaff($data);
            $company_name = '';
            $company_address = '';
            // date
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
            // get company
            $company = $this->companies->getCompanyById($company_id);
            if ($company) {
                $company_name = mb_strtoupper($company->fullname, "UTF-8");
                $company_address = mb_strtoupper($company->address, "UTF-8");
            }
            $route_title = 'Tất cả các tuyến';
            if ($route_id > 0) {
                $route = $this->routes->getRouteById($route_id);
                if ($route) {
                    $route_title = 'Tuyến : ' . $route->number;
                }
            }

            $title = 'BÁO CÁO THỐNG KÊ VÉ THÁNG THEO NHÂN VIÊN';
            $file_name = 'Thongkevethangtheonhanvien_' . date('Ymd');

            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $header_excel = [
                'com_name' => $company_name,
                'com_addr' => $company_address,
                'title' => $title,
                'quarter' => $report_date,
                'route_title' => $route_title
            ];
            //merges array
            $merges_arr = ['A1:D1', 'A2:D2', 'A4:G4', 'A5:G5', 'A6:G6'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }
            $a8_to_g8 = [
                ['Số', 'Họ tên', 'Chức vụ', 'Tuyến', 'Mã thẻ', 'Lượt đi', 'Doanh thu']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a8_to_g8, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:G8')->applyFromArray($style_center_bold);

            $lines = 1;
            $cell = 8;
            $cell_value = $lines + $cell;

            if (count($cardMonthByStaff) > 0) {
                foreach ($cardMonthByStaff as $vl) {
                    $cell_value =  $lines + $cell;

                    if (!empty($vl->fullname) && !empty($vl->barcode) && !empty($vl->route_number)) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $vl->fullname);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vl->role_name);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vl->route_number);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vl->barcode);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vl->count_times);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vl->total_revenue);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    if (empty($vl->fullname) && empty($vl->barcode) && empty($vl->route_number) && $vl->role_name != 'all' && $vl->role_name != 'only') {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $vl->role_name);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':E' . $cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vl->count_times);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vl->total_revenue);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    if (empty($vl->fullname) && empty($vl->barcode) && empty($vl->route_number) && ($vl->role_name == 'all')) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng toàn tuyến');
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':E' . $cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vl->count_times);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vl->total_revenue);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    if (empty($vl->fullname) && empty($vl->barcode) && empty($vl->route_number) && ($vl->role_name == 'only')) {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':E' . $cell_value);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':E' . $cell_value)->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vl->count_times);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $vl->total_revenue);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    $lines++;
                }
            }

            // save spread sheet
            $this->downloadFileCardMonthByStaff($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh thu theo nhan vien');
        }
    }

    public function exportCardMonthByGroupBusStation($data)
    {

        $company_id = $data['company_id'];
        $user_id = (int) $data['user_id'] ?? 0;
        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ?  date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;

        $results = $this->viewCardMonthByGroupBusStation($data);

        $company_name = '';
        $company_address = '';
        // date
        $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $title = 'BÁO CÁO CHI TIẾT DOANH THU VÉ THÁNG THEO LỘ TRÌNH';
        $file_name = 'baocaochitietdoanhthuvethangtheolotrinh' . date('Ymd');

        $staff_title = '';
        if($user_id > 0){

            $user = $this->users->getUserByKey('id', $user_id, $company_id);
            $staff_title = $user ? 'Nhân viên: '.$user['fullname'] : '';
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'staff' => $staff_title
        ];
        //merges array
        $merges_arr = ['A1:D1', 'A2:D2', 'A4:F4', 'A5:F5', 'A6:F6'];

        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }
        $a8_to_f8 = [
            ['STT', 'LỘ TRÌNH', 'SỐ VÉ', 'MÃ HÓA ĐƠN', 'SỐ TIỀN (VNĐ)', 'TẠO LÚC']
        ];
        $spread_sheet->getActiveSheet()->fromArray($a8_to_f8, NULL, 'A8');
        $spread_sheet->getActiveSheet()->getStyle('A8:F8')->applyFromArray($style_center_bold);

        $lines = 1;
        $cell = 8;
        $cell_value = $lines + $cell;

        if (count($results) > 0) {
            foreach ($results as $vl) {
                $cell_value =  $lines + $cell;
                $vl = (object) $vl;
                if ((($vl->group_bus_station_id != -1) && ($vl->group_bus_station_id != 0)) || ($vl->group_bus_station_id == -2)) {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    $group_bus_station_name = '';
                    switch ($vl->amount) {
                        case 0:
                            $group_bus_station_name = $vl->group_bus_station_name.' (Ưu tiên)';
                            break;

                        case 420000:
                            $group_bus_station_name = $vl->group_bus_station_name.' (1 - 10 Km)';
                            break;

                        case 550000:
                            $group_bus_station_name = $vl->group_bus_station_name.' (1 - 20 Km)';
                            break;

                        case 650000:
                            $group_bus_station_name = $vl->group_bus_station_name.' (1 - 24 Km)';
                            break;

                        case 800000:
                            $group_bus_station_name = $vl->group_bus_station_name.' (1 - 40 Km)';
                            break;

                        case 1000000:
                            $group_bus_station_name = $vl->group_bus_station_name.' (1 - 60 Km)';
                            break;
                    }
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $group_bus_station_name);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vl->ticket_number);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $vl->order_code);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vl->amount);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $vl->activated);
                    $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);
                }

                if ($vl->group_bus_station_id == -1) {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng lộ trình '.$vl->group_bus_station_name);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':B' . $cell_value)->applyFromArray($style_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vl->count_ticket);
                    $spread_sheet->getActiveSheet()->mergeCells('C' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vl->total_amount);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                }

               if ($vl->group_bus_station_id == 0) {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':B' . $cell_value)->applyFromArray($style_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $vl->count_ticket);
                    $spread_sheet->getActiveSheet()->mergeCells('C' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $vl->total_amount);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                }

                $lines++;
            }
        }

        // save spread sheet
        $this->downloadFileCardMonthByGroupBusStation($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'CTDT ve thang theo lo trinh');
    }

    public function exportCard($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];

        if ($data) {

            $card_arr = $this->viewCard($data);

            $company_name = '';
            $company_address = '';

            // date
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));

            // get company
            $company = $this->companies->getCompanyById($company_id);
            if ($company) {
                $company_name = mb_strtoupper($company->fullname, "UTF-8");
                $company_address = mb_strtoupper($company->address, "UTF-8");
            }

            $title = 'BÁO CÁO DOANH THU THẺ';
            $file_name = 'DoanhThuThe_' . date('Ymd');

            // create and save excel
            $spread_sheet = new Spreadsheet();
            $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
            $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            //header excel
            $header_excel = [
                'com_name' => $company_name,
                'com_addr' => $company_address,
                'title' => $title,
                'quarter' => $report_date,
            ];
            //merges array
            $merges_arr = ['A1:D1', 'A2:D2', 'A4:K4', 'A5:K5'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }
            // table
            $a7_to_j7 = [
                ['STT', 'RFID', 'Mã thẻ', 'Loại thẻ', 'Họ tên', 'Điện thoại', 'Số dư đầu', 'Nạp trong kì', 'Số tiền đã sử dụng', 'Số dư cuối', 'Số dư trên thẻ']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_j7, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:K7')->applyFromArray($style_center_bold);

            // set data
            $lines = 1;
            $cell = 7;
            $cell_value = $lines + $cell;

            if (count($card_arr) > 0) {

                foreach ($card_arr['card_arr'] as $card) {

                    $cell_value =  $lines + $cell;

                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $card->rfid);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $card->barcode);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $card->membership_type);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $card->fullname);

                    $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $card->phone);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $card->total_balance_before);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $card->total_deposit_in);
                    $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $card->total_charge_in);
                    $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $card->total_balance_end);
                    $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $card->balance);
                    $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                    $lines++;
                }

                $cell_value = $cell_value + 1;

                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':F' . $cell_value);

                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $card_arr['total_memberships']['total_balance_before']);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $card_arr['total_memberships']['total_deposit_in']);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $card_arr['total_memberships']['total_charge_in']);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $card_arr['total_memberships']['total_balance_end']);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $card_arr['total_memberships']['total_balance']);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
            }
            // save spread sheet
            $this->downloadFileCard($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu The');
        }
    }

    public function exportTripTimes($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'];
        $user_id = (int) $data['user_id'];
        $type_opt = (int) $data['type_opt'];
        $position = $data['position'];

        $company_name = '';
        $company_address = '';

        $result_arr = [];

        // date
        if ($from_date) {
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        } else {
            $report_date = 'Tất cả thời gian';
        }

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $title = 'BÁO CÁO LƯỢT CHẠY TRONG THÁNG';
        $file_name = 'LuotChayTrongThang_' . date('Ymd');

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $route_title = 'Tất cả các tuyến';
        if ($route_id > 0) {
            $route = $this->routes->getRouteById($route_id);
            if ($route) $route_title = 'Tuyến : ' . $route->number;
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => $route_title
        ];

        // table
        if ($type_opt == 0) {

            $b8_to_d8 = [
                ['STT', 'Tuyến', 'Tổng số lượt chạy'],
            ];

            //merges array
            $merges_arr = ['B1:C1', 'B2:C2', 'B4:D4', 'B5:D5', 'B6:D6'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $spread_sheet->getActiveSheet()->fromArray($b8_to_d8, NULL, 'B8');
            $spread_sheet->getActiveSheet()->getStyle('B8:D8')->applyFromArray($style_center_bold);

            $lines = 1;
            $cell = 8;
            $cell_value = $lines + $cell;

            $time_trips = $this->viewTripTimes($data);

            if (count($time_trips) > 0) {

                foreach ($time_trips as $key => $value) {

                    $cell_value = $lines + $cell;

                    if (!empty($value->route_number)) {

                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $lines);
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, 'Tuyến ' . $value->route_number);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value->count_route_number);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    } else {
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng cộng ');
                        $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('B' . $cell_value . ':C' . $cell_value);

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value['total_route_number']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    $lines++;
                }
            }

            // save spread sheet
            $this->downloadFileTrip($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Luot Chay Trong Thang', $type_opt);
        }

        if ($type_opt == 1) {

            $a8_to_e8 = [
                ['STT', 'Tên nhân viên', 'Vị trí', 'Tuyến', 'Tổng số lượt chạy'],
            ];

            //merges array
            $merges_arr = ['A1:C1', 'A2:C2', 'A4:E4', 'A5:E5', 'A6:E6'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $spread_sheet->getActiveSheet()->fromArray($a8_to_e8, NULL, 'A8');
            $spread_sheet->getActiveSheet()->getStyle('A8:E8')->applyFromArray($style_center_bold);

            $lines = 1;
            $cell = 8;
            $cell_value = $lines + $cell;

            $time_trips = $this->viewTripTimes($data);

            foreach ($time_trips as $key => $value) {

                $cell_value = $lines + $cell;

                if (!empty($value->fullname)  && !empty($value->route_number)) {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $value->fullname);
                    $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value->position_name);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value->route_number);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value->count_route_number);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                }
                if (empty($value->fullname) && empty($value->route_number) && $value->position_name != 'all') {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $value->position_name);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':D' . $cell_value)->applyFromArray($style_center_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value->count_route_number);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                }
                if (empty($value->fullname) && empty($value->route_number) && $value->position_name == 'all') {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng toàn bộ');
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':D' . $cell_value)->applyFromArray($style_center_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value->count_route_number);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                }

                if (empty($value->fullname) && empty($value->route_number) && $user_id > 0) {

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $value->position_name);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':D' . $cell_value);
                    $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':D' . $cell_value)->applyFromArray($style_center_bold);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value->count_route_number);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                }

                $lines++;
            }

            // save spread sheet
            $this->downloadFileTrip($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Luot Chay Trong Thang', $type_opt);
        }
    }

    public function exportTripsTimeDetail($data)
    {

        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $route_id = (int) $data['route_id'];
        $user_id = (int) $data['user_id'];
        $type_opt = (int) $data['type_opt'];
        $position = $data['position'];
        $id_tmp = $data['id_tmp'];
        $route_name_tmp = $data['route_name_tmp'] ?? '';

        $company_name = '';
        $company_address = '';

        $result_arr = [];

        // date
        if ($from_date) {
            $report_date = 'Từ ngày ' . date("d/m/Y", strtotime($from_date)) . ' đến ngày ' . date("d/m/Y", strtotime($to_date));
        } else {
            $report_date = 'Tất cả thời gian';
        }

        $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
        $to_date = date("Y-m-d 23:59:59", strtotime($to_date));

        $title = 'CHO TIẾT LƯỢT CHẠY TRONG THÁNG';
        $file_name = 'ChitietLuotChayTrongThang_' . date('Ymd');

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'route' => 'Tuyến: '.$route_name_tmp
        ];

            $b8_to_e8 = [
                ['STT', 'Thời gian', 'Tên tuyến', 'Xe'],
            ];

            //merges array
            $merges_arr = ['B1:C1', 'B2:C2', 'B4:E4', 'B5:E5', 'B6:E6'];

            //merges
            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $spread_sheet->getActiveSheet()->fromArray($b8_to_e8, NULL, 'B8');
            $spread_sheet->getActiveSheet()->getStyle('B8:E8')->applyFromArray($style_center_bold);

            $lines = 1;
            $cell = 8;
            $cell_value = $lines + $cell;

            $time_trips = $this->viewTripTimes($data);

            if (count($time_trips) > 0) {

                $data_shifts = $time_trips[$id_tmp]->data_shift ?? [];
                if(count($data_shifts) > 0){

                    foreach ($data_shifts as $key => $value) {

                        $cell_value = $lines + $cell;

                                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $lines);
                                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value->date_details);
                                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value->route_name_details);
                                $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value->vehicle_details);
                                $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                        $lines++;
                    }
                }
            }

            // save spread sheet
            $this->downloadFileTripTimeDetail($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Chi Tiet Luot Chay Trong Thang');

    }

    public function exportTimeKeeping($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $position = $data['position'] ?? 'all';
        $company_id = (int) $data['company_id'];
        $key_role = '';
        $isCheckModuleApp = false;

        //get module app by company id
        $module_app_arr = $this->module_apps->getModuleKeyByCompanyId($company_id);
        if (in_array('the_tra_truoc', $module_app_arr) || in_array('the_km', $module_app_arr) || in_array('the_dong_gia', $module_app_arr)) {
            $isCheckModuleApp = true;
        }

        //get data
        $result_arr = $this->viewTimeKeeping($data);
        $number_day_in_month = cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($from_date)), date('Y',strtotime($to_date)));


        ////format excel general------------------
        $company_name = '';
        $company_address = '';
        // date
        $report_date = 'Tháng ' . date("m", strtotime($from_date)) . ' năm ' . date("Y", strtotime($to_date));
        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $denominator = 'Mẫu số: 01a-LĐTL';
        $promulgate = '(Ban hành theo QĐ số 15/2006/QĐ-BTC ngày 20/03/2006)';
        $title = 'BẢNG CHẤM CÔNG LÁI XE , NHÂN VIÊN PHỤC VỤ';
        $file_name = 'chamcongchonhanvien' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(11);
        $spread_sheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(5);

        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_left =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_left_bold =  [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
            'denominator' => $denominator,
            'promulgate' => $promulgate
        ];

        $lines = 1;
        $cell = 7;
        $cell_value = $lines + $cell;

        ///////////////////////////////----------------

        if ($number_day_in_month == 28){

            //merges array
            $merges_arr = ['A1:F1', 'A2:F2','S1:AE1','S2:AE2', 'A3:AE3', 'A4:AE4','A5:A6','B5:B6','C5:AD5','AE6:AE7'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $tmp_col_day = [null,null];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_day[] = $i;
                if($i == $number_day_in_month){
                    $tmp_col_day[] = 'Tổng cộng lượt chạy';
                }
            }

            $tmp_col_row_1_null_of_day = ['STT', 'Ngày', 'Ngày trong tháng'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_1_null_of_day[] = null;
            }

            $tmp_col_row_2_null_of_day =  ['A','Họ và tên'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_2_null_of_day[] = null;
            }

            $a5_to_ae7 = [
                $tmp_col_row_1_null_of_day,
                $tmp_col_day,
                $tmp_col_row_2_null_of_day
            ];

            $spread_sheet->getActiveSheet()->fromArray($a5_to_ae7, NULL, 'A5');
            $spread_sheet->getActiveSheet()->getStyle('A5:AE7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A5:AE7')->applyFromArray($style_all_borders);

            $spread_sheet->getActiveSheet()->getStyle('C6:AD6')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('A7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('B5:B7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        }

        if ($number_day_in_month == 29){

            //merges array
            $merges_arr = ['A1:F1', 'A2:F2','V1:AF1','V2:AF2', 'A3:AF3', 'A4:AF4','A5:A6','B5:B6','C5:AD5','AF6:AF7'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $tmp_col_day = [null,null];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_day[] = $i;
                if($i == $number_day_in_month){
                    $tmp_col_day[] = 'Tổng cộng lượt chạy';
                }
            }

            $tmp_col_row_1_null_of_day = ['STT', 'Ngày', 'Ngày trong tháng'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_1_null_of_day[] = null;
            }

            $tmp_col_row_2_null_of_day =  ['A','Họ và tên'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_2_null_of_day[] = null;
            }

            $a5_to_af7 = [
                $tmp_col_row_1_null_of_day,
                $tmp_col_day,
                $tmp_col_row_2_null_of_day
            ];

            $spread_sheet->getActiveSheet()->fromArray($a5_to_af7, NULL, 'A5');
            $spread_sheet->getActiveSheet()->getStyle('A5:AF7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A5:AF7')->applyFromArray($style_all_borders);

            $spread_sheet->getActiveSheet()->getStyle('C6:AE6')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('A7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('B5:B7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        }

        if ($number_day_in_month == 30){

            $merges_arr = ['A1:F1', 'A2:F2','V1:AG1','V2:AG2', 'A3:AG3', 'A4:AG4','A5:A6','B5:B6','C5:AD5','AG6:AG7'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $tmp_col_day = [null,null];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_day[] = $i;
                if($i == $number_day_in_month){
                    $tmp_col_day[] = 'Tổng cộng lượt chạy';
                }
            }

            $tmp_col_row_1_null_of_day = ['STT', 'Ngày', 'Ngày trong tháng'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_1_null_of_day[] = null;
            }

            $tmp_col_row_2_null_of_day =  ['A','Họ và tên'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_2_null_of_day[] = null;
            }

            $a5_to_ag7 = [
                $tmp_col_row_1_null_of_day,
                $tmp_col_day,
                $tmp_col_row_2_null_of_day
            ];

            $spread_sheet->getActiveSheet()->fromArray($a5_to_ag7, NULL, 'A5');
            $spread_sheet->getActiveSheet()->getStyle('A5:AG7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A5:AG7')->applyFromArray($style_all_borders);

            $spread_sheet->getActiveSheet()->getStyle('C6:AF6')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('A7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('B5:B7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        }

        if ($number_day_in_month == 31){
            //merges array
            $merges_arr = ['A1:F1', 'A2:F2','V1:AH1','V2:AH2', 'A3:AH3', 'A4:AH4','A5:A6','B5:B6','C5:AD5','AH6:AH7'];

            foreach ($merges_arr as $merge) {
                $spread_sheet->getActiveSheet()->mergeCells($merge);
            }

            $tmp_col_day = [null,null];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_day[] = $i;
                if($i == $number_day_in_month){
                    $tmp_col_day[] = 'Tổng cộng lượt chạy';
                }
            }

            $tmp_col_row_1_null_of_day = ['STT', 'Ngày', 'Ngày trong tháng'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_1_null_of_day[] = null;
            }

            $tmp_col_row_2_null_of_day =  ['A','Họ và tên'];
            for($i = 1; $i <= $number_day_in_month; $i++){
                $tmp_col_row_2_null_of_day[] = null;
            }

            $a5_to_ah7 = [
                $tmp_col_row_1_null_of_day,
                $tmp_col_day,
                $tmp_col_row_2_null_of_day
            ];

            $spread_sheet->getActiveSheet()->fromArray($a5_to_ah7, NULL, 'A5');
            $spread_sheet->getActiveSheet()->getStyle('A5:AH7')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A5:AH7')->applyFromArray($style_all_borders);

            $spread_sheet->getActiveSheet()->getStyle('C6:AG6')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('A7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
            $spread_sheet->getActiveSheet()->getStyle('B5:B7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);

        }

        if(count($result_arr) > 0) {

            $result_arr = collect($result_arr)->groupBy('route_id')->toArray();
            $cell_glo = 7;

            foreach ($result_arr as $keys => $values) {

                $values = collect($values)->groupBy('role_id')->toArray();

                if ($number_day_in_month == 28) {

                    foreach ($values as $key => $vls) {

                        $cell_glo = 7 + $lines - 1;

                        foreach ($vls as $k => $v) {

                            $cell_value = $lines + $cell;

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $v->fullname);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_left);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $v->day1);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $v->day2);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $v->day3);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $v->day4);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $v->day5);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $v->day6);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $v->day7);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $v->day8);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $v->day9);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $v->day10);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $v->day11);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $v->day12);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $v->day13);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $v->day14);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $v->day15);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $v->day16);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $v->day17);
                            $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $v->day18);
                            $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $v->day19);
                            $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $v->day20);
                            $spread_sheet->getActiveSheet()->getStyle('V' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, $v->day21);
                            $spread_sheet->getActiveSheet()->getStyle('W' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $v->day22);
                            $spread_sheet->getActiveSheet()->getStyle('X' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Y' . $cell_value, $v->day23);
                            $spread_sheet->getActiveSheet()->getStyle('Y' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Z' . $cell_value, $v->day24);
                            $spread_sheet->getActiveSheet()->getStyle('Z' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AA' . $cell_value, $v->day25);
                            $spread_sheet->getActiveSheet()->getStyle('AA' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AB' . $cell_value, $v->day26);
                            $spread_sheet->getActiveSheet()->getStyle('AB' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AC' . $cell_value, $v->day27);
                            $spread_sheet->getActiveSheet()->getStyle('AC' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AD' . $cell_value, $v->day28);
                            $spread_sheet->getActiveSheet()->getStyle('AD' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AE' . $cell_value, $v->count_shift);
                            $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $lines++;
                        }

                        $cell_value += 1;
                        $sum_from = $cell_glo + 1;
                        $sum_to = $cell_value - 1;

                        $last_row = [
                            [
                                '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                                '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                                '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                                '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                                '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                                '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                                '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                                '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                                '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                                '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                                '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                                '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                                '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                                '=SUM(P' . $sum_from . ':P' . $sum_to . ')',
                                '=SUM(Q' . $sum_from . ':Q' . $sum_to . ')',
                                '=SUM(R' . $sum_from . ':R' . $sum_to . ')',
                                '=SUM(S' . $sum_from . ':S' . $sum_to . ')',
                                '=SUM(T' . $sum_from . ':T' . $sum_to . ')',
                                '=SUM(U' . $sum_from . ':U' . $sum_to . ')',
                                '=SUM(V' . $sum_from . ':V' . $sum_to . ')',
                                '=SUM(W' . $sum_from . ':W' . $sum_to . ')',
                                '=SUM(X' . $sum_from . ':X' . $sum_to . ')',
                                '=SUM(Y' . $sum_from . ':Y' . $sum_to . ')',
                                '=SUM(Z' . $sum_from . ':Z' . $sum_to . ')',
                                '=SUM(AA' . $sum_from . ':AA' . $sum_to . ')',
                                '=SUM(AB' . $sum_from . ':AB' . $sum_to . ')',
                                '=SUM(AC' . $sum_from . ':AC' . $sum_to . ')',
                                '=SUM(AD' . $sum_from . ':AD' . $sum_to . ')',
                                '=SUM(AE' . $sum_from . ':AE' . $sum_to . ')',
                            ],
                        ];

                        $spread_sheet->getActiveSheet()->getStyle('AE')->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AE' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':AE' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AE' . $cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                        $value_get_cell =  $spread_sheet->getActiveSheet()->getCell('AE'.$cell_value)->getValue();
                        $lines ++ ;

                        $cell_value += 1;
                        $spread_sheet->getActiveSheet()->setCellValue('A' .$cell_value, $vls[0]->route_name.' '.$vls[0]->position_name);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AD' . $cell_value)->applyFromArray($style_left_bold);

                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':AD' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('AE' . $cell_value, $value_get_cell);
                        $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
                        $lines ++ ;
                    }
                }

                if ($number_day_in_month == 29) {

                    foreach ($values as $key => $vls) {

                        $cell_glo = 7 + $lines - 1;

                        foreach ($vls as $k => $v) {

                            $cell_value = $lines + $cell;

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $v->fullname);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_left);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $v->day1);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $v->day2);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $v->day3);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $v->day4);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $v->day5);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $v->day6);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $v->day7);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $v->day8);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $v->day9);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $v->day10);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $v->day11);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $v->day12);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $v->day13);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $v->day14);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $v->day15);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $v->day16);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $v->day17);
                            $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $v->day18);
                            $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $v->day19);
                            $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $v->day20);
                            $spread_sheet->getActiveSheet()->getStyle('V' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, $v->day21);
                            $spread_sheet->getActiveSheet()->getStyle('W' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $v->day22);
                            $spread_sheet->getActiveSheet()->getStyle('X' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Y' . $cell_value, $v->day23);
                            $spread_sheet->getActiveSheet()->getStyle('Y' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Z' . $cell_value, $v->day24);
                            $spread_sheet->getActiveSheet()->getStyle('Z' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AA' . $cell_value, $v->day25);
                            $spread_sheet->getActiveSheet()->getStyle('AA' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AB' . $cell_value, $v->day26);
                            $spread_sheet->getActiveSheet()->getStyle('AB' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AC' . $cell_value, $v->day27);
                            $spread_sheet->getActiveSheet()->getStyle('AC' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AD' . $cell_value, $v->day28);
                            $spread_sheet->getActiveSheet()->getStyle('AD' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AE' . $cell_value, $v->day29);
                            $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AF' . $cell_value, $v->count_shift);
                            $spread_sheet->getActiveSheet()->getStyle('AF' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $lines++;
                        }

                        $cell_value += 1;
                        $sum_from = $cell_glo + 1;
                        $sum_to = $cell_value - 1;

                        $last_row = [
                            [
                                '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                                '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                                '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                                '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                                '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                                '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                                '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                                '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                                '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                                '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                                '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                                '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                                '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                                '=SUM(P' . $sum_from . ':P' . $sum_to . ')',
                                '=SUM(Q' . $sum_from . ':Q' . $sum_to . ')',
                                '=SUM(R' . $sum_from . ':R' . $sum_to . ')',
                                '=SUM(S' . $sum_from . ':S' . $sum_to . ')',
                                '=SUM(T' . $sum_from . ':T' . $sum_to . ')',
                                '=SUM(U' . $sum_from . ':U' . $sum_to . ')',
                                '=SUM(V' . $sum_from . ':V' . $sum_to . ')',
                                '=SUM(W' . $sum_from . ':W' . $sum_to . ')',
                                '=SUM(X' . $sum_from . ':X' . $sum_to . ')',
                                '=SUM(Y' . $sum_from . ':Y' . $sum_to . ')',
                                '=SUM(Z' . $sum_from . ':Z' . $sum_to . ')',
                                '=SUM(AA' . $sum_from . ':AA' . $sum_to . ')',
                                '=SUM(AB' . $sum_from . ':AB' . $sum_to . ')',
                                '=SUM(AC' . $sum_from . ':AC' . $sum_to . ')',
                                '=SUM(AD' . $sum_from . ':AD' . $sum_to . ')',
                                '=SUM(AE' . $sum_from . ':AE' . $sum_to . ')',
                                '=SUM(AF' . $sum_from . ':AF' . $sum_to . ')',
                            ],
                        ];

                        $spread_sheet->getActiveSheet()->getStyle('AF')->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AF' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':AF' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AF' . $cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                        $value_get_cell =  $spread_sheet->getActiveSheet()->getCell('AF'.$cell_value)->getValue();
                        $lines ++ ;

                        $cell_value += 1;
                        $spread_sheet->getActiveSheet()->setCellValue('A' .$cell_value, $vls[0]->route_name.' '.$vls[0]->position_name);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AE' . $cell_value)->applyFromArray($style_left_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':AE' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('AF' . $cell_value, $value_get_cell);
                        $spread_sheet->getActiveSheet()->getStyle('AF' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('AF' . $cell_value)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
                        $lines ++ ;
                    }
                }

                if ($number_day_in_month == 30) {

                    foreach ($values as $key => $vls) {

                        $cell_glo = 7 + $lines - 1;

                        foreach ($vls as $k => $v) {

                            $cell_value = $lines + $cell;

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $v->fullname);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_left);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $v->day1);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $v->day2);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $v->day3);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $v->day4);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $v->day5);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $v->day6);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $v->day7);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $v->day8);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $v->day9);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $v->day10);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $v->day11);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $v->day12);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $v->day13);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $v->day14);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $v->day15);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $v->day16);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $v->day17);
                            $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $v->day18);
                            $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $v->day19);
                            $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $v->day20);
                            $spread_sheet->getActiveSheet()->getStyle('V' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, $v->day21);
                            $spread_sheet->getActiveSheet()->getStyle('W' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $v->day22);
                            $spread_sheet->getActiveSheet()->getStyle('X' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Y' . $cell_value, $v->day23);
                            $spread_sheet->getActiveSheet()->getStyle('Y' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Z' . $cell_value, $v->day24);
                            $spread_sheet->getActiveSheet()->getStyle('Z' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AA' . $cell_value, $v->day25);
                            $spread_sheet->getActiveSheet()->getStyle('AA' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AB' . $cell_value, $v->day26);
                            $spread_sheet->getActiveSheet()->getStyle('AB' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AC' . $cell_value, $v->day27);
                            $spread_sheet->getActiveSheet()->getStyle('AC' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AD' . $cell_value, $v->day28);
                            $spread_sheet->getActiveSheet()->getStyle('AD' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AE' . $cell_value, $v->day29);
                            $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AF' . $cell_value, $v->day30);
                            $spread_sheet->getActiveSheet()->getStyle('AF' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AG' . $cell_value, $v->count_shift);
                            $spread_sheet->getActiveSheet()->getStyle('AG' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $lines++;
                        }

                        $cell_value += 1;
                        $sum_from = $cell_glo + 1;
                        $sum_to = $cell_value - 1;

                        $last_row = [
                            [
                                '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                                '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                                '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                                '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                                '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                                '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                                '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                                '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                                '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                                '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                                '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                                '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                                '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                                '=SUM(P' . $sum_from . ':P' . $sum_to . ')',
                                '=SUM(Q' . $sum_from . ':Q' . $sum_to . ')',
                                '=SUM(R' . $sum_from . ':R' . $sum_to . ')',
                                '=SUM(S' . $sum_from . ':S' . $sum_to . ')',
                                '=SUM(T' . $sum_from . ':T' . $sum_to . ')',
                                '=SUM(U' . $sum_from . ':U' . $sum_to . ')',
                                '=SUM(V' . $sum_from . ':V' . $sum_to . ')',
                                '=SUM(W' . $sum_from . ':W' . $sum_to . ')',
                                '=SUM(X' . $sum_from . ':X' . $sum_to . ')',
                                '=SUM(Y' . $sum_from . ':Y' . $sum_to . ')',
                                '=SUM(Z' . $sum_from . ':Z' . $sum_to . ')',
                                '=SUM(AA' . $sum_from . ':AA' . $sum_to . ')',
                                '=SUM(AB' . $sum_from . ':AB' . $sum_to . ')',
                                '=SUM(AC' . $sum_from . ':AC' . $sum_to . ')',
                                '=SUM(AD' . $sum_from . ':AD' . $sum_to . ')',
                                '=SUM(AE' . $sum_from . ':AE' . $sum_to . ')',
                                '=SUM(AF' . $sum_from . ':AF' . $sum_to . ')',
                                '=SUM(AG' . $sum_from . ':AG' . $sum_to . ')',
                            ],
                        ];

                        $spread_sheet->getActiveSheet()->getStyle('AG')->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AG' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':AG' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AG' . $cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                        $value_get_cell =  $spread_sheet->getActiveSheet()->getCell('AG'.$cell_value)->getValue();
                        $lines ++ ;

                        $cell_value += 1;
                        $spread_sheet->getActiveSheet()->setCellValue('A' .$cell_value, $vls[0]->route_name.' '.$vls[0]->position_name);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AF' . $cell_value)->applyFromArray($style_left_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':AF' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('AG' . $cell_value, $value_get_cell);
                        $spread_sheet->getActiveSheet()->getStyle('AG' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('AG' . $cell_value)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
                        $lines ++ ;
                    }
                }

                if ($number_day_in_month == 31) {

                    foreach ($values as $key => $vls) {

                        $cell_glo = 7 + $lines - 1;

                        foreach ($vls as $k => $v) {

                            $cell_value = $lines + $cell;

                            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $v->fullname);
                            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_left);

                            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $v->day1);
                            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $v->day2);
                            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $v->day3);
                            $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $v->day4);
                            $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $v->day5);
                            $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $v->day6);
                            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $v->day7);
                            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $v->day8);
                            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $v->day9);
                            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $v->day10);
                            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $v->day11);
                            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $v->day12);
                            $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $v->day13);
                            $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $v->day14);
                            $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $v->day15);
                            $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $v->day16);
                            $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $v->day17);
                            $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $v->day18);
                            $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('U' . $cell_value, $v->day19);
                            $spread_sheet->getActiveSheet()->getStyle('U' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('V' . $cell_value, $v->day20);
                            $spread_sheet->getActiveSheet()->getStyle('V' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('W' . $cell_value, $v->day21);
                            $spread_sheet->getActiveSheet()->getStyle('W' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('X' . $cell_value, $v->day22);
                            $spread_sheet->getActiveSheet()->getStyle('X' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Y' . $cell_value, $v->day23);
                            $spread_sheet->getActiveSheet()->getStyle('Y' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('Z' . $cell_value, $v->day24);
                            $spread_sheet->getActiveSheet()->getStyle('Z' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AA' . $cell_value, $v->day25);
                            $spread_sheet->getActiveSheet()->getStyle('AA' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AB' . $cell_value, $v->day26);
                            $spread_sheet->getActiveSheet()->getStyle('AB' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AC' . $cell_value, $v->day27);
                            $spread_sheet->getActiveSheet()->getStyle('AC' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AD' . $cell_value, $v->day28);
                            $spread_sheet->getActiveSheet()->getStyle('AD' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AE' . $cell_value, $v->day29);
                            $spread_sheet->getActiveSheet()->getStyle('AE' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AF' . $cell_value, $v->day30);
                            $spread_sheet->getActiveSheet()->getStyle('AF' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AG' . $cell_value, $v->day31);
                            $spread_sheet->getActiveSheet()->getStyle('AG' . $cell_value)->applyFromArray($style_center);

                            $spread_sheet->getActiveSheet()->setCellValue('AH' . $cell_value, $v->count_shift);
                            $spread_sheet->getActiveSheet()->getStyle('AH' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                            $lines++;
                        }

                        $cell_value += 1;
                        $sum_from = $cell_glo + 1;
                        $sum_to = $cell_value - 1;

                        $last_row = [
                            [
                                '=SUM(C' . $sum_from . ':C' . $sum_to . ')',
                                '=SUM(D' . $sum_from . ':D' . $sum_to . ')',
                                '=SUM(E' . $sum_from . ':E' . $sum_to . ')',
                                '=SUM(F' . $sum_from . ':F' . $sum_to . ')',
                                '=SUM(G' . $sum_from . ':G' . $sum_to . ')',
                                '=SUM(H' . $sum_from . ':H' . $sum_to . ')',
                                '=SUM(I' . $sum_from . ':I' . $sum_to . ')',
                                '=SUM(J' . $sum_from . ':J' . $sum_to . ')',
                                '=SUM(K' . $sum_from . ':K' . $sum_to . ')',
                                '=SUM(L' . $sum_from . ':L' . $sum_to . ')',
                                '=SUM(M' . $sum_from . ':M' . $sum_to . ')',
                                '=SUM(N' . $sum_from . ':N' . $sum_to . ')',
                                '=SUM(O' . $sum_from . ':O' . $sum_to . ')',
                                '=SUM(P' . $sum_from . ':P' . $sum_to . ')',
                                '=SUM(Q' . $sum_from . ':Q' . $sum_to . ')',
                                '=SUM(R' . $sum_from . ':R' . $sum_to . ')',
                                '=SUM(S' . $sum_from . ':S' . $sum_to . ')',
                                '=SUM(T' . $sum_from . ':T' . $sum_to . ')',
                                '=SUM(U' . $sum_from . ':U' . $sum_to . ')',
                                '=SUM(V' . $sum_from . ':V' . $sum_to . ')',
                                '=SUM(W' . $sum_from . ':W' . $sum_to . ')',
                                '=SUM(X' . $sum_from . ':X' . $sum_to . ')',
                                '=SUM(Y' . $sum_from . ':Y' . $sum_to . ')',
                                '=SUM(Z' . $sum_from . ':Z' . $sum_to . ')',
                                '=SUM(AA' . $sum_from . ':AA' . $sum_to . ')',
                                '=SUM(AB' . $sum_from . ':AB' . $sum_to . ')',
                                '=SUM(AC' . $sum_from . ':AC' . $sum_to . ')',
                                '=SUM(AD' . $sum_from . ':AD' . $sum_to . ')',
                                '=SUM(AE' . $sum_from . ':AE' . $sum_to . ')',
                                '=SUM(AF' . $sum_from . ':AF' . $sum_to . ')',
                                '=SUM(AG' . $sum_from . ':AG' . $sum_to . ')',
                                '=SUM(AG' . $sum_from . ':AH' . $sum_to . ')',
                            ],
                        ];

                        $spread_sheet->getActiveSheet()->getStyle('AH')->applyFromArray($style_center_bold);

                        $spread_sheet->getActiveSheet()->fromArray($last_row, NULL, 'C' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AH' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value . ':AH' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AH' . $cell_value)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                        $value_get_cell =  $spread_sheet->getActiveSheet()->getCell('AH'.$cell_value)->getValue();
                        $lines ++ ;

                        $cell_value += 1;
                        $spread_sheet->getActiveSheet()->setCellValue('A' .$cell_value, $vls[0]->route_name.' '.$vls[0]->position_name);
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value . ':AG' . $cell_value)->applyFromArray($style_left_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':AG' . $cell_value);
                        $spread_sheet->getActiveSheet()->setCellValue('AH' . $cell_value, $value_get_cell);
                        $spread_sheet->getActiveSheet()->getStyle('AH' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                        $spread_sheet->getActiveSheet()->getStyle('AH' . $cell_value)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
                        $lines ++ ;
                    }
                }
            }
            // save spread sheet
            $this->downloadFileTimeKeeping($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Cham cong cho nhan vien', $number_day_in_month);
        }
    }

    public function exportOutputByVehicle($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];
        $vehicle_id = $data['vehicle_id'] ?? 0;
        $isCheckModuleApp = false;

        //get module app by company id
        $module_app_arr = $this->module_apps->getModuleKeyByCompanyId($company_id);
        if (in_array('the_tra_truoc', $module_app_arr) || in_array('the_km', $module_app_arr) || in_array('the_dong_gia', $module_app_arr)) {
            $isCheckModuleApp = true;
        }

        $result_arr = $this->viewOutputByVehicle($data);

        $company_name = '';
        $company_address = '';
        $company_phone = '';
        $company_tax_code = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
            $company_phone = $company->phone;
            $company_tax_code = $company->tax_code;
        }
        // path root
        $file_name = 'Thongke_capnhat_sanluong' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];


        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'com_phone' => $company_phone,
            'com_tax_code' => $company_tax_code,
        ];

        //merges array
        $merges_arr = ['A1:D1', 'A2:C2', 'A5:F5', 'A14:A16', 'B14:F14', 'G14:K14', 'G15:H15', 'B15:B16', 'C15:C16', 'D15:D16', 'E15:E16', 'F15:F16', 'I15:I16', 'J15:J16', 'K15:K16', 'L14:L16', 'M14:M16', 'N14:N16', 'O14:O16', 'P14:P16', 'Q14:Q16', 'R14:R16'];

        // table
        $a14_to_r16 = [
            ['CHUYẾN', 'BẾN, TRẠM ĐI', null, null, null, null, 'BẾN, TRẠM ĐẾN', null, null, null, null, 'HK VÉ THÁNG', 'HK MIỄN VÉ', 'HK LÀ HỌC SINH', 'HK LÀ CÔNG NHÂN', 'QRCODE', 'TRẢ TRƯỚC', 'GHI CHÚ'],
            [null, 'Điểm xuất phát', 'Giờ đi', 'Ký hiệu', 'Số serie vé', 'Điều hành xác nhận', 'Giờ đến', null, 'Số serie vé cuối', 'Số lượng vé bán ra', 'Điều hành xác nhận'],
            [null, null, null, null, null, null, 'Thực tế', 'Sớm(+) trễ(-)']
        ];
        $spread_sheet->getActiveSheet()->fromArray($a14_to_r16, NULL, 'A14');
        $spread_sheet->getActiveSheet()->getStyle('A14:R16')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A14:R16')->applyFromArray($style_all_borders);

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        // set data
        $lines = 1;
        $cell = 16;
        $cell_value = $lines + $cell;

        $spread_sheet->getActiveSheet()->getStyle('A14:R16')->applyFromArray($style_center);

        if (count($result_arr) > 0) {
            foreach ($result_arr['result_arr'] as $keys => $values) {

                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $values->station_start);
                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $values->started);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $values->sign);
                $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $values->start_number);
                $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $values->ended);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $values->end_number);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $values->count_ticket_total);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $values->count_ticket_month);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $values->count_ticket_free);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $values->count_ticket_student);
                $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $values->count_ticket_worker);
                $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $values->count_ticket_qrcode);
                $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $values->count_ticket_charge);
                $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);

                $lines++;
            }

            // if (count($result_arr['obj_total']) > 0) {

                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':C' . $cell_value);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $result_arr['obj_total']->count_total_trip);
                $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $result_arr['obj_total']->total_ticket_price);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $result_arr['obj_total']->total_ticket_month);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $result_arr['obj_total']->total_ticket_free);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $result_arr['obj_total']->total_ticket_student);
                $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $result_arr['obj_total']->total_ticket_worker);
                $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $result_arr['obj_total']->total_ticket_qrcode);
                $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $result_arr['obj_total']->total_ticket_charge);
                $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, '');
                $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_center);
            // }
        }
        // save spread sheet
        $this->downloadFileOutputByVehicle($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Thong ke, cap nhat san luong', $isCheckModuleApp, $result_arr);
    }

    public function exportShiftSupervisor($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];
        $vehicle_id = $data['user_id'] ?? 0;

        $result_arr = $this->viewShiftSupervisor($data);

        $company_name = '';
        $company_address = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BÁO CÁO GIÁM SÁT',
            'quarter' => 'Từ ngày ' . date("d/m/Y", strtotime($data['from_date'])) . " đến ngày " . date("d/m/Y", strtotime($data['to_date']))
        ];
        // path root
        $file_name = 'Bao_cao_giam_sat' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        //merges array
        $merges_arr = ['A1:D1', 'A2:D2', 'A5:G5', 'A4:G4'];

        // table
        $a7_to_g7 = [
            ['STT', 'Họ tên', 'Biển số xe', 'Tuyến', 'Vị trí lên', 'Vị trí xuống', 'Thời gian']
        ];
        $spread_sheet->getActiveSheet()->fromArray($a7_to_g7, NULL, 'A7');
        $spread_sheet->getActiveSheet()->getStyle('A7:G7')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A7:G7')->applyFromArray($style_all_borders);

        //merges
        foreach ($merges_arr as $merge) { $spread_sheet->getActiveSheet()->mergeCells($merge); }

        // set data
        $lines = 1;
        $cell = 7;
        $cell_value = $lines + $cell;
        $spread_sheet->getActiveSheet()->getStyle('A7:G7')->applyFromArray($style_center);

        if (count($result_arr) > 0) {

            foreach ($result_arr as $keys => $values) {

                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $values->fullname);
                // $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $values->license_plates);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $values->route_name);
                // $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $values->station_up);
                // $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $values->station_up);
                // $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $values->start_end);
                $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center);

                $lines++;
            }
        }
        // save spread sheet
        $this->downloadFileShiftSupervisor($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Bao cao giam sat');
    }
    //-------------------------------------------function get/update/----------------------------------
    public function getReportVehicleAllByData($data)
    {
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $company_id = $data['company_id'];

        $vehicle_arr = [];
        $shifts = Shift::join('vehicles','shifts.vehicle_id','=','vehicles.id')
        ->join('transactions','transactions.shift_id','=','shifts.id')
        ->leftJoin('ticket_prices','transactions.ticket_price_id','=','ticket_prices.id')
        ->where([
            ['transactions.activated', '>=', $from_date],
            ['transactions.activated', '<=', $to_date],
            ['shift_destroy', '!=', 1],
            ['vehicles.company_id', '=',$company_id],
            ['transactions.ticket_destroy', '!=', 1]
        ])
        ->selectRaw('
            vehicles.id as vehicle_id,
            vehicles.license_plates,
            transactions.type,
            sum(transactions.amount) as total_amount,
            count(transactions.type) as count_ticket,
            sum(ticket_prices.price) as total_price,
            count(shifts.id) as count_shift
        ')
        ->groupBy('vehicles.id','vehicles.license_plates','transactions.type')
        ->orderby('vehicles.id')
        ->get();

        $shifts = collect($shifts)->groupBy('vehicle_id')->toArray();

        $total_all = [
            'license_plates' => 'all',
            'sum_count_tickets_pos' => 0,
            'sum_count_tickets_charge' => 0,
            'sum_count_tickets_qrcode' => 0,
            'sum_count_tickets_charge_month' => 0,
            'sum_total_pos' => 0,
            'sum_total_charge' => 0,
            'sum_total_qrcode' => 0,
            'sum_total_charge_month' => 0,
            'sum_total_receipts' => 0,
            'sum_total_shift' => 0,

            'sum_total_shift_last' => 0,
            'sum_total_count_ticket_last' => 0,
            'sum_total_revenue_last' => 0,
            'sum_total_receipts_last' => 0
        ];

        foreach ($shifts as $shift) {

            $data_tmp = [
                'license_plates' => $shift[0]['license_plates'],
                'count_tickets_pos' => 0,
                'count_tickets_charge' => 0,
                'count_tickets_qrcode' => 0,
                'count_tickets_charge_month' => 0,
                'total_pos' => 0,
                'total_charge' => 0,
                'total_qrcode' => 0,
                'total_charge_month' => 0,
                'total_receipts' => 0,
                'total_shift' => 0,

                'total_shift_last' => 0,
                'total_count_ticket_last' => 0,
                'total_revenue_last' => 0,
                'total_receipts_last' => 0
            ];

            foreach ($shift as $value) {

                if ($value['type'] == "pos") {
                    $data_tmp['total_pos'] =  $value['total_price'];
                    $data_tmp['count_tickets_pos'] = $value['count_ticket'];
                }

                if ($value['type'] == "charge") {
                    $data_tmp['total_charge'] =  $value['total_price'];
                    $data_tmp['count_tickets_charge'] = $value['count_ticket'];
                    $data_tmp['total_receipts'] =  $value['total_amount'];
                }

                if ($value['type'] == "qrcode") {
                    $data_tmp['total_qrcode'] =  $value['total_price'];
                    $data_tmp['count_tickets_qrcode'] = $value['count_ticket'];
                }

                if ($value['type'] == "charge_month") {
                    $data_tmp['total_shift'] = $value['count_shift'];
                }

                $data_tmp['total_shift_last'] += $value['count_shift'];
                $data_tmp['total_count_ticket_last'] =   $data_tmp['count_tickets_pos'] + $data_tmp['count_tickets_charge'] + $data_tmp['count_tickets_qrcode'];
                $data_tmp['total_revenue_last'] =   $data_tmp['total_pos'] + $data_tmp['total_charge'] + $data_tmp['total_qrcode'];
                $data_tmp['total_receipts_last'] =   $data_tmp['total_pos'] + $data_tmp['total_receipts'] + $data_tmp['total_qrcode'];


            }

            $vehicle_arr[] = $data_tmp;
            $total_all['sum_count_tickets_pos'] += $data_tmp['count_tickets_pos'];
            $total_all['sum_total_pos'] += $data_tmp['total_pos'];
            $total_all['sum_count_tickets_charge'] += $data_tmp['count_tickets_charge'];
            $total_all['sum_total_charge'] += $data_tmp['total_charge'];
            $total_all['sum_total_receipts'] += $data_tmp['total_receipts'];
            $total_all['sum_count_tickets_qrcode'] += $data_tmp['count_tickets_qrcode'];
            $total_all['sum_total_qrcode'] += $data_tmp['total_qrcode'];
            $total_all['sum_total_shift'] += $data_tmp['total_shift'];

            $total_all['sum_total_shift_last'] += $data_tmp['total_shift_last'];
            $total_all['sum_total_count_ticket_last'] += $data_tmp['total_count_ticket_last'];
            $total_all['sum_total_revenue_last'] += $data_tmp['total_revenue_last'];
            $total_all['sum_total_receipts_last'] += $data_tmp['total_receipts_last'];
        }
        $vehicle_arr[] = $total_all;
        return $vehicle_arr;
    }

    public function getReportVehicleByPeriod($data)
    {

        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));

        $from_year_last = $this->editStringYearByDateTime(date("Y-m-d", strtotime($data['from_date'])));
        $to_year_last = $this->editStringYearByDateTime(date("Y-m-d", strtotime($data['to_date'])));

        $from_date_last = date("Y-m-d 00:00:00", strtotime($from_year_last));
        $to_date_last = date("Y-m-d 23:59:59", strtotime($to_year_last));

        $company_id = $data['company_id'];

        $vehicle_arr = [];
        $vehicles = $this->vehicles->getVehiclesByOptions([['company_id', $company_id]]);

        foreach ($vehicles as $vl) {
            $vehicle_arr[$vl->id] = [
                'license_plates' => $vl->license_plates,
                'count_tickets' => 0,
                'total_pos' => 0,
                'total_charge' => 0,
                'total_deposit' => 0,
                'total_revenue' => 0,
                'count_tickets_last' => 0,
                'total_pos_last' => 0,
                'total_charge_last' => 0,
                'total_deposit_last' => 0,
                'total_revenue_last' => 0,
                'count_tickets_percent' => 0,
                'total_pos_percent' => 0,
                'total_charge_percent' => 0,
                'total_deposit_percent' => 0,
                'total_revenue_percent' => 0
            ];
        }

        $vehicle_id_arr = $this->vehicles->getVehicleIdByCompanyId($company_id);
        //this period
        $shift_period_now =  DB::table('shifts')
            ->whereIn('shifts.vehicle_id', $vehicle_id_arr)
            ->where('shifts.ended', '>=', $from_date)
            ->where('shifts.ended', '<=', $to_date)
            ->get();

        //previous period
        $shift_period_last =  DB::table('shifts')
            ->whereIn('shifts.vehicle_id', $vehicle_id_arr)
            ->where('shifts.ended', '>=', $from_date_last)
            ->where('shifts.ended', '<=', $to_date_last)
            ->get();

        if (count($shift_period_now) > 0) {
            foreach ($shift_period_now  as $shift) {

                $sum_pos = 0;
                $sum_charge = 0;
                $sum_deposit = 0;
                $sum_ticket = 0;

                $obj_vehicle =  $vehicle_arr[$shift->vehicle_id];

                $transaction_arr = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id],
                    ['ticket_destroy', '!=', 1]
                ]);
                if (count($transaction_arr) > 0) {

                    foreach ($transaction_arr as $transaction) {

                        $type = $transaction->type;
                        $amount = $transaction->amount;

                        if ((float) $amount > 0) {
                            if ($type == 'deposit') {
                                $sum_deposit += (float)  $amount;
                            }
                            if ($type == 'charge') {
                                $sum_ticket += 1;
                                $sum_charge += (float)  $amount;
                            }
                            if ($type == 'pos' && $transaction->ticket_number != '') {
                                $sum_ticket += 1;
                                $sum_pos += (float) $amount;
                            }
                        }
                    }
                    $obj_vehicle['count_tickets'] +=  $sum_ticket;
                    $obj_vehicle['total_pos'] += $sum_pos;
                    $obj_vehicle['total_charge'] +=  $sum_charge;
                    $obj_vehicle['total_deposit'] +=  $sum_deposit;
                    $obj_vehicle['total_revenue'] += ($sum_charge + $sum_pos);

                    $vehicle_arr[$shift->vehicle_id] =  $obj_vehicle;
                }
            }
        }

        if (count($shift_period_last) > 0) {

            foreach ($shift_period_last  as $shift) {

                $sum_pos = 0;
                $sum_charge = 0;
                $sum_deposit = 0;
                $sum_ticket = 0;

                $obj_vehicle =  $vehicle_arr[$shift->vehicle_id];

                $transaction_arr = $this->transactions->getTransactionByOptions([
                    ['shift_id', $shift->id],
                    ['company_id', $company_id],
                    ['ticket_destroy', '!=', 1]
                ]);
                if (count($transaction_arr) > 0) {

                    foreach ($transaction_arr as $transaction) {

                        $type = $transaction->type;
                        $amount = $transaction->amount;

                        if ((float) $amount > 0) {
                            if ($type == 'deposit') {
                                $sum_deposit += (float)  $amount;
                            }
                            if ($type == 'charge') {
                                $sum_ticket += 1;
                                $sum_charge += (float)  $amount;
                            }
                            if ($type == 'pos' && $transaction->ticket_number != '') {
                                $sum_ticket += 1;
                                $sum_pos += (float) $amount;
                            }
                        }
                    }
                    $obj_vehicle['count_tickets_last']  +=  $sum_ticket;
                    $obj_vehicle['total_pos_last']  += $sum_pos;
                    $obj_vehicle['total_charge_last'] +=  $sum_charge;
                    $obj_vehicle['total_deposit_last'] +=  $sum_deposit;
                    $obj_vehicle['total_revenue_last'] += ($sum_charge + $sum_pos);

                    $vehicle_arr[$shift->vehicle_id] =  $obj_vehicle;
                }
            }
        }
        $result = [];

        foreach (array_values($vehicle_arr) as $vl) {

            if ($vl['count_tickets_last'] > 0) {
                $vl['count_tickets_percent'] =  round(($vl['count_tickets'] / $vl['count_tickets_last']) * 100, 0);
            } else if ($vl['count_tickets_last'] == 0) {
                $vl['count_tickets_percent'] = '-';
            }

            if ($vl['total_pos_last'] > 0) {
                $vl['total_pos_percent'] =  round(($vl['total_pos'] / $vl['total_pos_last']) * 100, 0);
            } else if ($vl['total_pos_last'] == 0) {
                $vl['total_pos_percent'] = '-';
            }

            if ($vl['total_charge_last'] > 0) {
                $vl['total_charge_percent'] =  round(($vl['total_charge'] / $vl['total_charge_last']) * 100, 0);
            } else if ($vl['total_charge_last'] == 0) {
                $vl['total_charge_percent'] = '-';
            }

            if ($vl['total_deposit_last'] > 0) {
                $vl['total_deposit_percent'] =  round(($vl['total_deposit'] / $vl['total_deposit_last']) * 100, 0);
            } else if ($vl['total_deposit_last'] == 0) {
                $vl['total_deposit_percent'] = '-';
            }

            if ($vl['total_revenue_last'] > 0) {
                $vl['total_revenue_percent'] =  round(($vl['total_revenue'] / $vl['total_revenue_last']) * 100, 0);
            } else if ($vl['total_revenue_last'] == 0) {
                $vl['total_revenue_percent'] = '-';
            }
            array_push($result, $vl);
        }

        return  $result;
    }

    public function printTicket($data)
    {

        $company_id = $data['company_id'];

        // date
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $ticket_price_id = (int)$data['price_id'];

        // $transaction_arr = [];
        // get company
        // $company = $this->companies->getCompanyById($company_id);
        // $company_fullname = $company ?  $company->fullname : '';
        // $company_address = $company ? $company->address : '';
        // $company_tax_code = $company ? $company->tax_code : '';
        // $company_phone = $company ? $company->phone : '';
        // $company_print_at = $company ? $company->print_at : '';

        $transactions = DB::table('transactions')
            ->where('transactions.company_id', $company_id)
            ->whereIn('transactions.type', ['pos', 'charge','qrcode','deposit_month'])
            ->where('transactions.ticket_number', '!=', NULL)
            ->where('transactions.ticket_destroy', '!=', 1)
            ->where('transactions.ticket_price_id', $ticket_price_id)
            ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
            ->join('vehicles', 'shifts.vehicle_id', '=', 'vehicles.id')
            ->where('shifts.ended', '>=', $from_date)
            ->where('shifts.ended', '<=', $to_date)
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
            ->leftJoin('routes', 'routes.id', '=', 'shifts.route_id')
            ->leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
            ->leftJoin('ticket_types', 'ticket_types.id', '=', 'ticket_prices.ticket_type_id')
            ->leftJoin('rfidcards', 'rfidcards.rfid', '=', 'transactions.rfid')
            ->leftJoin('memberships', 'memberships.rfidcard_id', '=', 'rfidcards.id')
            ->select(
              'transactions.amount',
              'transactions.activated',
              'transactions.balance',
              'transactions.sign',
              'transactions.ticket_number',
              'transactions.station_data',
              'transactions.station_down',
              'users.fullname as staff',
              'bus_stations.name as station_name',
              'routes.number as route_number',
              'routes.name as route_name',
              'ticket_types.order_code as order_code',
              'ticket_prices.price as price',
              'vehicles.license_plates',
              'ticket_types.type as ticket_type',
              'rfidcards.barcode',
              'memberships.fullname as fullname'
            )
            ->orderByRaw('cast(routes.number as unsigned)')
            ->orderBy('transactions.activated')
            ->get();
        return $transactions;
        // if (count($transactions) > 0) {
        //
        //     foreach ($transactions as $key => $transaction) {

                // $staff = '';

                // $station_name = '';
                // $route_number = null;
                // $price = 0;
                // $discount = 0;
                // $order_code = '';
                // $activated = $transaction->activated;

                //get data bus station by id
                // $bus_station = $this->bus_stations->getDataBusStationById($transaction->station_id);
                // if ($bus_station) {
                //     $station_name = $bus_station->name;
                // }

                //get data shift by id
                // $shift = $this->shifts->getShiftsById($transaction->shift_id);
                // if ($shift) {
                //
                //     //get data route by id
                //     $route = $this->routes->getRouteById($shift->route_id);
                //     if ($route) {
                //         $route_number =  $route->number;
                //     }
                // }

                //get data user by id
                // $user = $this->users->getUsersById($transaction->user_id);
                // if ($user) {
                //     $staff = $user->fullname;
                // }

                //get data ticket price by id
                // $ticket_price = $this->ticket_prices->getPriceById($transaction->ticket_price_id);
                // if ($ticket_price) {
                //
                //     $price = $ticket_price->price;
                //     $discount = ;
                //     //get data ticket type by id
                //     $ticket_type_id = $ticket_price->ticket_type_id;
                //     if ($ticket_type_id) {
                //
                //         $ticket_type = $this->ticket_types->getTicketTypeById($ticket_type_id);
                //         if ($ticket_type) {
                //             $order_code = $ticket_type->order_code;
                //         }
                //     }
                // }
                //
                // $transaction_tmp = array(
                //     'company_fullname' =>  $company_fullname,
                //     'company_address' =>  $company_address,
                //     'company_tax_code' =>  $company_tax_code,
                //     'company_phone' =>  $company_phone,
                //     'company_print_at' =>  $company_print_at,
                //     'order_code' =>  $transaction->order_code,
                //     'sign' => $transaction->sign ?? null,
                //     'ticket_number' => $transaction->ticket_number,
                //     'route_number' => $transaction->route_number,
                //     'staff' => $transaction->staff,
                //     'station_name' => $transaction->station_name,
                //     'price' => $transaction->price,
                //     'discount' => (float) $transaction->price - (float) $transaction->amount,
                //     'collected' => $transaction->amount,
                //     'balance' => $transaction->balance ?? null,
                //     'activated' => $transaction->activated
                // );
                // array_push($transaction_arr, $transaction_tmp);
        //         $transaction_arr[$key] =  $transaction_tmp;
        //     }
        // }
        //
        // return $transaction_arr;
    }

    public function getReceiptDetailByShiftId($data)
    {

        $company_id = $data['company_id'];
        $shift_id = $data['shift_id'];

        $shift_detail = [];
        $trans_arr = [];
        $summary = [];

        $pos = 0;
        $charge = 0;
        $deposit = 0;
        $online = 0;
        $pos_goods = 0;
        $charge_goods = 0;
        $online_goods = 0;
        $count_charge_free = 0;
        $count_charge_month = 0;
        $count_pos_goods = 0 ;
        $count_charge_goods = 0 ;
        $count_online_goods = 0 ;


        $ticket_types = $this->ticket_types->listTicketTypesWhereNotIn([], $company_id);

        if (count($ticket_types) > 0) {

            foreach ($ticket_types as $ticket_type) {

                $obj = new \stdClass;
                $ticket_prices = end($ticket_type['ticket_prices']);
                $price = $ticket_prices['price'];
                $obj->price = $price;
                $obj->pos = 0;
                $obj->charge = 0;
                $obj->online = 0;
                $obj->total_pos = 0;
                $prices_arr[$price] = $obj;
            }
        }
        // set data return
        $transactions = $this->transactions->getTransactionByOptions([
            ['shift_id', $shift_id],
            ['company_id', $company_id],
            ['ticket_destroy', '!=', 1]
        ]);

        if (count($transactions) > 0) {

            foreach ($transactions as $transaction) {

                $ticket_price_id = $transaction->ticket_price_id;
                $type = $transaction->type;
                $amount = $transaction->amount;

                $station_name = '';

                //get bus station
                $bus_station = $this->bus_stations->getDataBusStationById($transaction->station_id);

                if ($bus_station) {
                    $station_name = $bus_station->name;
                }

                $is_tkt_deposit_month = 0;

                if($transaction->type == 'deposit_month'){

                    $count_tran_deposit = Transaction::where([
                        ['type', 'charge_month'],
                        ['ticket_number', $transaction->ticket_number],
                        ['ticket_price_id', $transaction->ticket_price_id],
                        ['rfid', $transaction->rfid],
                        ['company_id', $company_id],
                        ['ticket_destroy', '!=', 1]
                    ])->first();

                    if(!empty($count_tran_deposit)) $is_tkt_deposit_month = 1;
                }

                // set transaction
                $trans_temp = [
                    'id' =>  $transaction->id,
                    'shift_id' => $transaction->shift_id,
                    'station_name' =>  $station_name,
                    'ticket_number' => $transaction->ticket_number,
                    'ticket_price_id' => $transaction->ticket_price_id,
                    'type' => $type,
                    'created_at' => $transaction->activated,
                    'amount' => $amount,
                    'ticket_destroy' => $transaction->ticket_destroy,
                    'is_tkt_deposit_month' => $is_tkt_deposit_month,
                    'subuser_id' => $transaction->user_id,
                ];

                array_push($trans_arr, $trans_temp);

                // get ticket price array_search
                if (!empty($ticket_price_id) && (int) $ticket_price_id > 0) {

                    $ticket_price = $this->ticket_prices->getPriceById($ticket_price_id);
                    $price = $ticket_price->price;


                    if ($type == 'pos') {
                        $obj = $prices_arr[$price];
                        $obj->pos += 1;
                        $obj->total_pos += $amount;
                        $prices_arr[$price] = $obj;
                    }

                    if ($type == 'charge') {
                        $obj = $prices_arr[$price];
                        $obj->charge += 1;
                        $prices_arr[$price] = $obj;
                    }

                    if ($type == 'qrcode' || $type == 'app:1') {
                        $obj = $prices_arr[$price];
                        $obj->online += 1;
                        $prices_arr[$price] = $obj;
                    }
                }
                // set type = pos
                if ($type == 'pos') {
                    $pos += (float) $amount;
                }

                // set type = charge
                if ($type == 'charge') {
                    $charge += (float) $amount;
                }

                // set type = deposit
                if ($type == 'deposit' || $type == 'deposit_month') {
                    $deposit += (float) $amount;
                }

                // set type = deposit
                if ($type == 'charge_free') {
                    $count_charge_free += 1;
                }

                // set type = deposit
                if ($type == 'charge_month') {
                    $count_charge_month += 1;
                }

                // // set type = goods
                // if ($type == 'charge_goods' || $type == 'pos_goods' || $type == 'qrcode_goods') {
                //     $goods += (double) $amount;
                // }

                // set type = deposit
                if ($type == 'qrcode' || $type === 'app:1') {
                    $online += (float) $amount;
                }

                //set 'pos_goods'
                if ($type == 'pos_goods') {
                    $pos_goods += (float) $amount;
                    $count_pos_goods +=1 ;
                }
                //set 'charge_goods'
                if ($type == 'charge_goods') {
                    $charge_goods += (float) $amount;
                    $count_charge_goods +=1 ;
                }
                //set 'qrcode_goods'
                if ($type == 'qrcode_goods') {
                    $online_goods += (float) $amount;
                    $count_online_goods +=1 ;
                }
            }
        }

        $obj_pos = new \stdClass;
        $obj_pos->price = 'pos';
        $obj_pos->total_price = $pos;
        $prices_arr[] = $obj_pos;

        $obj_deposit = new \stdClass;
        $obj_deposit->price = 'deposit';
        $obj_deposit->total_price = $deposit;
        $prices_arr[] = $obj_deposit;

        $obj_charge = new \stdClass;
        $obj_charge->price = 'charge';
        $obj_charge->total_price = $charge;
        $prices_arr[] = $obj_charge;

        $obj_charge = new \stdClass;
        $obj_charge->price = 'online';
        $obj_charge->total_price = $online;
        $prices_arr[] = $obj_charge;

        $obj_charge = new \stdClass;
        $obj_charge->price = 'charge_free';
        $obj_charge->total_price = $count_charge_free;
        $prices_arr[] = $obj_charge;

        $obj_charge = new \stdClass;
        $obj_charge->price = 'charge_month';
        $obj_charge->total_price = $count_charge_month;
        $prices_arr[] = $obj_charge;

        $obj_pos_goods = new \stdClass;
        $obj_pos_goods->price = 'pos_goods';
        $obj_pos_goods->total_price = $pos_goods;
        $obj_pos_goods->count_goods = $count_pos_goods;
        $prices_arr[] = $obj_pos_goods;

        $obj_charge_goods = new \stdClass;
        $obj_charge_goods->price = 'charge_goods';
        $obj_charge_goods->total_price = $charge_goods;
        $obj_charge_goods->count_goods = $count_charge_goods;
        $prices_arr[] = $obj_charge_goods;

        $obj_qrcode_goods = new \stdClass;
        $obj_qrcode_goods->price = 'qrcode_goods';
        $obj_qrcode_goods->total_price = $online_goods;
        $obj_qrcode_goods->count_goods = $count_online_goods;
        $prices_arr[] = $obj_qrcode_goods;

        foreach ($prices_arr as $v) {
            $summary[] = $v;
        }

        $shift_detail['transactions'] = $trans_arr;
        $shift_detail['summary'] = $summary;

        return $shift_detail;
    }

    public function updateShiftByUserId($input)
    {
        //get input
        $shift_id = (int)$input['shift_id'];
        $user_id = (int)$input['user_id'];
        $subdriver_id = (int)$input['subdriver_id'];
        $data = [];

        $shift = Shift::where('id', $shift_id)->first();
        if (empty($shift)) return response("Shift not found", 404);

        if ($user_id != 0) $shift->user_id = $user_id;
        if ($subdriver_id != 0) $shift->subdriver_id = $subdriver_id;

        if($shift->save()) return response($shift, 200);

        return response('Update error', 404);
    }

    public function cmp_route_id($a, $b)
    {
        $a = (object) $a;
        $b = (object) $b;
        return ($a->route_id < $b->route_id) ? -1 : 1;
    }

    public function cmp_vehicle_id($a, $b)
    {
        $a = (object) $a;
        $b = (object) $b;
        return ($a->vehicle_id < $b->vehicle_id) ? -1 : 1;
    }

    public function cmp_device_id($a, $b)
    {
        $a = (object) $a;
        $b = (object) $b;
        return ($a->device_id < $b->device_id) ? -1 : 1;
    }

    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val->$key, $key_array)) {
                $key_array[$i] = $val->$key;
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    // -------------------------- Download file --------------------------------------//

    private function downloadFileStaff($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $data_total, $key_role, $route_id = null, $isCheckModuleApp)
    {

        if ($isCheckModuleApp) {

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];
            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_all_borders = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
            ];

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth(17);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);


            $c4 = 'A4';
            $d5 = 'A5';
            $cell_B = 'A';
            $cell_B_last = ':E';
            $cell_GH = 'O';
            $cell_GH_last = ':Q';
            $start_border = 'A8:Q';


            $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($d5, $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('A6')->applyFromArray($style_center);

            //------------row last
            if ($key_role == 'role_only' && $route_id == 0) {

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng cộng');

                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_only']['count_ticket_pos_only']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_only']['total_ticket_pos_only']);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_total['data_total_only']['count_ticket_charge_only']);
                $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, $data_total['data_total_only']['total_ticket_charge_only']);
                $spread_sheet->getActiveSheet()->setCellValue('I' . $last_cell, $data_total['data_total_only']['total_discount_charge_only']);
                $spread_sheet->getActiveSheet()->setCellValue('J' . $last_cell, $data_total['data_total_only']['total_collected_charge_only']);

                $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, $data_total['data_total_only']['count_ticket_qrcode_only']);
                $spread_sheet->getActiveSheet()->setCellValue('L' . $last_cell, $data_total['data_total_only']['total_ticket_qrcode_only']);

                $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, $data_total['data_total_only']['total_price_month_only']);

                $spread_sheet->getActiveSheet()->setCellValue('N' . $last_cell, $data_total['data_total_only']['total_revenue_ticket_all_only']);
                $spread_sheet->getActiveSheet()->setCellValue('O' . $last_cell, $data_total['data_total_only']['total_discount_ticket_all_only']);
                $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, $data_total['data_total_only']['total_collected_ticket_all_only']);
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $last_cell, $data_total['data_total_only']['total_deposit_only']);

                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('I' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('J' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('K' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('L' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');
                $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('O' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('Q' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
            }

            if ($key_role == 'role_all') {

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng tài xế');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_driver']['count_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_driver']['total_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_total['data_total_driver']['count_ticket_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, $data_total['data_total_driver']['total_ticket_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('I' . $last_cell, $data_total['data_total_driver']['total_discount_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('J' . $last_cell, $data_total['data_total_driver']['total_collected_charge_driver']);

                $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, $data_total['data_total_driver']['count_ticket_qrcode_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('L' . $last_cell, $data_total['data_total_driver']['total_ticket_qrcode_driver']);

                $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, $data_total['data_total_driver']['total_price_month_driver']);

                $spread_sheet->getActiveSheet()->setCellValue('N' . $last_cell, $data_total['data_total_driver']['total_revenue_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, $data_total['data_total_driver']['total_collected_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('O' . $last_cell, $data_total['data_total_driver']['total_discount_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $last_cell, $data_total['data_total_driver']['total_deposit_driver']);


                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('I' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('J' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('K' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('L' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');

                $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('O' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('Q' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng phụ xe');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_subdriver']['count_ticket_pos_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_subdriver']['total_ticket_pos_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_total['data_total_subdriver']['count_ticket_charge_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, $data_total['data_total_subdriver']['total_ticket_charge_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('I' . $last_cell, $data_total['data_total_subdriver']['total_discount_charge_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('J' . $last_cell, $data_total['data_total_subdriver']['total_collected_charge_subdriver']);

                $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, $data_total['data_total_subdriver']['count_ticket_qrcode_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('L' . $last_cell, $data_total['data_total_subdriver']['total_ticket_qrcode_subdriver']);

                $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, $data_total['data_total_subdriver']['total_price_month_subdriver']);

                $spread_sheet->getActiveSheet()->setCellValue('N' . $last_cell, $data_total['data_total_subdriver']['total_revenue_ticket_all_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, $data_total['data_total_subdriver']['total_collected_ticket_all_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('O' . $last_cell, $data_total['data_total_subdriver']['total_discount_ticket_all_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $last_cell, $data_total['data_total_subdriver']['total_deposit_subdriver']);


                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('I' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('J' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('K' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('L' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');

                $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('O' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('Q' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_driver']['count_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_driver']['total_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_total['data_total_driver']['count_ticket_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, $data_total['data_total_driver']['total_ticket_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('I' . $last_cell, $data_total['data_total_driver']['total_discount_charge_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('J' . $last_cell, $data_total['data_total_driver']['total_collected_charge_driver']);

                $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, $data_total['data_total_driver']['count_ticket_qrcode_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('L' . $last_cell, $data_total['data_total_driver']['total_ticket_qrcode_driver']);

                $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, $data_total['data_total_driver']['total_price_month_driver']);


                $spread_sheet->getActiveSheet()->setCellValue('N' . $last_cell, $data_total['data_total_driver']['total_revenue_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, $data_total['data_total_driver']['total_collected_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('O' . $last_cell, $data_total['data_total_driver']['total_discount_ticket_all_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('Q' . $last_cell, $data_total['data_total_driver']['total_deposit_driver']);

                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('I' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('J' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('K' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('L' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0.00');

                $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('O' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('Q' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
            }

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);
        } else {

            $style_center_bold = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];
            $style_bold = [
                'font' => ['bold' => true],
            ];

            $style_center =  [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ];

            $style_all_borders = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
            ];

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(17);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);


            $c4 = 'A4';
            $d5 = 'A5';
            $cell_B = 'A';
            $cell_B_last = ':E';
            $cell_GH = 'E';
            $cell_GH_last = ':F';
            $start_border = 'A9:F';

            $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($d5, $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('A6')->applyFromArray($style_center);

            //------------row last
            if ($key_role == 'role_only') {

                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_only']['count_ticket_pos_only']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_only']['total_ticket_pos_only']);
                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
            }

            if ($key_role == 'role_all') {
                $last_cell = $last_cell + 1;

                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tài xế');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_driver']['count_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_driver']['total_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);

                $last_cell = $last_cell + 1;

                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Phụ xe');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_subdriver']['count_ticket_pos_subdriver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_subdriver']['total_ticket_pos_subdriver']);
                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_total['data_total_driver']['count_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_total['data_total_driver']['total_ticket_pos_driver']);
                $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . ':D' . $last_cell);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
            }

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'PHÒNG KẾ TOÁN');
            $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'BAN GIÁM ĐỐC');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileTicket($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $c4 = 'A4';
        $a5 = 'A5';
        $cell_GH = 'H';
        $cell_GH_last = ':K';
        $start_border = 'A8:K';

        $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':D' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':D' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileTicketByStation($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $c4 = 'A4';
        $a5 = 'A5';
        $cell_B = 'A';
        $cell_B_last = ':D';
        $cell_GH = 'G';
        $cell_GH_last = ':I';
        $start_border = 'A8:I';

        $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);


        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('C'.$last_cell, 'PHÒNG KẾ TOÁN');
        // $spread_sheet->getActiveSheet()->getStyle('C'.$last_cell)->applyFromArray($style_center_bold);
        // $spread_sheet->getActiveSheet()->mergeCells('C'.$last_cell.':D'.$last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('C'.$last_cell, '(Ký, họ tên)');
        // $spread_sheet->getActiveSheet()->getStyle('C'.$last_cell)->applyFromArray($style_center);
        // $spread_sheet->getActiveSheet()->mergeCells('C'.$last_cell.':D'.$last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileDaily($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title,  $isCheckModuleApp, $total_collecter)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if ($isCheckModuleApp) {
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(18);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(12);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(13);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(7);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(7);
            $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(11);
            $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(7);
            $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('R')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('S')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('T')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('U')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('V')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('W')->setWidth(16);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(27);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

            $c4 = 'A4';
            $a5 = 'A5';
            $cell_B = 'A';
            $cell_B_last = ':E';
            $cell_GH = 'R';
            $cell_GH_last = ':W';
            $start_border = 'A8:W';

            $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

            //------------row last


            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            if(count($total_collecter) > 0){
                 //total collecter
                $last_cell_first = $last_cell + 3;
                $last_cell = $last_cell + 3;
                $i = 1;

                //set title
                $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell,  'STT');
                $spread_sheet->getActiveSheet()->getStyle('B'. $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'Họ tên');
                $spread_sheet->getActiveSheet()->getStyle('C'. $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Số tiền thu');
                $spread_sheet->getActiveSheet()->getStyle('D'. $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'Ký nhận');
                $spread_sheet->getActiveSheet()->getStyle('E'. $last_cell)->applyFromArray($style_center_bold);

                foreach ($total_collecter as $value) {

                    $value = (array)$value;
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell,  $i);
                    $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, $value['fullname']);
                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $value['total_amount']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, '');
                    $i++;
                }

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle('B'.$last_cell_first.':E'.$last_cell)->applyFromArray($style_all_borders);

                $spread_sheet->getActiveSheet()->getStyle('B'.$last_cell_first.':E'.$last_cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
            }

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':E' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':E' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);
        } else {
            //
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileVehicle($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title,  $isCheckModuleApp, $by_route = null)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if ($isCheckModuleApp) {

            if (!empty($by_route) && $by_route == "only") {

                // Sheet 1
                $spread_sheet->setActiveSheetIndex(0);
                $spread_sheet->getActiveSheet()->setTitle($sheet_title);
                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('S')->setWidth(40);
                $spread_sheet->getActiveSheet()->getColumnDimension('T')->setWidth(20);

                $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
                $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
                // $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

                $c4 = 'A4';
                $a5 = 'A5';
                $cell_AE = 'A';
                $cell_AE_last = ':E';
                $cell_HL = 'N';
                $cell_HL_last = ':R';
                $start_border = 'A8:T';

                $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
                $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
                $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

                //------------row last

                // $spread_sheet->getActiveSheet()->setCellValue($cell_AE . $last_cell, 'TỔNG CỘNG');
                // $spread_sheet->getActiveSheet()->getStyle($cell_AE . $last_cell)->applyFromArray($style_center_bold);
                // $spread_sheet->getActiveSheet()->mergeCells($cell_AE . $last_cell . $cell_AE_last . $last_cell);

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);


                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue($cell_HL . $last_cell, 'Ngày ........ tháng ........ năm ........');
                $spread_sheet->getActiveSheet()->getStyle($cell_HL . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_HL . $last_cell . $cell_HL_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_HL . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
                $spread_sheet->getActiveSheet()->getStyle($cell_HL . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_HL . $last_cell . $cell_HL_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_HL . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle($cell_HL . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_HL . $last_cell . $cell_HL_last . $last_cell);
            }

            if (!empty($by_route) && $by_route == "all") {

                // Sheet 1
                $spread_sheet->setActiveSheetIndex(0);
                $spread_sheet->getActiveSheet()->setTitle($sheet_title);
                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);

                $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
                $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
                // $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

                $c4 = 'A4';
                $a5 = 'A5';
                $cell_A = 'A';
                $cell_A_last = ':B';
                $cell_AB = 'A';
                $cell_AB_last = ':C';
                $cell_FI = 'K';
                $cell_FI_last = ':O';
                $start_border = 'A8:O';

                $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
                $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
                $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

                //------------row last

                // $spread_sheet->getActiveSheet()->setCellValue($cell_A . $last_cell, 'TỔNG CỘNG');
                // $spread_sheet->getActiveSheet()->getStyle($cell_A . $last_cell)->applyFromArray($style_center_bold);
                // $spread_sheet->getActiveSheet()->mergeCells($cell_A . $last_cell . $cell_A_last . $last_cell);

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue($cell_FI . $last_cell, 'Ngày ........ tháng ........ năm ........');
                $spread_sheet->getActiveSheet()->getStyle($cell_FI . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_FI . $last_cell . $cell_FI_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_AB . $last_cell, 'NGƯỜI LẬP BẢNG');
                $spread_sheet->getActiveSheet()->getStyle($cell_AB . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_AB . $last_cell . $cell_AB_last . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_FI . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
                $spread_sheet->getActiveSheet()->getStyle($cell_FI . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_FI . $last_cell . $cell_FI_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue($cell_AB . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle($cell_AB . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_AB . $last_cell . $cell_AB_last . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_FI . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle($cell_FI . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_FI . $last_cell . $cell_FI_last . $last_cell);
            }
        } else {

            if (!empty($by_route) && $by_route == "only") {

                // Sheet 1
                $spread_sheet->setActiveSheetIndex(0);
                $spread_sheet->getActiveSheet()->setTitle($sheet_title);
                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(23);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                // $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(40);
                // $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);

                $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
                $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
                $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
                // $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);
                // $spread_sheet->getActiveSheet()->getRowDimension(17)->setRowHeight(20);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);


                $a4 = 'A4';
                $a5 = 'A5';
                $cell_B = 'A';
                $cell_B_last = ':D';
                $cell_GH = 'F';
                $cell_GH_last = ':G';
                $start_border = 'A8:G';

                $spread_sheet->getActiveSheet()->setCellValue($a4, $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle($a4)->getFont()->setSize(18);
                $spread_sheet->getActiveSheet()->getStyle($a4)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
                $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

                //------------row last

                // $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'TỔNG CỘNG');
                // $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
                // $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . $cell_B_last . $last_cell);

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'PHÒNG KẾ TOÁN');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':E' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'BAN GIÁM ĐỐC');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':E' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);
            }

            if (!empty($by_route) && $by_route == "all") {

                // Sheet 1
                $spread_sheet->setActiveSheetIndex(0);
                $spread_sheet->getActiveSheet()->setTitle($sheet_title);
                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);

                $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
                $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(15);
                $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(20);
                $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(15);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(8);


                $a4 = 'A4';
                $a5 = 'A5';
                $cell_B = 'A';
                $cell_B_last = ':B';
                $start_border = 'A8:D';

                $spread_sheet->getActiveSheet()->setCellValue($a4, $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle($a4)->getFont()->setSize(16);
                $spread_sheet->getActiveSheet()->getStyle($a4)->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
                $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

                //------------row last

                // $spread_sheet->getActiveSheet()->setCellValue($cell_B . $last_cell, 'TỔNG CỘNG');
                // $spread_sheet->getActiveSheet()->getStyle($cell_B . $last_cell)->applyFromArray($style_center_bold);
                // $spread_sheet->getActiveSheet()->mergeCells($cell_B . $last_cell . $cell_B_last . $last_cell);

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Ngày ... tháng .... năm ....');
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':D' . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'PHÒNG KẾ TOÁN');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':C' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'BAN GIÁM ĐỐC');
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':D' . $last_cell);

                $last_cell = $last_cell + 1;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':E' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':D' . $last_cell);
            }
        }
        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileVehicleAll($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $isCheckModuleApp)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if (!$isCheckModuleApp) {

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(30);


            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

            //------------row last

            // $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'TỔNG CỘNG');
            // $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            // $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'TỔNG CỘNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell . ':F' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A7:F' . $last_cell)->applyFromArray($style_all_borders);


            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'Ngày ..... tháng ..... năm ........');
            $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'ĐỘI XE');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'PHÒNG KẾ TOÁN');
            $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'BAN GIÁM ĐỐC');
            $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);


            $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);
        } else {

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(15);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->mergeCells('A1:D1');
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->mergeCells('A2:D2');
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

            $spread_sheet->getActiveSheet()->setCellValue('G4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('G4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('G4')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->setCellValue('G5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('G5')->applyFromArray($style_center);

            //------------row last

            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'TỔNG CỘNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell . ':B' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell . ':N' . $last_cell)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A7:N' . $last_cell)->applyFromArray($style_all_borders);


            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);
            //
            // $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, 'PHÒNG KÉ TOÁN');
            // $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center_bold);
            // $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':H' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('M' . $last_cell . ':M' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            // $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, '(Ký, họ tên)');
            // $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center);
            // $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':H' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('M' . $last_cell . ':M' . $last_cell);
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }


    public function exportVehicleRoutePeriod($data)
    {

        $now_from_date = date("Y-m-d 00:00:00", strtotime($data['now_from_date']));
        $now_to_date = date("Y-m-d 23:59:59", strtotime($data['now_to_date']));
        $last_from_date = date("Y-m-d 00:00:00", strtotime($data['last_from_date']));
        $last_to_date = date("Y-m-d 23:59:59", strtotime($data['last_to_date']));
        $object_compare = $data['object_compare'];
        $object_report = $data['object_report'];
        $company_id = $data['company_id'];

        $period_arr = $this->viewVehicleRoutePeriod($data);

        $company_name = '';
        $company_address = '';

        $report_date = 'Thời gian kỳ này ' . date("d/m/Y", strtotime($now_from_date)).' - '.date("d/m/Y", strtotime($now_to_date)) . ' Thời gian kỳ trước ' . date("d/m/Y", strtotime($last_from_date)).' - '.date("d/m/Y", strtotime($last_to_date));

        // get company
        $company = $this->companies->getCompanyById($company_id);

        if ($company) {
            $company_name = mb_strtoupper($company->fullname, "UTF-8");
            $company_address = mb_strtoupper($company->address, "UTF-8");
        }

        $title = 'BÁO CÁO DOANH THU XE BUÝT VÀ TUYẾN THEO KỲ';
        $file_name = 'BaoCaoDoanhThuXeBuytVaTuyenTheoKy_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => $title,
            'quarter' => $report_date,
        ];

        if ($object_compare !== 'all') {
            //merges array
            $merges_arr = [
                'A7:A9', 'B7:B9', 'C7:H7', 'I7:N7', 'O7:T7', 'C8:C9', 'D8:H8', 'I8:I9', 'J8:N8', 'O8:O9', 'P8:T8'
            ];

            // table
            $a7_to_t9 = [
                ['STT', 'Nội dung', 'Kỳ này', null, null, null, null, null, 'Kỳ trước', null, null, null, null, null, 'So sánh kỳ này với kỳ trước (%)'],
                [null, null, 'Số lượt chạy', 'Số lượng vé/Doanh thu', null, null, null, null, 'Số lượt chạy', 'Số lượng vé/Doanh thu', null, null, null, null, 'Số lượt chạy', 'Số lượng vé/Doanh thu'],
                [null, null, null, 'Tổng cộng', 'Vé lượt', 'Thẻ trả trước', 'Thẻ tháng', 'Momo', null, 'Tổng cộng', 'Vé lượt', 'Thẻ trả trước', 'Thẻ tháng', 'Momo', null, 'Tổng cộng', 'Vé lượt', 'Thẻ trả trước', 'Thẻ tháng', 'Momo']
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_t9, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:T9')->applyFromArray($style_center_bold);
            // set data
            $lines = 1;
            $cell = 9;
            $cell_value = $lines + $cell;

            if (count($period_arr) > 0) {
                // return $period_arr;
                foreach ($period_arr as $value) {

                    $cell_value = $lines + $cell;

                    if ($value['license_plates'] !== 'all') {
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                        if ($value['license_plates'] !== '') {
                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $value['license_plates']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);
                        }

                        if ($value['route_number'] !== '') {
                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tuyến ' . $value['route_number']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);
                        }

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value['now_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value['now_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value['now_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $value['now_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $value['now_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $value['now_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $value['last_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $value['last_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $value['last_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $value['last_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $value['last_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $value['last_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $value['compare_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $value['compare_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $value['compare_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $value['compare_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $value['compare_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $value['compare_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
                    } else {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value['now_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value['now_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value['now_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $value['now_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $value['now_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $value['now_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $value['last_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $value['last_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $value['last_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $value['last_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $value['last_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('N' . $cell_value, $value['last_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('N' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('O' . $cell_value, $value['compare_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('O' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('P' . $cell_value, $value['compare_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('P' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('Q' . $cell_value, $value['compare_total_revenue_pos']);
                        $spread_sheet->getActiveSheet()->getStyle('Q' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('R' . $cell_value, $value['compare_total_revenue_charge']);
                        $spread_sheet->getActiveSheet()->getStyle('R' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('S' . $cell_value, $value['compare_total_revenue_month']);
                        $spread_sheet->getActiveSheet()->getStyle('S' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('T' . $cell_value, $value['compare_total_revenue_qr_code']);
                        $spread_sheet->getActiveSheet()->getStyle('T' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    $lines++;
                }
            }
        } else {
            //merges array
            $merges_arr = [
                'A7:A8','B7:B8','C7:E7','F7:H7','I7:K7'
            ];

            // table
            $a7_to_k8 = [
                ['STT', 'Nội dung', 'Kỳ này', null, null, 'Kỳ trước', null, null,'So sánh kỳ này với kỳ trước (%)'],
                [null, null, 'Số lượt chạy', 'Số lượng vé','Doanh thu','Số lượt chạy', 'Số lượng vé','Doanh thu','Số lượt chạy', 'Số lượng vé','Doanh thu'],
            ];
            $spread_sheet->getActiveSheet()->fromArray($a7_to_k8, NULL, 'A7');
            $spread_sheet->getActiveSheet()->getStyle('A7:K8')->applyFromArray($style_center_bold);
            // set data
            $lines = 1;
            $cell = 8;
            $cell_value = $lines + $cell;

            if (count($period_arr) > 0) {

                foreach ($period_arr as $value) {

                    $cell_value = $lines + $cell;

                    if ($value['license_plates'] !== 'all') {

                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);
                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);

                        if ($value['license_plates'] !== '') {
                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $value['license_plates']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);
                        }

                        if ($value['route_number'] !== '') {
                            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tuyến ' . $value['route_number']);
                            $spread_sheet->getActiveSheet()->getStyle('B')->applyFromArray($style_center);
                        }

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value['now_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value['now_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value['now_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $value['last_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $value['last_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $value['last_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $value['compare_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $value['compare_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $value['compare_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');

                    } else {

                        $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
                        $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);
                        $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);

                        $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $value['now_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $value['now_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $value['now_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('E' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $value['last_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('F' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, $value['last_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('G' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $value['last_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $value['compare_count_shift']);
                        $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $value['compare_total_count_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');

                        $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $value['compare_total_revenue_ticket']);
                        $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    $lines++;
                }
            }
        }

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }
        // save spread sheet
        $this->downloadFileVehicleRoutePeriod($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Doanh Thu', $data);
    }



    private function downloadFileVehiclePeriod($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $isCheckModuleApp)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if (!$isCheckModuleApp) {

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(22);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(35);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(30);


            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

            //------------row last

            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, 'TỔNG CỘNG');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('B' . $last_cell . ':B' . $last_cell);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A7:I' . $last_cell)->applyFromArray($style_all_borders);


            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, 'Ngày ..... tháng ..... năm ........');
            $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':I' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'PHÒNG KẾ TOÁN');
            $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, 'BAN GIÁM ĐỐC');
            $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':I' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, '(Ký, họ tên, đống dấu)');
            $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':I' . $last_cell);
        } else {

            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(25);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
            $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(27);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

            //------------row last

            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, 'TỔNG CỘNG');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('B' . $last_cell . ':B' . $last_cell);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A7:O' . $last_cell)->applyFromArray($style_all_borders);


            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('M' . $last_cell . ':O' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, 'PHÒNG KÉ TOÁN');
            $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':I' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, 'BAN GIÁM ĐỐC');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('M' . $last_cell . ':O' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':I' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('M' . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle('M' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('M' . $last_cell . ':O' . $last_cell);
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileTransaction($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $by_where, $data_sumary, $isCheckModuleApp)
    {
        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(35);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(25);


        if ($by_where == 'by_date') {

            if ($isCheckModuleApp) {

                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(27);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);

                $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
                $spread_sheet->getActiveSheet()->getStyle('A4:H4')->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
                $spread_sheet->getActiveSheet()->getStyle('A5:H5')->applyFromArray($style_center);

                $cell_GH = 'G';
                $cell_GH_last = ':H';
                $start_border = 'A8:H';

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);
                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['total_transactions']['total_price']);
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_sumary['total_transactions']['total_discount']);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, $data_sumary['total_transactions']['total_collected']);
                $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_bold)->getNumberFormat()->setFormatCode('#,##0');

                $last_cell = $last_cell + 1;

                //------------row last
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Loại vé');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Số lượng');
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, 'Tổng tiền');
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);

                if ($data_sumary['pos'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tiền mặt');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['pos_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['pos']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['pos_taxi'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tiền mặt taxi');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['pos_taxi_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['pos_taxi']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['online'] > 0) {
                    $last_cell = $last_cell + 1;

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Online');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['online_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['online']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['online_taxi'] > 0) {
                    $last_cell = $last_cell + 1;

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Online taxi');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['online_taxi_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['online_taxi']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['charge'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Quẹt thẻ');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['charge_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['charge']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['charge_taxi'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Quẹt thẻ taxi');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['charge_taxi_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['charge_taxi']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['charge_free'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Miễn phí');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['charge_free_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['charge_free']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['deposit'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Nạp thẻ NFC');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['deposit_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['deposit']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                if ($data_sumary['topup_momo'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Nạp thẻ ví momo');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['topup_momo_num']);
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['topup_momo']);
                    $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);
                }

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $data_sumary['sumary_all']);
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('G' . $last_cell, $data_sumary['sumary_money_all']);
                $spread_sheet->getActiveSheet()->getStyle('G' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->mergeCells('G' . $last_cell . ':H' . $last_cell);

                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                // save file
                $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
                ob_start();
                $obj_writer->save('php://output');
                $excel_output = ob_get_clean();

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
                header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
                header("Cache-Control: public");
                echo base64_encode($excel_output);
            } else {

                $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(27);
                $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                //header
                $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
                $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);

                $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
                $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
                $spread_sheet->getActiveSheet()->getStyle('A4:F4')->applyFromArray($style_center_bold);

                $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
                $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
                $spread_sheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($style_center);

                $cell_GH = 'E';
                $cell_GH_last = ':F';
                $start_border = 'A8:F';

                $last_cell = $last_cell + 1;

                //------------row last
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Loại vé');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'Số lượng');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'Tổng tiền');
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);

                if ($data_sumary['pos'] > 0) {
                    $last_cell = $last_cell + 1;
                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tiền mặt');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, $data_sumary['pos_num']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_sumary['pos']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);
                }
                if ($data_sumary['online'] > 0) {
                    $last_cell = $last_cell + 1;

                    $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Online');
                    $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, $data_sumary['online_num']);
                    $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
                    $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                    $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_sumary['online']);
                    $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                    $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);
                }

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tổng cộng');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, $data_sumary['sumary_all']);
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, $data_sumary['sumary_money_all']);
                $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold)->getNumberFormat()->setFormatCode('#,##0');
                $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':F' . $last_cell);


                //------------set border
                $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

                //footer ---------- signature
                $last_cell = $last_cell + 2;
                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'KẾ TOÁN');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'GIÁM ĐỐC');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                $last_cell = $last_cell + 1;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
                $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

                $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
                $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
                $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

                // save file
                $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
                ob_start();
                $obj_writer->save('php://output');
                $excel_output = ob_get_clean();

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
                header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
                header("Cache-Control: public");
                echo base64_encode($excel_output);
            }
        }

        if ($by_where == 'by_shift') {

            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('A4:E4')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
            $spread_sheet->getActiveSheet()->getStyle('A5:E5')->applyFromArray($style_center);

            $cell_GH = 'D';
            $cell_GH_last = ':F';
            $start_border = 'A8:F';

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            // save file
            $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
            ob_start();
            $obj_writer->save('php://output');
            $excel_output = ob_get_clean();

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
            header("Cache-Control: public");
            echo base64_encode($excel_output);
        }
    }

    private function downloadFileTransactionOnline($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $total_onlines)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(35);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(25);

        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(27);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('A4:F4')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($style_center);

        $cell_GH = 'E';
        $cell_GH_last = ':F';
        $start_border = 'A8:F';

        $last_cell = $last_cell + 1;

        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Tổng cộng');
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, $total_onlines['total']);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->getNumberFormat()->setFormatCode('#,##0');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_bold);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'KẾ TOÁN');
        $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'GIÁM ĐỐC');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('C' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('C' . $last_cell . ':D' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileInvoice($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(7);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(27);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('R')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('S')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('T')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('U')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('V')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('W')->setWidth(9);
        $spread_sheet->getActiveSheet()->getColumnDimension('X')->setWidth(8);


        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(32);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(14);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getRowDimension(9)->setRowHeight(17);
        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('B9')->applyFromArray($style_all_borders);
        $spread_sheet->getActiveSheet()->setCellValue('C9', 'Kỳ báo cuối cùng');

        $spread_sheet->getActiveSheet()->getStyle('G9')->applyFromArray($style_all_borders);
        $spread_sheet->getActiveSheet()->setCellValue('H9', 'Chuyển địa điểm');

        //header
        $spread_sheet->getActiveSheet()->setCellValue('B1', $header_excel['title']);
        $spread_sheet->getActiveSheet()->setCellValue('B2', $header_excel['date_from_to']);
        $spread_sheet->getActiveSheet()->setCellValue('B4', '[02] Tên tổ chức, cá nhân:');
        $spread_sheet->getActiveSheet()->setCellValue('B5', '[03] Mã số thuế:');
        $spread_sheet->getActiveSheet()->setCellValue('E4', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('E5', $header_excel['tax_code']);
        $spread_sheet->getActiveSheet()->setCellValue('W5', 'Đơn vị tính: số ');

        $spread_sheet->getActiveSheet()->getStyle('B1')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('B1')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('B2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('B2')->getFont()->setSize(12);

        $spread_sheet->getActiveSheet()->getStyle('B4:X9')->applyFromArray($style_bold);
        $spread_sheet->getActiveSheet()->getStyle('B4:X9')->getFont()->setSize(11);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('B12:X' . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 3;

        $spread_sheet->getActiveSheet()->setCellValue('C' . $last_cell, 'Người lập phiếu:');
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':H' . $last_cell);
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell . ':H' . $last_cell)->applyFromArray($style_all_borders);

        $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, 'Người đại diện theo pháp luật:');
        $spread_sheet->getActiveSheet()->mergeCells('N' . $last_cell . ':Q' . $last_cell);
        $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell . ':Q' . $last_cell)->applyFromArray($style_all_borders);

        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('K' . $last_cell, 'Ngày lập báo cáo:');
        $spread_sheet->getActiveSheet()->mergeCells('N' . $last_cell . ':Q' . $last_cell);
        $spread_sheet->getActiveSheet()->getStyle('N' . $last_cell . ':Q' . $last_cell)->applyFromArray($style_all_borders);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function managerHistoryShiftExport($input)
    {

        $data = $this->history_shifts->managerHistoryShiftSearch($input);
        $histories = $data['data'];
        $collecters = $data['collecte_group'];
        $total = $data['total_all'];
        //return $data;

        $company_id = $input['company_id'];

        $company_name = '';
        $company_address = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        // path root
        $file_name = 'LichSuThuTien_' . date('Ymd');

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $start_date = date("d-m-Y 00:00:00", strtotime($input['date_form']));
        $end_date = date("d-m-Y 23:59:59", strtotime($input['date_to']));

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BẢNG CHI TIẾT - LỊCH SỬ THU TIỀN',
            'quarter' => $start_date . " - " . $end_date,
        ];

        //merges array
        $merges_arr = [
            'A1:G1', 'A2:G2', 'A4:M4', 'A5:M5', 'A6:M6',
            'A7:A9', 'B7:B9', 'C7:C9', 'D7:D9', 'E7:E9', 'F7:F9', 'G7:G9', 'H7:J7', 'K7:L7', 'M7:M9',
            'H8:H9', 'I8:J8', 'K8:K9', 'L8:L9'
        ];


        // table
        $a7_to_m9 = [
            ['STT', 'Thời gian làm việc', 'Người thu', 'Lái xe', 'Phụ xe', 'Tuyến', 'Ngày thu', 'Doanh thu vé xe', NULL, NULL, 'Tổng cộng', NULL, 'Nạp thẻ'],
            [NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vé lượt', 'Thẻ', NULL, 'Tổng doanh thu', 'Tổng thực thu', NULL],
            [NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Doanh thu', 'Thực thu', NULL, NULL, NULL],
        ];


        $spread_sheet->getActiveSheet()->fromArray($a7_to_m9, NULL, 'A7');
        $spread_sheet->getActiveSheet()->getStyle('A7:M9')->applyFromArray($style_center_bold);

        // set data
        $lines = 1;
        $cell = 9;
        $cell_value = $lines + $cell;

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        if (count($histories) > 0) {

            foreach ($histories as $item) {
                $item = (object)$item;
                $cell_value = $lines + $cell;
                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $item->shift_time);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $item->user_collecte);

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $item->driver_name);

                $spread_sheet->getActiveSheet()->setCellValue('E' . $cell_value, $item->subdriver_name);
                $spread_sheet->getActiveSheet()->setCellValue('F' . $cell_value, $item->routes_name);
                $spread_sheet->getActiveSheet()->setCellValue('G' . $cell_value, date("d/m/y h:m:s", strtotime($item->created_at)));

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $item->totalPos);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $item->totalCharge);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $item->totalChargeReal);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $item->totalPos + $item->totalCharge);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $item->totalPos + $item->totalChargeReal);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $item->totalDeposit);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $lines++;
            }
            //group collecter

            foreach ($collecters as $item) {
                // $item = (array)$item;
                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, NULL);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng cộng theo ' . $item['user_collecte']['user_collecte']);
                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value . ':' . 'G' . $cell_value)->applyFromArray($style_bold);
                $spread_sheet->getActiveSheet()->mergeCells('B' . $cell_value . ':' . 'G' . $cell_value);

                $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $item['collection_totalPos']);
                $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $item['collection_totalCharge']);
                $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $item['collection_totalChargeReal']);
                $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $item['collection_totalPos'] + $item['collection_totalCharge']);
                $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $item['collection_totalPos'] + $item['collection_totalChargeReal']);
                $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $item['collection_totalDeposit']);
                $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $lines++;
            }

            //total
            $total = (array)$total;
            $cell_value = $lines + $cell;

            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, NULL);
            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

            $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, 'Tổng cộng ');
            $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value . ':' . 'G' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->mergeCells('B' . $cell_value . ':' . 'G' . $cell_value);

            $spread_sheet->getActiveSheet()->setCellValue('H' . $cell_value, $total['all_totalPos']);
            $spread_sheet->getActiveSheet()->getStyle('H' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('I' . $cell_value, $total['all_totalCharge']);
            $spread_sheet->getActiveSheet()->getStyle('I' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('J' . $cell_value, $total['all_totalChargeReal']);
            $spread_sheet->getActiveSheet()->getStyle('J' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('K' . $cell_value, $total['all_totalPos'] + $total['all_totalCharge']);
            $spread_sheet->getActiveSheet()->getStyle('K' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('L' . $cell_value, $total['all_totalPos'] + $total['all_totalChargeReal']);
            $spread_sheet->getActiveSheet()->getStyle('L' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('M' . $cell_value, $total['all_totalDeposit']);
            $spread_sheet->getActiveSheet()->getStyle('M' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
        }
        // save spread sheet
        $this->downloadFileHistory($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Thu tien');
    }

    private function downloadFileHistory($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $c4 = 'A4';
        $a5 = 'A5';
        $cell_B = 'A';
        $cell_B_last = ':D';
        $cell_GH = 'J';
        $cell_GH_last = ':M';
        $start_border = 'A7:M';

        $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

        //------------row last

        // $spread_sheet->getActiveSheet()->setCellValue($cell_B.$last_cell, 'TỔNG CỘNG');
        // $spread_sheet->getActiveSheet()->getStyle($cell_B.$last_cell)->applyFromArray($style_center_bold);
        // $spread_sheet->getActiveSheet()->mergeCells($cell_B.$last_cell.$cell_B_last.$last_cell);

        // //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);


        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'PHÒNG KINH DOANH');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'PHÒNG KẾ TOÁN');
        $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':G' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'BAN GIÁM ĐỐC');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':G' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileCardMonth($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title,$route_id)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if ($route_id == 0) {
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

            $cell_GH = 'D';
            $cell_GH_last = ':F';
            $start_border = 'A8:F';
        }


        if ($route_id > 0) {
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(45);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(35);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);

            $cell_GH = 'G';
            $cell_GH_last = ':J';
            $start_border = 'A8:J';
        }

        $c4 = 'A4';
        $a5 = 'A5';
        $cell_B = 'A';
        $cell_B_last = ':D';

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);


        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('C'.$last_cell, 'PHÒNG KẾ TOÁN');
        // $spread_sheet->getActiveSheet()->getStyle('C'.$last_cell)->applyFromArray($style_center_bold);
        // $spread_sheet->getActiveSheet()->mergeCells('C'.$last_cell.':D'.$last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        // $spread_sheet->getActiveSheet()->setCellValue('C'.$last_cell, '(Ký, họ tên)');
        // $spread_sheet->getActiveSheet()->getStyle('C'.$last_cell)->applyFromArray($style_center);
        // $spread_sheet->getActiveSheet()->mergeCells('C'.$last_cell.':D'.$last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileCard($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);

        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);


        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        //        $spread_sheet->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(8);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('A7:K' . $last_cell)->applyFromArray($style_all_borders);
        //------------row last

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':K' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':K' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('H' . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle('H' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('H' . $last_cell . ':K' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileTrip($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $type_opt)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);

        if ($type_opt == 0) {

            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(35);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(45);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(28);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('B1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('B2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('B1:B2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('B1:B2')->getFont()->setSize(8);

            $c4 = 'B4';
            $a5 = 'B5';
            $cell_B = 'B';
            $cell_B_last = ':C';
            $cell_GH = 'D';
            $cell_GH_last = ':D';
            $start_border = 'B8:D';

            $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->setCellValue('B6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('B5:B6')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày .... tháng .... năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);
        }

        if ($type_opt == 1) {


            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(35);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(28);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(8);


            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A8:E' . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Ngày .... tháng .... năm ........');
            $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);
        }

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileTripTimeDetail($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);

            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
            $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(28);
            $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('B1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('B2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('B1:B2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('B1:B2')->getFont()->setSize(8);

            $c4 = 'B4';
            $a5 = 'B5';
            $cell_B = 'B';
            $cell_B_last = ':C';
            $cell_GH = 'D';
            $cell_GH_last = ':D';
            $start_border = 'B8:E';

            $spread_sheet->getActiveSheet()->setCellValue($c4, $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle($c4)->getFont()->setSize(18);
            $spread_sheet->getActiveSheet()->getStyle($c4)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue($a5, $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->setCellValue('B6', $header_excel['route']);
            $spread_sheet->getActiveSheet()->getStyle('B5:B6')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 2;
            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Ngày .... tháng .... năm ........');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, 'NGƯỜI LẬP BẢNG');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('B' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('B' . $last_cell)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, '(Ký, họ tên, đóng dấu)');
            $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);


        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function downloadFileCardMonthForGeneral($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(20);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(8);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('A7:E' . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':E' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function downloadFileCardMonthByStaff($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {
        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route_title']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('A8:G' . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':G' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':G' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('E' . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle('E' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('E' . $last_cell . ':G' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function downloadFileCardMonthByGroupBusStation($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(25);

        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(30);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(10);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5:A5')->applyFromArray($style_center);

        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['staff']);
        $spread_sheet->getActiveSheet()->getStyle('A6:A6')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('A8:F' . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('D' . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle('D' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('D' . $last_cell . ':F' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function downloadFileTimeKeeping($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $number_day_in_month)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]]
        ];

        if($number_day_in_month == 28){
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('AE')->setWidth(12);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('S1', $header_excel['denominator']);
            $spread_sheet->getActiveSheet()->setCellValue('S2', $header_excel['promulgate']);
            $spread_sheet->getActiveSheet()->getStyle('S1:S2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('S1:S2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('A3', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(11);
            $spread_sheet->getActiveSheet()->getStyle('A3')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A4:AE4')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A5:AE' . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 3;
            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '........,ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AE' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Người chấm công');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, 'Phó giám đốc');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AE' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AE' . $last_cell);
        }

        if($number_day_in_month == 29){
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('AF')->setWidth(12);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('V1', $header_excel['denominator']);
            $spread_sheet->getActiveSheet()->setCellValue('V2', $header_excel['promulgate']);
            $spread_sheet->getActiveSheet()->getStyle('V1:V2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('V1:V2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('A3', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(11);
            $spread_sheet->getActiveSheet()->getStyle('A3')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A4:AF4')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A5:AF' . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 3;
            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '........,ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AF' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Người chấm công');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, 'Phó giám đốc');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AF' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AF' . $last_cell);
        }

        if($number_day_in_month == 30){
            // Sheet 1
            $spread_sheet->setActiveSheetIndex(0);
            $spread_sheet->getActiveSheet()->setTitle($sheet_title);
            $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spread_sheet->getActiveSheet()->getColumnDimension('AG')->setWidth(12);

            $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $spread_sheet->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

            //header
            $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
            $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('V1', $header_excel['denominator']);
            $spread_sheet->getActiveSheet()->setCellValue('V2', $header_excel['promulgate']);
            $spread_sheet->getActiveSheet()->getStyle('V1:V2')->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->getStyle('V1:V2')->getFont()->setSize(9);

            $spread_sheet->getActiveSheet()->setCellValue('A3', $header_excel['title']);
            $spread_sheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(11);
            $spread_sheet->getActiveSheet()->getStyle('A3')->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['quarter']);
            $spread_sheet->getActiveSheet()->getStyle('A4:AG4')->applyFromArray($style_center);

            //------------set border
            $spread_sheet->getActiveSheet()->getStyle('A5:AG' . $last_cell)->applyFromArray($style_all_borders);

            //footer ---------- signature
            $last_cell = $last_cell + 3;
            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '........,ngày ........ tháng ........ năm ........');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AG' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Người chấm công');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, 'Phó giám đốc');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center_bold);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AG' . $last_cell);

            $last_cell = $last_cell + 1;
            $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

            $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '(Ký, họ tên)');
            $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
            $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AG' . $last_cell);
        }

        if($number_day_in_month == 31){
              // Sheet 1
              $spread_sheet->setActiveSheetIndex(0);
              $spread_sheet->getActiveSheet()->setTitle($sheet_title);
              $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
              $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
              $spread_sheet->getActiveSheet()->getColumnDimension('AH')->setWidth(12);

              $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
              $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
              $spread_sheet->getActiveSheet()->getRowDimension(3)->setRowHeight(15);

              //header
              $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
              $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
              $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
              $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(9);

              $spread_sheet->getActiveSheet()->setCellValue('V1', $header_excel['denominator']);
              $spread_sheet->getActiveSheet()->setCellValue('V2', $header_excel['promulgate']);
              $spread_sheet->getActiveSheet()->getStyle('V1:V2')->applyFromArray($style_center_bold);
              $spread_sheet->getActiveSheet()->getStyle('V1:V2')->getFont()->setSize(9);

              $spread_sheet->getActiveSheet()->setCellValue('A3', $header_excel['title']);
              $spread_sheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(11);
              $spread_sheet->getActiveSheet()->getStyle('A3')->applyFromArray($style_center_bold);

              $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['quarter']);
              $spread_sheet->getActiveSheet()->getStyle('A4:AH4')->applyFromArray($style_center);

              //------------set border
              $spread_sheet->getActiveSheet()->getStyle('A5:AH' . $last_cell)->applyFromArray($style_all_borders);

              //footer ---------- signature
              $last_cell = $last_cell + 3;
              $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '........,ngày ........ tháng ........ năm ........');
              $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
              $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AH' . $last_cell);

              $last_cell = $last_cell + 1;
              $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Người chấm công');
              $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
              $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

              $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, 'Phó giám đốc');
              $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center_bold);
              $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AH' . $last_cell);

              $last_cell = $last_cell + 1;
              $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
              $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
              $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

              $spread_sheet->getActiveSheet()->setCellValue('AA' . $last_cell, '(Ký, họ tên)');
              $spread_sheet->getActiveSheet()->getStyle('AA' . $last_cell)->applyFromArray($style_center);
              $spread_sheet->getActiveSheet()->mergeCells('AA' . $last_cell . ':AH' . $last_cell);
        }


        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    private function downloadFileOutputByVehicle($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title, $isCheckModuleApp, $result_arr)
    {

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(35);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(20);

        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('J')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('M')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('N')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('O')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('P')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
        $spread_sheet->getActiveSheet()->getColumnDimension('R')->setWidth(12);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', 'Tên đơn vị: ' . $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', 'ĐT: ' . $header_excel['com_phone'] . '       ' . 'Mã số thuế: ' . $header_excel['com_tax_code']);
        $spread_sheet->getActiveSheet()->setCellValue('A5', 'Cấp cho lái xe: ' . ($result_arr['result_arr'][0]->driver_name ?? '') . '             ' . ' Hạng GPLX:.........');
        $spread_sheet->getActiveSheet()->setCellValue('A6', 'NVPV1: ' . ($result_arr['result_arr'][0]->subdriver_name ?? '') . '                  ' . ' NVPV2:.....................................');
        $spread_sheet->getActiveSheet()->setCellValue('A7', 'Biển xe đăng ký: ' . ($result_arr['result_arr'][0]->license_plate ?? '') . '             ' . '  Loại xe:.................................');
        $spread_sheet->getActiveSheet()->setCellValue('A8', 'Thời hạn kiểm định lưu hành lần sau:...................................');
        $spread_sheet->getActiveSheet()->setCellValue('A9', 'Chạy tuyến: ' . ($result_arr['result_arr'][0]->route_name ?? '') . '      ' . ' Mã số tuyến: ' . ($result_arr['result_arr'][0]->route_number ?? '') );
        $spread_sheet->getActiveSheet()->setCellValue('A10', 'Ngày vận chuyển: ' . ( isset($result_arr['result_arr'][0]->ended) ? (date('d-m-Y', strtotime($result_arr['result_arr'][0]->ended))) :  '') . '           ' . ' Nốt tài bắt đầu: ' . ($result_arr['result_arr'][0]->station_start ?? ''));

        $spread_sheet->getActiveSheet()->setCellValue('P1', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM');
        $spread_sheet->getActiveSheet()->setCellValue('P2', 'Độc lập - Tự do - Hạnh phúc');
        $spread_sheet->getActiveSheet()->setCellValue('P5', '.........., ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));

        $spread_sheet->getActiveSheet()->setCellValue('M6', 'Cán bộ kiểm tra');
        $spread_sheet->getActiveSheet()->setCellValue('M7', 'Kiểm tra xe');

        $spread_sheet->getActiveSheet()->setCellValue('I4', 'LỆNH VẬN CHUYỂN');
        $spread_sheet->getActiveSheet()->setCellValue('Q6', 'Thủ trưởng đơn vị');
        $spread_sheet->getActiveSheet()->setCellValue('Q7', '(Ký tên, đóng dấu)');

        $spread_sheet->getActiveSheet()->setCellValue('I12', 'Phần thống kê, cập nhật sản lượng');
        $spread_sheet->getActiveSheet()->getStyle('I12')->getFont()->setSize(13);
        $spread_sheet->getActiveSheet()->getStyle('I12')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);
        $spread_sheet->getActiveSheet()->getStyle('P1:P2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('P1:P2')->getFont()->setSize(11);
        $spread_sheet->getActiveSheet()->getStyle('M6:Q6')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('M7:Q7')->applyFromArray($style_center);

        $spread_sheet->getActiveSheet()->getStyle('I4')->getFont()->setSize(15);
        $spread_sheet->getActiveSheet()->getStyle('I4')->applyFromArray($style_center_bold);

        // $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        // $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
        // $spread_sheet->getActiveSheet()->getStyle('A5:E5')->applyFromArray($style_center);

        $cell_GH = 'H';
        $cell_GH_last = ':J';
        $start_border = 'A14:R';

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 3;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'Lái xe');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Nhân viên phục vụ');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, 'Cán bộ tổng hợp của đơn vị vận tải');
        $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('P' . $last_cell . ':R' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký và ghi rõ họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký và ghi rõ họ tên)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('P' . $last_cell, '(Ký và ghi rõ họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('P' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('P' . $last_cell . ':R' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function downloadFileShiftSupervisor($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {
        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(35);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(7)->setRowHeight(20);

        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('G')->setWidth(40);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(8);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $spread_sheet->getActiveSheet()->getStyle('A4')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5')->applyFromArray($style_center);

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle('A7:G' . $last_cell)->applyFromArray($style_all_borders);
        //------------row last

        //footer ---------- signaturE
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('F' . $last_cell . ':G' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('F' . $last_cell . ':G' . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':C' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue('F' . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle('F' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('F' . $last_cell . ':G' . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }
    //----------------------------------------------function public -----------------------//
    public function convertNumberToString($data)
    {

        $number = $data['number_convert'];
        $company_id = $data['company_id'];

        return convert_number_to_words($number);
    }

    public function editStringYearByDateTime($date)
    {

        $year =  substr($date, 0, 4);
        $month_day = substr($date, -6);
        $year_last = round($year) - 1;
        return   $year_last . $month_day;
    }

    public function isCheckModuleApp($company_id)
    {
        $isCheckModuleApp = false;

        //get module app by company id
        $module_app_arr = $this->module_apps->getModuleKeyByCompanyId($company_id);

        if (in_array('ve_luot', $module_app_arr) &&
            (
                in_array('the_tra_truoc', $module_app_arr)      ||
                in_array('the_km', $module_app_arr)             ||
                in_array('the_dong_gia', $module_app_arr)       ||
                in_array('qr_code', $module_app_arr)            ||
                in_array('module_vc_hang_hoa', $module_app_arr) ||
                in_array('module_tt_sl_quet', $module_app_arr)  ||
                in_array('module_tt_km', $module_app_arr)       ||
                in_array('module_taxi', $module_app_arr)        ||
                in_array('module_in_ve_the', $module_app_arr)   ||
                in_array('module_in_ve_momo', $module_app_arr)
            )
        ) {
            $isCheckModuleApp = true;
        }
        return $isCheckModuleApp;
    }

    public function viewCardExemption($data)
    {
        $result = [];
        $from_date = date("Y-m-d 00:00:00", strtotime($data['from_date']));
        $to_date = date("Y-m-d 23:59:59", strtotime($data['to_date']));
        $route_id = (int)$data['route_id'] ?? 0;
        $company_id = $data['company_id'];

        $where = [
            ["transactions.company_id", $company_id],
            ["transactions.activated", '>=',$from_date],
            ["transactions.activated", '<=',$to_date],
            ["transactions.ticket_destroy", '!=', 1],
        ];

        if($route_id > 0) $where[] = ['shifts.route_id', '=', $route_id];
        $membership_types = $this->membership_types->getMembershipTypeByDeductionAndCompanyId($company_id);

        foreach($membership_types as $index) {
            $obj = new \stdClass;
            $obj->deduction = $index->deduction;
            $obj->total_ticket = 0;
            $obj->total_amount = 0;
            $obj->subject_data = [];//Detail

            $route = $this->routes->getRouteById((int) $route_id);
            $obj->route_name = $route ? "Tuyến: $route->name" : "Tất cả các tuyến";

            $transactions = Membership::join('rfidcards', 'rfidcards.id', '=', 'memberships.rfidcard_id')
                ->join('transactions', 'transactions.rfid', '=', 'rfidcards.rfid')
                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->join('routes', 'routes.id', '=', 'shifts.route_id')
                ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                ->leftJoin('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                ->where('memberships.membershiptype_id', '=', $index->id)
                ->where($where)
                ->whereIn('transactions.type', ['charge', 'charge_free'])
                ->orderByRaw(
                    "shifts.route_id, transactions.activated"
                )
                ->select(
                    'memberships.fullname',
                    'transactions.activated',
                    'ticket_prices.price as ticket_price',
                    'transactions.amount',
                    'transactions.type',
                    'bus_stations.name as bus_station_name',
                    'routes.number as route_number'
                )->get();

                if(count($transactions) > 0) {
                    $sum_of_prices = 0;
                    foreach ($transactions as $value) {
                        $sub_obj = new \stdClass;
                        $sub_obj->fullname = $value->fullname;
                        $sub_obj->bus_station_name = $value->bus_station_name;
                        $sub_obj->route_number = $value->route_number;
                        $sub_obj->activated = $value->activated;
                        $sub_obj->type = $value->type;//
                        $sub_obj->sum_of_prices = 0;

                        if($value->type == 'charge') {
                            $obj->total_ticket += 1;
                            $obj->total_amount += $value->ticket_price - $value->amount;
                            $sub_obj->ticket_price = $value->ticket_price ?? 0;
                        }

                        if($value->type == 'charge_free') {
                            $obj->total_ticket += 1;
                            $obj->total_amount += $value->amount;
                            $sub_obj->ticket_price = $value->amount ?? 0;
                        }

                        $sum_of_prices += $sub_obj->ticket_price;
                        $sub_obj->sum_of_prices += $sum_of_prices;
                        $obj->subject_data[] = $sub_obj;
                    }
                }

            $result[] = $obj;
        }

        return $result;
    }

    private function downloadFileCardExemption($spread_sheet, $extension, $file_name, $header_excel, $last_cell, $sheet_title)
    {
        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true]
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Sheet 1
        $spread_sheet->setActiveSheetIndex(0);
        $spread_sheet->getActiveSheet()->setTitle($sheet_title);
        $spread_sheet->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
        $spread_sheet->getActiveSheet()->getRowDimension(4)->setRowHeight(35);
        $spread_sheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spread_sheet->getActiveSheet()->getRowDimension(8)->setRowHeight(25);

        $spread_sheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $spread_sheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('C')->setWidth(27);
        $spread_sheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $spread_sheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        //header
        $spread_sheet->getActiveSheet()->setCellValue('A1', $header_excel['com_name']);
        $spread_sheet->getActiveSheet()->setCellValue('A2', $header_excel['com_addr']);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(11);

        $spread_sheet->getActiveSheet()->setCellValue('A4', $header_excel['title']);
        $spread_sheet->getActiveSheet()->getStyle('A4')->getFont()->setSize(18);
        $spread_sheet->getActiveSheet()->getStyle('A4:F4')->applyFromArray($style_center_bold);

        $spread_sheet->getActiveSheet()->setCellValue('A5', $header_excel['quarter']);
        $spread_sheet->getActiveSheet()->getStyle('A5')->getFont()->setSize(13)->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('A5:F5')->applyFromArray($style_center);

        $spread_sheet->getActiveSheet()->setCellValue('A6', $header_excel['route_name']);
        $spread_sheet->getActiveSheet()->getStyle('A6')->getFont()->setSize(13)->setItalic(true);
        $spread_sheet->getActiveSheet()->getStyle('A6:F6')->applyFromArray($style_center);

        $cell_GH = 'E';
        $cell_GH_last = ':F';
        $start_border = 'A8:F';

        //------------set border
        $spread_sheet->getActiveSheet()->getStyle($start_border . $last_cell)->applyFromArray($style_all_borders);

        //footer ---------- signature
        $last_cell = $last_cell + 2;
        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'Ngày ........ tháng ........ năm ........');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, 'NGƯỜI LẬP BẢNG');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, 'TRƯỞNG BAN ĐIỀU HÀNH');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        $last_cell = $last_cell + 1;
        $spread_sheet->getActiveSheet()->setCellValue('A' . $last_cell, '(Ký, họ tên)');
        $spread_sheet->getActiveSheet()->getStyle('A' . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells('A' . $last_cell . ':B' . $last_cell);

        $spread_sheet->getActiveSheet()->setCellValue($cell_GH . $last_cell, '(Ký, họ tên, đóng dấu)');
        $spread_sheet->getActiveSheet()->getStyle($cell_GH . $last_cell)->applyFromArray($style_center);
        $spread_sheet->getActiveSheet()->mergeCells($cell_GH . $last_cell . $cell_GH_last . $last_cell);

        // save file
        $obj_writer = IOFactory::createWriter($spread_sheet, $extension);
        ob_start();
        $obj_writer->save('php://output');
        $excel_output = ob_get_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name . '.xls');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30758400));
        header("Cache-Control: public");
        echo base64_encode($excel_output);
    }

    public function exportCardExemption($data) {

        $company_id = $data['company_id'];
        $route_id = $data['route_id'] ?? 0;

        if ($route_id == 0) {
            $route_name_title = 'Tất cả các tuyến';
        } else {
            $route_obj = $this->routes->getRouteById($route_id, $company_id);
            $route_name_title = $route_obj ? 'Tuyến: '.$route_obj->name : '';
        }

        // create and save excel
        $spread_sheet = new Spreadsheet();
        $spread_sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spread_sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(17);
        $spread_sheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        $style_center_bold = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_bold = [
            'font' => ['bold' => true],
        ];

        $style_center =  [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $style_all_borders = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        $company_name = '';
        $company_address = '';

        // get company
        $company = $this->companies->getCompanyById($company_id);
        if ($company) {
            $company_name = $company->fullname;
            $company_address = $company->address;
        }

        //header excel
        $header_excel = [
            'com_name' => $company_name,
            'com_addr' => $company_address,
            'title' => 'BÁO CÁO THẺ MIỄN GIẢM',
            'quarter' => 'Từ ngày ' . date("d/m/Y", strtotime($data['from_date'])) . " đến ngày " . date("d/m/Y", strtotime($data['to_date'])),
            'route_name' => $route_name_title
        ];

        // path root
        $file_name = 'BaoCaoTheMienGiam_' . date('dd-MM-yyyy');

        //merges array
        $merges_arr = ['A1:C1', 'A2:C2', 'A4:F4', 'A5:F5', 'A6:F6', 'A7:F7', 'D8:F8'];

        //merges
        foreach ($merges_arr as $merge) {
            $spread_sheet->getActiveSheet()->mergeCells($merge);
        }

        // table
        $a8_to_f8 = [
            ['STT', 'LOẠI THẺ', 'SỐ LƯỢT', 'TỔNG SỐ TIỀN ĐƯỢC GIẢM'],
        ];

        $spread_sheet->getActiveSheet()->fromArray($a8_to_f8, NULL, 'A8');
        $spread_sheet->getActiveSheet()->getStyle('A8:F8')->applyFromArray($style_center_bold);
        $spread_sheet->getActiveSheet()->getStyle('A8:F8')->applyFromArray($style_all_borders);

        // set data
        $lines = 1;
        $cell = 8;
        $cell_value = $lines + $cell;

        $transactions_arr = $this->viewCardExemption($data);

        if(count($transactions_arr) > 0) {

            $sum_of_tickets = 0;
            $sum_of_amount = 0;

            foreach ($transactions_arr as $transaction) {
                $cell_value = $lines + $cell;

                $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, $lines);
                $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('B' . $cell_value, $transaction->deduction."%");
                $spread_sheet->getActiveSheet()->getStyle('B' . $cell_value)->applyFromArray($style_center);

                $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $transaction->total_ticket);
                $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $transaction->total_amount);
                $spread_sheet->getActiveSheet()->mergeCells('D' . $cell_value . ':F' . $cell_value);
                $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

                //handle total
                $sum_of_tickets += $transaction->total_ticket;
                $sum_of_amount += $transaction->total_amount;
                $lines++;
            }

            $cell_value++;

            $spread_sheet->getActiveSheet()->setCellValue('A' . $cell_value, 'Tổng cộng');
            $spread_sheet->getActiveSheet()->mergeCells('A' . $cell_value . ':B' . $cell_value);
            $spread_sheet->getActiveSheet()->getStyle('A' . $cell_value)->applyFromArray($style_center_bold);

            $spread_sheet->getActiveSheet()->setCellValue('C' . $cell_value, $sum_of_tickets);
            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->getStyle('C' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');

            $spread_sheet->getActiveSheet()->setCellValue('D' . $cell_value, $sum_of_amount);
            $spread_sheet->getActiveSheet()->mergeCells('D' . $cell_value . ':F' . $cell_value);
            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->applyFromArray($style_bold);
            $spread_sheet->getActiveSheet()->getStyle('D' . $cell_value)->getNumberFormat()->setFormatCode('#,##0');
        }

        //save spread sheet
        $this->downloadFileCardExemption($spread_sheet, "Xls", $file_name, $header_excel, $cell_value, 'Thẻ miễn giảm');
    }

}

function convert_number_to_words($number)
{
    $formatter = new \NumberFormatter("vi-VN", \NumberFormatter::DECIMAL);
    $num_parts = explode('.', $formatter->format($number));
    $num_parts = array_reverse($num_parts);
    $output = '';

    $phan_cach = ['ngàn', 'triệu', 'tỷ'];
    $so = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $chuc = ['lẻ', 'mười', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $don_vi = ['', 'một', 'hai', 'ba', 'bốn', 'lăm', 'sáu', 'bảy', 'tám', 'chín'];

    foreach ($num_parts as $key => $num_part) {

        $num_part = strrev($num_part);
        $hang_don_vi = intval($num_part[0]);
        $hang_chuc = strlen($num_part) > 1 ? intval($num_part[1]) : -1;
        $hang_tram = strlen($num_part) > 2 ? intval($num_part[2]) : -1;
        $block = '';

        if ($num_part == '000') {

            if ($key >= 1) {
                $output = ' lẻ' . $output;
            }
            continue;
        }

        // hang tram
        if ($hang_tram > -1) {
            $block .= $so[$hang_tram] . ' trăm';
        }

        // hang chuc
        if ($hang_chuc > -1) {
            if ($hang_chuc == 0) {
                if ($hang_don_vi > 0) {
                    $block .= ' lẻ';
                }
            } elseif ($hang_chuc == 1) {
                $block .= ' ' . $chuc[$hang_chuc];
            } else {
                $block .= ' ' . $chuc[$hang_chuc] . ' mươi';
            }
        }

        // hang don vi
        if ($hang_don_vi == 1) {
            if ($hang_chuc > 1)
                $block .= ' mốt';
            else
                $block .= ' một';
        } elseif ($hang_don_vi == 5) {
            if ($hang_chuc > 0)
                $block .= ' lăm';
            else
                $block .= ' năm';
        } else {
            $block .= ' ' . $don_vi[$hang_don_vi];
        }

        if ($key > 0) {
            $block .= ' ' . $phan_cach[($key - 1) % 3];
        }

        $output = $block . ' ' . $output;
    }
    if(!empty($output) || !$output == null){

        return ucfirst($output) . 'đồng';
    }else{
        return  'không đồng';
    }
}
