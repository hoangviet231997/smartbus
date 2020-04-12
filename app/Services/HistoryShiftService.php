<?php

namespace App\Services;
use App\Models\HistoryShift;
use App\Services\TicketPricesService;
use App\Services\UsersService;
use App\Models\Transaction;
use DB;

class HistoryShiftService
{
     /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_prices;

    /**
     * @var App\Services\UsersService
     */
    protected $users;
    

    public function __construct(TicketPricesService $ticket_prices, UsersService $users )
    {
        $this->ticket_prices = $ticket_prices;
        $this->users = $users;
    }

    public function getHisttoryShifts($data){
        $limit = $data['limit'];
        if (empty($limit) && $limit < 0)
            $limit = 10;

        $historyShifts = [];
        if (!$data['user_id']) {
            $start_date = date("Y-m-d 00:00:00", strtotime($data['date']));
            $end_date = date("Y-m-d 23:59:59", strtotime($data['date_to']));
            $orWheres = [
                ['created_at', '>=' , $start_date],
                ['created_at', '<=' , $end_date]
            ];
            $historyShifts = HistoryShift::where($orWheres)
                                    ->with('shift', 'user')
                                    ->paginate($limit);
        } else {
            $user_id = $data['user_id'];
            $historyShifts = HistoryShift::where('user_id', $user_id)
                                    ->with('shift', 'user')
                                    ->paginate($limit);
        }
            
        return $historyShifts;
    }

    public function created($shift, $user_id){

        if ($shift) {

            $history_shift = $this->getHistoryShiftByShiftId($shift['shift_id']);
            
            if(!empty($history_shift)){

                $history_shift->amount = $shift['total_price'];
                $history_shift->deposit = $shift['deposit'];
                $history_shift->user_id = $user_id;
                // $history_shift->created_at = date("Y-m-d h:m:s");
                // $history_shift->updated_at = date("Y-m-d h:m:s");

                if ($history_shift->save()) {
                    return $history_shift;
                }
    
            }else{
                //create history
                $historyShift = new HistoryShift();
                $historyShift->user_id = $user_id;
                $historyShift->shift_id = $shift['shift_id'];
                $historyShift->amount = $shift['total_price'];
                $historyShift->deposit = $shift['deposit'];
                $historyShift->shift_time = $shift['shift_time'];

                if ($historyShift->save()) {
                    return $historyShift;
                }
            }           
            return false;
        }

        return response('No data', 404);
    }

