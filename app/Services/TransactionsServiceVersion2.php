<?php
namespace App\Services;

use App\Models\Transaction;
use DB;

class TransactionsServiceVersion2
{

    public function __construct( ) { }

    public function getTransactionByRfid($rfid, $type, $company_id, $limit){

        $type_params = [];
        if((int)$type == 0){ $type_params = ['charge', 'charge_free', 'charge_month', 'charge_taxi']; }
        if((int)$type == 1){ $type_params = ["topup_momo", 'deposit', 'deposit_month']; }

        return Transaction::where('transactions.rfid', $rfid)
                        ->whereIn('transactions.type', $type_params)
                        ->where('transactions.ticket_destroy', '!=', 1)
                        ->where('transactions.company_id', '=', $company_id)
                        ->leftJoin('bus_stations', 'bus_stations.id', '=', 'transactions.station_id')
                        ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
                        ->select('transactions.*', 'bus_stations.name', 'users.fullname')
                        ->paginate($limit);
    }

    public function searchMembershipsDetailTransactionByRfid($data) {

        $type_params = [];
        if((int)$data['search_type'] == 0){ $type_params = ['charge', 'charge_free', 'charge_month', 'charge_taxi']; }
        if((int)$data['search_type'] == 1){ $type_params = ['deposit', 'topup_momo', 'deposit_month']; }

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

    public function updateTransactionFroRfidByRfid($rfid_old, $rfid_new){

        return DB::update('update transactions set rfid = "'.$rfid_new.'" where rfid = ?', [$rfid_old]);
    }

    public function getTransactionByBarcodeOfRfid($rfid, $limit){

        if($rfid) {

            $type = ['charge','charge_taxi','charge_free','deposit','topup_momo', 'deposit_mponth', 'charge_month'];
            return Transaction::where('transactions.rfid', '=', $rfid)
                        ->whereIn('transactions.type', $type)
                        ->where('transactions.ticket_destroy', '!=', 1)
                        ->select('transactions.id as ID', 'transactions.activated as date_transaction', 'transactions.amount as amount_transaction', 'transactions.type as type_transaction')
                        ->orderBy('transactions.activated', 'DESC')
                        ->limit($limit)
                        ->get();

            return response('Transaction not found', 404);
        }

        return response('Rfid not found', 404);
    }

    public function countTransactionByrfidAndCompanyIdInDay($rfid, $company_id)
    {
        $from_date = date("Y-m-d 00:00:00");
        $to_date = date("Y-m-d 23:59:59");
        return Transaction::where('rfid', '=', $rfid)
            ->where('rfid', '!=' , NULL)
            ->where('company_id', '=', $company_id)
            ->where('ticket_destroy', '!=', 1)
            ->whereIn('type', ['charge'])
            ->where('activated', '>=', $from_date)
            ->where('activated', '<=', $to_date)
            ->count();
    }
}
