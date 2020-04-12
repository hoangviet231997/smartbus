<?php
namespace App\Services;

use App\Models\Transaction;
use App\Services\DevicesService;
use App\Services\ShiftsService;
use App\Services\TicketPricesService;
use App\Services\TicketAllocatesService;
use App\Services\PushLogsService;
use App\Services\UsersService;
use App\Services\MembershipsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Log;
use DB;

class TransactionsService
{
    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_prices;

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

     /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\TicketAllocatesService
     */
    protected $ticket_allocates;

     /**
     * @var App\Services\MembershipsService
     */
    protected $memberships;


    public function __construct(
        DevicesService $devices,
        ShiftsService $shifts,
        TicketPricesService $ticket_prices,
        PushLogsService $push_logs,
        UsersService $users,
        TicketAllocatesService $ticket_allocates,
        MembershipsService $memberships
    )
    {
        $this->devices = $devices;
        $this->shifts = $shifts;
        $this->ticket_prices = $ticket_prices;
        $this->push_logs = $push_logs;
        $this->users = $users;
        $this->ticket_allocates = $ticket_allocates;
        $this->memberships = $memberships;
    }

    public function checkExistTicketNumberAndCompanyId($ticket_number, $ticket_price_id, $company_id, $sign, $type){
        return Transaction::where([
            'ticket_number' => $ticket_number,
            'ticket_price_id' => $ticket_price_id,
            'company_id' => $company_id,
            'type' => $type,
            'sign' => $sign])->exists();
    }

    // public function checkExistTicketByOption($company_id, $rfid, $amount, $activated){
    //     return Transaction::where([
    //             'company_id' => $company_id,
    //             'rfid' => $rfid,
    //             'amount' => $amount,
    //             'activated' => $activated
    //         ])->exists();
    // }

    public function insertTicket($data)
    {

        $company_id = $data['company_id'];
        $ticket_number = $data['ticket_number'] ?? null;
        $type = $data['type'];
        $user_id = $data['user_id'];
        $ticket_type_id = $data['ticket_type_id'] ?? null;
        $ticket_price_id = $data['ticket_price_id'] ?? null;
        $amount = $data['amount'];
        $device_id = $data['device_id'];
        $timetamp =  $data['created'];
        $link_company_id = $data['link_company_id'] ?? null;

        // get shift
        $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $user_id);

        if (empty($shift)) {
            return response('Shift not found', 404);
        }
        $data['shift_id'] = $shift->id;

        if ($ticket_number == '' && ($type == 'pos' || $type == 'charge' || $type == 'qrcode' || $type == 'deposit_month' )) {
            return response('Create error', 404);
        }

        if (round($ticket_type_id) > 0) {

            $ticket_price = null;
            // get ticket price
            if(!empty($ticket_price_id)){
                $ticket_price = $this->ticket_prices->getPriceById($ticket_price_id);
            }else{
                $ticket_price = $this->ticket_prices->getPriceByTicketTypeIdAndPrice($ticket_type_id);
            }

            if ($ticket_price) {
                $data['ticket_price_id'] = $ticket_price->id;
                $data['duration'] = $ticket_price->ticketType->duration ?? 0;
            }
        }

        if($ticket_type_id == null && $type != "charge_month"){
            $data['ticket_price_id'] = null;
        }

        $result = $this->createTransaction($data);

        if (!$result) {
            return response('Create error', 404);
        }