    public function managerHistoryShiftSearch($data)
    {
        $company_id = $data['company_id'];
        $start_date = date("Y-m-d 00:00:00", strtotime($data['date_form']));
        $end_date = date("Y-m-d 23:59:59", strtotime($data['date_to']));
        $user_id = $data['user_id'];
        $user_collected_id = $data['user_collected_id'];

        $results = ['data' => []];
        $obj_total = new \stdClass;
        $obj_total->all_totalPos =  0;
        $obj_total->all_totalCharge = 0;
        $obj_total->all_totalChargeReal = 0;
        $obj_total->all_totalDeposit = 0;

        $orwhere = [
            ['history_shifts.created_at', '>=', $start_date],
            ['history_shifts.created_at', '<=', $end_date],
            ['users.company_id', '=', $company_id]
        ];

        if ($user_id > 0 || $user_id != null) {
            $user  = $this->users->getUsersById($user_id);
            if (!empty($user) && $user->role->name == 'driver') {
                array_push($orwhere, ['shifts.user_id', '=', $user_id]);
            } else if (!empty($user) && $user->role->name == 'subdriver') {
                array_push($orwhere, ['shifts.subdriver_id', '=', $user_id]);
            }
        }

        if ($user_collected_id > 0 || $user_collected_id != null) {
            array_push($orwhere, ['history_shifts.user_id', '=', $user_collected_id]);
        }

        $history_shifts = HistoryShift::join('users', 'history_shifts.user_id', '=', 'users.id')
            ->join('shifts', 'history_shifts.shift_id', '=', 'shifts.id')
            ->join('routes', 'shifts.route_id', '=', 'routes.id')
            ->where($orwhere)
            ->selectRaw('
                history_shifts.id,
                history_shifts.amount,
                history_shifts.deposit,
                history_shifts.shift_id,
                history_shifts.shift_time,
                history_shifts.created_at,
                users.id as user_collecte_id,
                users.fullname as user_collecte,
                routes.name as routes_name,
                shifts.subdriver_id,
                shifts.user_id
            ')->orderBy('user_collecte_id')->get();

        if (count($history_shifts)) {
            
            foreach ($history_shifts as $keys => $history_shift) {

                $tmp_arr = [
                    'id' => $history_shift->id,
                    'amount' => $history_shift->amount,
                    'deposit' => $history_shift->deposit,
                    'created_at' => $history_shift->created_at->toDateTimeString(),
                    'user_collecte' => $history_shift->user_collecte,
                    'user_collecte_id' => $history_shift->user_collecte_id,
                    'routes_name' => $history_shift->routes_name,
                    'driver_name' => '',
                    'subdriver_name' => '',
                    'shift_id' => $history_shift->shift_id,
                    'role_id' => 0,
                    'shift_time' => $history_shift->shift_time,
                    'totalPos' => 0,
                    'totalCharge' => 0,
                    'totalChargeReal' => 0,
                    'totalDeposit' => 0,
                ];

                if (round($history_shift->user_id) > 0) {
                    $driver = $this->users->getUserByKey('id', $history_shift->user_id, $company_id);
                    $tmp_arr['driver_name'] = $driver['fullname'];
                    $tmp_arr['role_id'] = $driver['role']['id'];
                }

                // get subdriver
                if (round($history_shift->subdriver_id) > 0) {
                    $subdriver = $this->users->getUserByKey('id', $history_shift->subdriver_id, $company_id);
                    $tmp_arr['subdriver_name'] = $subdriver['fullname'];
                }
                $transactions = Transaction::where('shift_id', $history_shift->shift_id)
                    ->where('ticket_destroy', '!=', 1)
                    ->whereIn('type', ['pos', 'charge', 'deposit', 'deposit_month'])
                    ->join('ticket_prices', 'ticket_prices.id', '=', 'transactions.ticket_price_id')
                    ->selectRaw('ticket_prices.price, transactions.*')
                    ->get();

                $group_transactions_by_type = collect($transactions)->groupBy('type');
                $tmp_arr['totalPos'] = isset($group_transactions_by_type['pos']) ? $group_transactions_by_type['pos']->sum('amount') : 0;
                $tmp_arr['totalDeposit'] = isset($group_transactions_by_type['deposit']) ? $group_transactions_by_type['deposit']->sum('amount') : 0;
                if (isset($group_transactions_by_type['charge'])) {
                    $tmp_arr['totalChargeReal'] =  $group_transactions_by_type['charge']->sum('amount');
                    $tmp_arr['totalCharge'] = $group_transactions_by_type['charge']->sum('price');
                }
                $results['data'][] = $tmp_arr;
            }
        }

        $collect = collect($results['data'])->groupBy('user_collecte_id');
        foreach ($collect as $keys => $values) {
            $collecter_arr = [
                'collection_totalPos' => $values->sum('totalPos'),
                'collection_totalCharge' => $values->sum('totalCharge'),
                'collection_totalChargeReal' => $values->sum('totalChargeReal'),
                'collection_totalDeposit' => $values->sum('totalDeposit'),
                'user_collecte' => $values[0]
            ];
            $results['collecte_group'][] = (array) $collecter_arr;

            $obj_total->all_totalPos = collect($results['collecte_group'])->sum('collection_totalPos');
            $obj_total->all_totalCharge = collect($results['collecte_group'])->sum('collection_totalCharge');
            $obj_total->all_totalChargeReal = collect($results['collecte_group'])->sum('collection_totalChargeReal');
            $obj_total->all_totalDeposit = collect($results['collecte_group'])->sum('collection_totalDeposit');
            $results['total_all'] = $obj_total;
        }
        return $results;
    }

    public function getHistoryShiftByShiftId($shift_id){

      
        $history_shift = HistoryShift::where('shift_id', $shift_id)
                    ->with('shift', 'user')
                    ->orderBy('created_at', 'DESC')
                    ->first();
            
        return $history_shift;
    }

    public function getHistoryShiftByOption($data, $orderby)
    {
        return HistoryShift::where('created_at', '>=', $data['from_date'])
                        ->where('created_at', '<=', $data['to_date'])
                        ->where('shift_id', '=', $data['shift_id'])
                        ->with('shift', 'user')
                        ->orderBy($orderby, 'DESC')
                        ->first();
        
        return response('Transaction not found', 404);
    }
}