        return $result;
    }

    public function redeemTicket($data)
    {
        $company_id = $data['company_id'];
        $ticket_number = $data['ticket_number'];
        $device_id = $data['device_id'];
        $user_id = $data['user_id'];
        $updated = $data['updated'];

        // get Transaction
        $transaction = Transaction::where('company_id', $company_id)
                            ->where('ticket_number', $ticket_number)
                            ->first();

        if (empty($transaction)) {
            return response('Transaction not found', 404);
        }

        // get shift
        $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $user_id);

        if (empty($transaction->shift_id) && $shift) $transaction->shift_id = $shift->id;

        if (empty($transaction->device_id)) $transaction->device_id = $device_id;

        if (empty($transaction->user_id)) $transaction->user_id = $user_id;

        if (empty($transaction->activated)) {
            $transaction->activated = $updated;

            $transaction_pl = $transaction->toArray();

            unset($transaction_pl['device_id']);
            unset($transaction_pl['shift_id']);
            unset($transaction_pl['ticket_price_id']);
            unset($transaction_pl['user_id']);

            $transaction_pl['startdate'] = intval(strtotime($updated));
            $transaction_pl['moreinfo'] = null;
            $transaction_pl['expiration'] = intval(strtotime($updated) + $transaction_pl['duration']);
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $transaction_pl['id'];
            $push_log['subject_type'] = 'voucher';
            $push_log['subject_data'] = $transaction_pl;
            $push_log['expiration'] = date("Y-m-d H:i:s", $transaction_pl['expiration']);

            $this->push_logs->createPushLog($push_log);

        }

        $transaction->updated_at = empty($updated) ? date("Y-m-d h:i:s") : $updated;

        if ($transaction->save()) {

            try {

                $client = new Client();
                $response = $client->request('GET', 'http://phuquocbustour.com/api/Update', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json'
                        ],
                        'query' => [
                            'apikey' => 'ABC123',
                            'code' => $ticket_number,
                            'status'=>'2'
                        ]
                ]);

            } catch(ClientException $ce){

                $message = $ce->getMessage();
            } catch(RequestException $re){

                $message = $re->getMessage();
            } catch (Exception $e) {

            }

            // $client = new GuzzleHttp\Client();

            // TODO get app by appId -> $app
            // $callback = json_decode $app->callback
            // array_walk_recursive($callback['options'], function($item, $key) {
            //     str_replace('$ticket_number', $ticket_numer, $item);
            // });

            // $res = $client->request($callback['method'],
            //     $callback['baseurl'],
            //     [
            //         'headers' => [
            //             'Accept'     => 'application/json',
            //         ],
            //         'query' => [
            //             'apikey' => 'ABC123',
            //             'code' => '$ticket_number',
            //             'status'=>'1'
            //         ]
            //     ]
            // );

            return ['status' => true, 'message' => 'Ok'];
        }

        return response('Redeem ticket faild', 404);
    }

    // public function extendTicket($data){

    //     $user_id = $data['user_id'];
    //     $device_id = $data['device_id'];

    //     // get shift
    //     $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $user_id);

    //     if (empty($shift)) {
    //         return response('Shift not found', 404);
    //     }
    //     $data['shift_id'] = $shift->id;

    //     $result = $this->createTransactionForExtendTicket($data);

    //     if (!$result) {
    //         return response('Create error', 404);
    //     }

    //     return $result;
    // }

    public function createTransaction($data) {

        $type = $data['type'] ?? null;
        $company_id = $data['company_id'];
        $rfid = $data['rfid'] ?? null;
        $ticket_price_id = $data['ticket_price_id'] ?? null;
        $activated = $data['activated'];

        $transaction = new Transaction();
        $transaction->company_id = $company_id;
        $transaction->device_id = $data['device_id'];
        $transaction->shift_id = $data['shift_id'];
        $transaction->ticket_price_id = $ticket_price_id ?? null;
        $transaction->ticket_number = $data['ticket_number'] ?? null;

        if($type == "charge_month"){

            $transaction_tmp1 = Transaction::where('company_id', $company_id)
                                ->where('type','deposit_month')
                                ->where('ticket_destroy','!=', 1)
                                ->where('rfid', $rfid)
                                ->where('ticket_price_id', $ticket_price_id)
                                ->where('created_at', date('Y-m-01 00:00:00'))
                                ->orderBy('created_at', 'DESC')
                                ->first();

            if($transaction_tmp1){ $transaction->ticket_number = $transaction_tmp1->ticket_number; }
        }

        $transaction->station_data = $data['station_data'] ?? null;
        $transaction->duration = $data['duration'] ?? 0;
        $transaction->type = $type;
        $transaction->user_id = $data['user_id'];
        $transaction->amount = $data['amount'];
        $transaction->station_id = $data['station_id'] ?? null;
        $transaction->station_down = $data['station_down'] ?? null;
        $transaction->rfid = $rfid ?? null;
        $transaction->sign = $data['sign'] ?? null;
        $transaction->balance = $data['balance'] ?? null;
        $transaction->transaction_code = $data['transaction_code'] ?? null;
        $transaction->ticket_destroy = 0;
        $transaction->activated = empty($activated)? date("Y-m-d h:i:s"): $activated;
        $transaction->created_at = empty($data['created']) ? date("Y-m-d h:i:s"): $data['created'];

        if($type == "deposit_month"){

            if($data['start_expiration_date'] != null && $data['end_expiration_date'] != null){

                $transaction->created_at = $data['end_expiration_date'].'-01 00:00:00';
            }else{
                $transaction_tmp2 = Transaction::where('company_id', $company_id)
                                ->where('type','deposit_month')
                                ->where('ticket_destroy','!=', 1)
                                ->where('rfid', $rfid)
                                ->where('ticket_price_id', $ticket_price_id)
                                ->orderBy('created_at', 'DESC')
                                ->orderBy('id', 'DESC')
                                ->first();

                if($transaction_tmp2){

                    $d1 = strtotime(date('Y-m-01 00:00:00'));
                    $d2 = strtotime(date('Y-m-01 00:00:00',strtotime($transaction_tmp2->created_at)));
                    //format get month
                    $date_compare = round(($d1 -$d2)/(60*60*24*29));

                    if($date_compare >= 1)
                        $transaction->created_at = date('Y-m-01 00:00:00');
                    else
                        $transaction->created_at = date('Y-m-01 00:00:00', strtotime("+1 months", strtotime($transaction_tmp2->created_at)));

                }else $transaction->created_at = date('Y-m-01 00:00:00');
            }
        }

        $transaction->updated_at = empty($data['created'])? date("Y-m-d h:i:s"): $data['created'];
        $transaction->link_company_id = $data['link_company_id'] ?? null;

        //check exist ticket by number
        if($type == 'pos' || $type == 'charge' || $type == 'qrcode' || $type == 'deposit_month' ){
            if($this->checkExistTicketNumberAndCompanyId(
                $data['ticket_number'],
                $ticket_price_id,
                $company_id,
                $data['sign'],
                $type
            )){
                return response('Ticket number is exist', 404);
            }
        }

        //check exist ticket by options is card
        if( $type == 'charge_taxi' || $type == 'charge_free' || $type == 'deposit' || $type == 'charge_goods' ){
            $where_arr = [
                ['company_id', $company_id],
                ['rfid', $rfid],
                ['amount', $data['amount']],
                ['type', $type],
                ['activated', $activated]
            ];
            if($this->checkExistTransactionByOptions( $where_arr ))return response('Ticket is exist', 404);
        }

        //check exist ticket by charge month, tkt_price, tkt_number
        if( $type == 'charge_month'){
            $where_arr_1 = [
                ['company_id', $company_id],
                ['rfid', $rfid],
                ['ticket_number', $transaction->ticket_number],
                ['ticket_price_id', $transaction->ticket_price_id],
                ['type', $type],
                ['shift_id', (int)$data['shift_id']]
            ];
            if($this->checkExistTransactionByOptions( $where_arr_1 )){
                return response('Ticket charge month is exist', 404);
            }
        }

        //check exist ticket by options is pos
        if( $type == 'pos_taxi' || $type == 'qrcode_taxi' ||$type == 'qrcode_goods'){
            $where_arr = [
                ['company_id', $company_id],
                ['amount', $data['amount']],
                ['activated', $activated],
                ['type', $type],
            ];
            if($this->checkExistTransactionByOptions( $where_arr )){
                return response('Ticket is exist', 404);
            }
        }

        //check exist ticket by options is pos_goods
        if($type == 'pos_goods'){
            $where_arr = [
                ['company_id', $company_id],
                ['amount', $data['amount']],
                ['ticket_number', $data['ticket_number']],
                ['type', $type],
            ];
            if($this->checkExistTransactionByOptions($where_arr)) return response('Ticket is exist', 404);
        }

        if ($transaction->save()) {

            //handle cty sacco
            if (!empty($data['duration']) &&  intval($data['duration']) > 0 && $type == 'app:1') {
                $voucher = array(
                    'ticket_type_id' => $data['ticket_type_id'],
                    'ticket_number' => $data['ticket_number'],
                    'amount' => intval($data['amount']),
                    'startdate' => strtotime($activated),
                    'duration' => intval($data['duration']),
                    'company_id' => $company_id
                );

                $push_log = [];
                $push_log['action'] = 'create';
                $push_log['company_id'] = $company_id;
                $push_log['subject_id'] = $transaction['id'];
                $push_log['subject_type'] = 'voucher';
                $push_log['subject_data'] = $voucher;
                $this->push_logs->createPushLog($push_log);
            }

            //update current number in ticket allocate
            if(
                $transaction->type == 'pos' ||
                $transaction->type == 'charge' ||
                $transaction->type == 'qrcode' ||
                $transaction->type == 'deposit_month'
            ){

                $this->ticket_allocates->updateCurrentNumberByDeviceIdAndTicketPriceId(
                    $transaction->company_id,
                    $transaction->device_id,
                    $transaction->ticket_price_id,
                    $transaction->ticket_number,
                    $transaction->activated
                );
            }

            //update balance for membership
            if(
                $transaction->type == 'charge' ||
                $transaction->type == 'deposit' ||
                $transaction->type == 'charge_taxi' ||
                $transaction->type == 'charge_goods'
            ){
                $this->memberships->updateBalance($data, $transaction->type);
            }

            //update expiration_date for membership by type = deposit month
            if($transaction->type == "deposit_month"){

                $membership = $this->memberships->getMembership($data['membership_id']);
                if(empty($membership)){ return response('Memberships not found', 404);}

               
                if($membership->membershipType['code'] == 1){
                    
                    if($data['start_expiration_date'] != null && $data['end_expiration_date'] != null){

                        $transaction->created_at = $data['end_expiration_date'].'-01 00:00:00';
                        $membership->actived =  1;
                        $membership->start_expiration_date =  $data['start_expiration_date'];
                        $membership->expiration_date =  $data['end_expiration_date'];

                    }else{

                        if($membership->actived == 1){
                            if($membership->expiration_date <  date("Y-m") ){
                                $membership->expiration_date =  date("Y-m");
                            }else{
                                $membership->expiration_date = date('Y-m', strtotime("+1 months", strtotime($membership->expiration_date)));
                            }
                        }
                        if($membership->actived == -2){ $membership->expiration_date =  date("Y-m"); }

                        $membership->actived =  1;
                    }  

                    $membership->save();  

                } return response('Membership not is card month', 404);
            }

            //update charge_count for membership
            if(
                $transaction->type == 'charge' ||
                $transaction->type == 'charge_taxi' ||
                $transaction->type == 'charge_goods' ||
                $transaction->type == 'charge_month' ||
                $transaction->type == 'charge_free'

            ) {$this->memberships->updateChargeCountByValue($data,  $transaction->type, 1);}

            //update charge_limit for membership
            if($transaction->type == 'charge_month') $this->memberships->updateChargeLimitByValue($data,  $transaction->type, 1);

            return $this->getTransactionById($transaction['id']);
        }

        return false;
    }

    // public function createTransactionForExtendTicket($data){

    //     $membership = $this->memberships->getMembership($data['membership_id']);
    //     if(empty($membership)){ return response('Memberships not found', 404);}

    //     if($membership->membershipType['code'] == 1){

    //         $ticket_number = date("Ymd");
    //         if(!empty($membership->denomination_id)){
    //             $ticket_number .= $membership->denomination_id;
    //         }

    //         $count_transaction = Transaction::where('type', $data['type'])->where('ticket_number', 'like', $ticket_number.'%')->count();

    //         if($count_transaction == 0){
    //             $ticket_number .= '-01';
    //         }
    //         if($count_transaction > 0 && $count_transaction < 9){
    //             $ticket_number .= '-0'.((int)$count_transaction + 1);
    //         }
    //         if($count_transaction >= 9) {
    //             $ticket_number .= '-'.((int)$count_transaction + 1);
    //         }

    //         $transaction = new Transaction();
    //         $transaction->company_id = $data['company_id'];
    //         $transaction->device_id = $data['device_id'];
    //         $transaction->shift_id = $data['shift_id'];
    //         $transaction->type = $data['type'];
    //         $transaction->user_id = $data['user_id'];
    //         $transaction->amount = $data['amount'];
    //         $transaction->station_id = $data['station_id'] ?? null;
    //         $transaction->ticket_number = $ticket_number ?? null;
    //         $transaction->rfid = $data['rfid'] ?? null;
    //         $transaction->ticket_destroy = 0;
    //         $transaction->activated = empty($data['activated'])
    //                                         ? date("Y-m-d H:i:s")
    //                                         : $data['activated'];
    //         $transaction->created_at = empty($data['created'])
    //                                         ? date("Y-m-d H:i:s")
    //                                         : $data['created'];
    //         $transaction->updated_at = empty($data['created'])
    //                                         ? date("Y-m-d H:i:s")
    //                                         : $data['created'];

    //         $check_transaction = Transaction::where([
    //                                         'company_id' => $data['company_id'],
    //                                         'ticket_number' => $ticket_number,
    //                                         'rfid' => $data['rfid'] ,
    //                                         'amount' => $data['amount'],
    //                                         'activated' => $data['activated']
    //                                     ])->exists();
    //         if($check_transaction){  return response('Ticket is exist', 404); }

    //         if ($transaction->save()) {

    //             if($membership->actived == 1){
    //                 if($membership->expiration_date <  date("Y-m") ){
    //                     $membership->expiration_date =  date("Y-m");
    //                 }else{
    //                     $membership->expiration_date = date('Y-m', strtotime("+1 months", strtotime($membership->expiration_date)));
    //                 }
    //             }

    //             if($membership->actived == -2){
    //                 $membership->expiration_date =  date("Y-m");
    //             }

    //             $membership->actived =  1;

    //             if($membership->save()){
    //                 return $this->getTransactionById($transaction['id']);
    //             }
    //             return false;
    //         }
    //         return false;

    //     } return response('Membership not is card month', 404);
    // }

    //create transaction for sacco(Phu Quoc)
    public function createTransactionForApp($data) {

        Log::info('Trustvn ticket:'.json_encode($data));
        $transaction = new Transaction();
        $transaction->company_id = $data['company_id'];
        $transaction->ticket_number = $data['ticket_code'];
        $transaction->amount = intval($data['price']);
        $transaction->created_at = $data['timestamp'];
        $transaction->updated_at = $data['timestamp'];
        $transaction->type = $data['type'];
        $transaction->ticket_destroy = 0;
        $transaction->duration = $data['duration'] * 3600;

        if ($transaction->save()) {

            $start_date = null;

            if (!empty($data['startdate'])) {
                $start_date = intval(strtotime($data['timestamp']));
            }

            $transaction['startdate'] = $start_date;
            $transaction['moreinfo'] = $data['moreinfo'] ?? null;
            $transaction['expiration'] = intval($start_date + 7776000);

            $push_log = [];
            $push_log['action'] = 'create';
            $push_log['company_id'] = $data['company_id'];
            $push_log['subject_id'] = $transaction['id'];
            $push_log['subject_type'] = 'voucher';
            $push_log['subject_data'] = $transaction;
            $this->push_logs->createPushLog($push_log);

            return $this->getTransactionById($transaction['id']);
        }

        return false;
    }

    public function getTransactionById($id) {
        return Transaction::find($id);
    }

    public function checkExistTransactionByOptions($options = []) {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }
           return Transaction::where($options)->exists();
        }

        return response('Transaction not found', 404);
    }

    public function getTransactionByOptions($options = []) {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return Transaction::where($options)->get();
        }

        return response('Transaction not found', 404);
    }

    public function createPushLogForApp(){

        $transactions = Transaction::where('type', 'app:1')->whereNull('activated')->get();
        foreach ($transactions as $transaction) {
            // code...
            $transaction = $transaction->toArray();
            $ticket_number = $transaction['ticket_number'];
            $pl = $this->push_logs->getPushLogByOptions([
                    ['subject_type', '=', 'voucher'],
                    ['subject_data', 'like', '%'.$ticket_number.'%']
                ]);
            if(count($pl) == 0){
                $subject_id = $transaction['id'];
                unset($transaction['activated']);
                unset($transaction['device_id']);
                unset($transaction['shift_id']);
                unset($transaction['ticket_price_id']);
                unset($transaction['user_id']);

                $start_date = intval(strtotime($transaction['created_at']));
                $transaction['startdate'] = $start_date;
                $transaction['moreinfo'] = null;
                //$transaction['expiration'] = null;
                $push_log = [];
                $push_log['action'] = 'create';
                $push_log['company_id'] = $transaction['company_id'];
                $push_log['subject_id'] = $subject_id;
                $push_log['subject_type'] = 'voucher';
                $push_log['subject_data'] = $transaction;
                $this->push_logs->createPushLog($push_log);
            }

        }
    }

    public function getTransactionByRfid($rfid, $type, $company_id, $limit){

        $type_params = [];
        if($type == 0){ $type_params = ['charge', 'charge_free', 'charge_month', 'charge_taxi']; }
        if($type == 1){ $type_params = ['deposit', 'topup_momo', 'deposit_month']; }

        return Transaction::where('transactions.rfid', '=', $rfid)
                        ->whereIn('transactions.type', $type_params)
                        ->where('transactions.ticket_destroy', '!=', 1)
                        ->where('transactions.company_id', '=', $company_id)
                        ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                        ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                        ->select('transactions.id','transactions.type', 'transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                        ->paginate($limit);
    }

    public function searchMembershipsDetailTransactionByRfid($data) {

        $type_params = [];
        if($data['search_type'] == 0){ $type_params = ['charge', 'charge_free', 'charge_month', 'charge_taxi']; }
        if($data['search_type'] == 1){ $type_params = ['deposit', 'topup_momo', 'deposit_month']; }

        if ($data['search_opt'] == 0 || !$data['search_key']) {
            $from_date = date("Y-m-d 00:00:00", strtotime($data['search_date']));
            $to_date = date("Y-m-d 23:59:59", strtotime($data['search_date']));

            return Transaction::where('transactions.rfid', '=', $data['rfid'])
                    ->whereIn('transactions.type', $type_params)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->where('transactions.company_id', '=', $data['company_id'])
                    ->where('transactions.activated', '>=', $from_date)
                    ->where('transactions.activated', '<=', $to_date)
                    ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                    ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                    ->select('transactions.id','transactions.type', 'transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                    ->get();
        }
        if ($data['search_opt'] == 1) {
            if ($data['search_date']) {
                $from_date = date("Y-m-d 00:00:00", strtotime($data['search_date']));
                $to_date = date("Y-m-d 23:59:59", strtotime($data['search_date']));
                return Transaction::where('transactions.rfid', '=', $data['rfid'])
                    ->whereIn('transactions.type', $type_params)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->where('transactions.company_id', '=', $data['company_id'])
                    ->where('transactions.activated', '>=', $from_date)
                    ->where('transactions.activated', '<=', $to_date)
                    ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                    ->where('bus_stations.name', 'like', '%'.$data['search_key'].'%')
                    ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                    ->select('transactions.id','transactions.type', 'transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                    ->get();
            } else {
                return Transaction::where('transactions.rfid', '=', $data['rfid'])
                    ->whereIn('transactions.type', $type_params)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->where('transactions.company_id', '=', $data['company_id'])
                    ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                    ->where('bus_stations.name', 'like', '%'.$data['search_key'].'%')
                    ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                    ->select('transactions.id','transactions.type', 'transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                    ->get();
            }

        }
        if ($data['search_opt'] == 2) {
            if ($data['search_date']) {
                $from_date = date("Y-m-d 00:00:00", strtotime($data['search_date']));
                $to_date = date("Y-m-d 23:59:59", strtotime($data['search_date']));
                return Transaction::where('transactions.rfid', '=', $data['rfid'])
                    ->whereIn('transactions.type', $type_params)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->where('transactions.company_id', '=', $data['company_id'])
                    ->where('transactions.activated', '>=', $from_date)
                    ->where('transactions.activated', '<=', $to_date)
                    ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                    ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                    ->where('users.fullname', 'like', '%'.$data['search_key'].'%')
                    ->select('transactions.id','transactions.type', 'transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                    ->get();
            } else {
                return Transaction::where('transactions.rfid', '=', $data['rfid'])
                    ->whereIn('transactions.type', $type_params)
                    ->where('transactions.ticket_destroy', '!=', 1)
                    ->where('transactions.company_id', '=', $data['company_id'])
                    ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                    ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                    ->where('users.fullname', 'like', '%'.$data['search_key'].'%')
                    ->select('transactions.id','transactions.type','transactions.activated', 'transactions.amount', 'bus_stations.name', 'users.fullname')
                    ->get();
            }
        }
    }

    public function getNumTickets($data) {

        $start_date = !empty($data['from_date']) ?  date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $end_date = !empty($data['to_date']) ? $end_date = date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;
        $route_id = $data['route_id'] != 0 ? $data['route_id'] : null;

        $transactions = Transaction::where('transactions.company_id', $data['company_id'])
                ->join('shifts', 'shifts.id', '=', 'transactions.shift_id')
                ->when($route_id, function($transactions) use ($route_id){
                    return $transactions->where('shifts.route_id', $route_id);
                })
                ->when($start_date, function($transactions) use ($start_date){
                    return $transactions->where('shifts.ended', '>=', $start_date);
                })
                ->when($end_date, function($transactions) use ($end_date){
                    return $transactions->where('shifts.ended', '<=', $end_date);
                })
                ->join('routes', 'shifts.route_id', '=', 'routes.id')
                ->whereIn('transactions.type', ['pos', 'charge', 'deposit_month', 'qrcode'])
                ->where('transactions.ticket_destroy', '!=', 1)
                ->where('transactions.ticket_price_id', $data['ticket_price_id'])
                ->select(
                    'shifts.route_id',
                    'routes.number',
                    'transactions.ticket_number',
                    'transactions.ticket_price_id',
                    'transactions.amount',
                    'transactions.activated',
                    'transactions.type',
                    'transactions.station_id'
                )
                ->orderBy('transactions.ticket_number')
                ->get()
                ->toArray();

        if(count($transactions) > 0){
            return $transactions;
        }

        return [];
    }

    public function updateTicketDestroyByTransactionId($transaction_id, $value){

        $transaction = $this->getTransactionById($transaction_id);

        if (empty($transaction)) {
            return response('Transaction not found', 404);
        }

        $transaction->ticket_destroy = $value;

        if ($transaction->save()) {
            return $transaction;
        }
        return response('Update not found', 404);
    }

    public function updateTransactionFroRfidByRfid($rfid_old, $rfid_new){

        return DB::update('update transactions set rfid = "'.$rfid_new.'" where rfid = ?', [$rfid_old]);
    }

    public function getTransactionByBarcodeOfRfid($rfid, $limit){

        if($rfid) {

            return Transaction::where('transactions.rfid', '=', $rfid)
                        ->whereIn('transactions.type', ['charge','charge_taxi','charge_free','deposit','topup_momo', 'deposit_month', 'charge_month'])
                        ->where('transactions.ticket_destroy', '!=', 1)
                        ->select('transactions.id as ID', 'transactions.activated as date_transaction', 'transactions.amount as amount_transaction', 'transactions.type as type_transaction')
                        ->orderBy('transactions.activated', 'DESC')
                        ->limit($limit)
                        ->get();

            // return response('Transaction not found', 404);
        }

        // return response('Rfid not found', 404);
    }

    public function checkExistTransactionByTopupMomo($timestamp, $rfid, $company_id ){

        return Transaction::where([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'activated' => $timestamp,
            'rfid' => $rfid,
            'company_id' => $company_id,
            ])->exists();
    }

    public function createTransactionForMomo($data){

        if($data){

            $membership =  $data['membership'];
            $amount_momo =  $data['amount_momo'];
            $transaction_code_momo =  $data['transaction_code_momo'];
            $timestamp = date("Y-m-d H:i:s", $data['timestamp']);
            $rfid = $membership['rfidcard']['rfid'];
            $company_id = $membership['company_id'];

            $transaction = new Transaction();
            $transaction->company_id = $company_id;
            $transaction->transaction_code = $transaction_code_momo;
            $transaction->amount = intval($amount_momo);
            $transaction->created_at = $timestamp;
            $transaction->updated_at = $timestamp;
            $transaction->activated = $timestamp;
            $transaction->rfid = $rfid;
            $transaction->type = 'topup_momo';
            $transaction->ticket_destroy = 0;

            if($data['type_momo'] == 'topup_momo'){

                $checkTransaction = $this->checkExistTransactionByTopupMomo($timestamp, $rfid, $company_id);

                if($checkTransaction){
                    return false;
                }else{

                    if($transaction->save()) {  return $transaction;}
                    return  false;
                }
            }
        }
    }

    public function getCountTicketGoodsByDevice($data){

        $from_date = date('Y-m-d 00:00:00', $data['timestamp']);
        $to_date = date('Y-m-d 23:59:59', $data['timestamp']);

        return Transaction::where([
                ['company_id',$data['company_id']],
                ['activated','>=', $from_date],
                ['activated','<=', $to_date],
                ['device_id','=', $data['device_id']]
        ])->whereIn('type',['pos_goods','charge_goods','qrcode_goods'])
        ->count();
    }
}
