<?php
namespace App\Services;

use App\Models\PushLogs;
use App\Models\User;
use App\Services\ShiftsService;
use App\Services\TransactionsService;
use App\Services\PrepaidCardsService;
use App\Services\UsersService;
use App\Services\VehiclesService;
use App\Services\TicketTypesService;
use App\Services\TicketPricesService;
use App\Services\MembershipsService;
use App\Services\CompaniesService;
use App\Services\PublicFunctionService;
use Log;

class UpdatesService
{
    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions;

    /**
     * @var App\Services\PrepaidCardsService
     */
    protected $prepaid_cards;

    /**
     * @var App\Services\UsersService
     */
    protected $users;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    /**
     * @var App\Services\TicketTypesService
     */
    protected $ticket_types;

    /**
     * @var App\Services\TicketTypesService
     */
    protected $ticket_prices;

    /**
     * @var App\Services\MembershipsService
     */
    protected $membership;
     /**
     * @var App\Services\CompaniesService
     */
    protected $companies;
       /**
     * @var App\Services\PublicFunctionService
     */
    protected $public_functions;

    public function __construct(
        ShiftsService $shifts,
        TransactionsService $transactions,
        PrepaidCardsService $prepaid_cards,
        UsersService $users,
        VehiclesService $vehicles,
        TicketTypesService $ticket_types,
        TicketPricesService $ticket_prices,
        MembershipsService $membership,
        CompaniesService $companies,
        PublicFunctionService $public_functions
        )
    {
        $this->shifts = $shifts;
        $this->transactions = $transactions;
        $this->prepaid_cards = $prepaid_cards;
        $this->users = $users;
        $this->vehicles = $vehicles;
        $this->ticket_types = $ticket_types;
        $this->ticket_prices = $ticket_prices;
        $this->membership = $membership;
        $this->companies = $companies;
        $this->public_functions = $public_functions;
    }

    public function createActivities($device, $data)
    {
        $imei = $device->identity;
        $device_id = $device->id;
        $version = $device->version;
        $ts = 0;
        //update auto property for membership
        $this->membership->updateAutoMembershipForProperties();

        foreach ($data as $value) {

            $timestamp = date("Y-m-d H:i:s", $value['timestamp']);
            $action = $value['action'];
            $subject_type = $value['subject_type'];
            $user_id = $value['user_id'];
            $user = $this->users->getUserByKey('id', $user_id);
            $company_id = $user->company_id;
            $subject_data = null;
            $check_subject_data =  json_decode($value['subject_data']);

            if(empty($check_subject_data)){
                $company = $this->companies->getCompanyById($company_id);
                if($company){
                    // Log::info('$$$subject_data_BEFORE '.$value['subject_data']);
                    $subject_data = json_decode($this->public_functions->deCrypto($value['subject_data'], $company->tax_code));
                    // Log::info('$$$subject_data_AFTER '.json_encode($subject_data));
                }
            }else{
                $subject_data = json_decode($value['subject_data']);
            }

            switch ($action) {

                case 'login':
                    $pin_code = null;
                    $vehicle_id = $subject_data->vehicle_id ?? null;
                    $rfid_user = $user->rfidcard->rfid;
                    $station_id = $subject_data->station_id ?? 0;

                    // create shift
                    $this->shifts->login(
                        $imei,
                        $rfid_user,
                        $pin_code,
                        $timestamp,
                        $station_id
                    );

                    if (!empty($vehicle_id) && intval($vehicle_id) > 0) {

                        $vehicle = $this->vehicles->getVehicleById($vehicle_id);
                        $rfid_vehicle = $vehicle->rfidcard->rfid;

                        $this->shifts->updateRfidVehicle(
                            $imei,
                            $rfid_user,
                            $rfid_vehicle,
                            $timestamp,
                            $station_id
                        );
                    }
                    break;

                case 'insert_ticket':

                    // get data
                    $data = [];
                    $data['company_id'] = $company_id;
                    $data['ticket_number'] = $subject_data->ticket_number;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['type'] = $subject_data->type;
                    $data['user_id'] = $user_id;
                    $data['amount'] = $subject_data->amount;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['station_down'] = $subject_data->station_down ?? null;
                    $data['sign'] = $subject_data->sign ?? null;
                    $data['ticket_type_id'] = $subject_data->ticket_type_id;
                    $data['ticket_price_id'] = $subject_data->ticket_price_id ?? null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'insert_ticket_taxi':

                    // get data
                    $data = [];
                    $data['company_id'] = $company_id;
                    $data['ticket_number'] = $subject_data->ticket_number ?? null;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['type'] = $subject_data->type;
                    $data['user_id'] = $user_id;
                    $data['amount'] = $subject_data->amount;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'insert_ticket_goods':

                    // get data
                    $data = [];
                    $data['company_id'] = $company_id;
                    $data['ticket_number'] = $subject_data->ticket_number ?? null;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['type'] = $subject_data->type;
                    $data['user_id'] = $user_id;
                    $data['amount'] = $subject_data->amount;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'redeem_ticket':

                    // get data
                    $data = [];
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['ticket_number'] = $subject_data->ticket_number;
                    $data['device_id'] = $device_id;
                    $data['updated'] = $timestamp;

                    $this->transactions->redeemTicket($data);
                    break;

                case 'card_deposit':

                    if(round($value['timestamp']) != round($ts)) {
                        // get data
                        $ts = $value['timestamp'];
                        $data = [];
                        $data['rfid'] = $subject_data->rfid;
                        $data['amount'] = $subject_data->amount;
                        $data['station_id'] = $subject_data->station_id ?? 0;
                        $data['balance'] = $subject_data->balance ?? null;
                        $data['company_id'] = $company_id;
                        $data['link_company_id'] = $subject_data->link_company_id ?? null;
                        $data['user_id'] = $user_id;
                        $data['type'] = $subject_data->type;
                        $data['activated'] = $timestamp;
                        $data['duration'] = null;
                        $data['device_id'] = $device_id;
                        $data['created'] = $timestamp;

                        $this->transactions->insertTicket($data);
                    }
                    break;

                case 'card_deposit_month':

                    // get data
                    $data = [];
                    $data['rfid'] = $subject_data->rfid;
                    $data['amount'] = $subject_data->amount;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['membership_id'] = $subject_data->membership_id ?? 0;
                    $data['station_data'] = $subject_data->station_data ?? null;
                    $data['ticket_number'] = $subject_data->ticket_number;
                    $data['ticket_type_id'] = $subject_data->ticket_type_id;
                    $data['ticket_price_id'] = $subject_data->ticket_price_id ?? null;
                    $data['start_expiration_date'] = $subject_data->start_expiration_date ?? null;
                    $data['end_expiration_date'] = $subject_data->end_expiration_date ?? null;
                    $data['sign'] = $subject_data->sign ?? null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['type'] = $subject_data->type;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'card_charge':
                    if(round($value['timestamp']) != round($ts)) {
                        // get data
                        $ts = $value['timestamp'];

                        $data = [];
                        $data['rfid'] = $subject_data->rfid;
                        $data['amount'] = $subject_data->amount;
                        $data['ticket_number'] = $subject_data->ticket_number;
                        $data['ticket_type_id'] = $subject_data->ticket_type_id;
                        $data['ticket_price_id'] = $subject_data->ticket_price_id ?? null;
                        $data['station_id'] = $subject_data->station_id ?? 0;
                        $data['sign'] = $subject_data->sign ?? null;
                        $data['balance'] = $subject_data->balance ?? null;
                        $data['company_id'] = $company_id;
                        $data['link_company_id'] = $subject_data->link_company_id ?? null;
                        $data['user_id'] = $user_id;
                        $data['type'] = $subject_data->type;
                        $data['activated'] =  $timestamp;
                        $data['duration'] = null;
                        $data['device_id'] = $device_id;
                        $data['created'] = $timestamp;

                        $this->transactions->insertTicket($data);
                    }
                    break;

                case 'card_free':

                    // get data
                    $data = [];
                    $data['rfid'] = $subject_data->rfid;
                    $data['amount'] = $subject_data->amount ?? 0;
                    $data['ticket_number'] = null;
                    $data['ticket_type_id'] =  null;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = null;
                    $data['company_id'] = $company_id;
                    $data['link_company_id'] = $subject_data->link_company_id ?? null;
                    $data['balance'] = $subject_data->balance ?? null;
                    $data['user_id'] = $user_id;
                    $data['type'] = $subject_data->type;
                    $data['activated'] =  $timestamp;
                    $data['duration'] = null;
                    $data['ticket_price_id'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);

                    break;

                case 'card_charge_taxi':

                    // get data
                    $data = [];
                    $data['rfid'] = $subject_data->rfid;
                    $data['amount'] = $subject_data->amount;
                    $data['ticket_number'] = null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = $subject_data->sign ?? null;
                    $data['balance'] = $subject_data->balance ?? null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['type'] = $subject_data->type;
                    $data['activated'] =  $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'card_charge_goods':

                    // get data
                    $data = [];
                    $data['rfid'] = $subject_data->rfid;
                    $data['amount'] = $subject_data->amount;
                    $data['ticket_number'] = $subject_data->ticket_number ?? null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = $subject_data->sign ?? null;
                    $data['balance'] = $subject_data->balance ?? null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['type'] = $subject_data->type;
                    $data['activated'] =  $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'card_charge_month':

                    // get data
                    $data = [];
                    $data['rfid'] = $subject_data->rfid;
                    $data['amount'] = $subject_data->amount ?? null;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['station_data'] = $subject_data->station_data ?? null;
                    $data['ticket_price_id'] = $subject_data->ticket_price_id;
                    $data['sign'] = null;
                    $data['balance'] = null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['type'] = $subject_data->type;
                    $data['activated'] =  $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'momo_qrcode':

                    $data = [];
                    $data['amount'] = $subject_data->amount;
                    $data['ticket_number'] = $subject_data->ticket_number;
                    $data['ticket_type_id'] = $subject_data->ticket_type_id;
                    $data['ticket_price_id'] = $subject_data->ticket_price_id ?? null;
                    $data['transaction_code'] = $subject_data->transaction_code ?? null;
                    $data['type'] = $subject_data->type;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['sign'] = $subject_data->sign ?? null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'momo_qrcode_taxi':
                    $data = [];
                    $data['amount'] = $subject_data->amount;
                    $data['ticket_number'] = null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['type'] = $subject_data->type;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['transaction_code'] = $subject_data->transaction_code ?? null;
                    $data['sign'] = null;
                    $data['balance'] = null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'momo_qrcode_goods':
                    $data = [];
                    $data['amount'] = $subject_data->amount;
                    $data['ticket_number'] = $subject_data->ticket_number ?? null;
                    $data['ticket_type_id'] = null;
                    $data['ticket_price_id'] = null;
                    $data['type'] = $subject_data->type;
                    $data['station_id'] = $subject_data->station_id ?? 0;
                    $data['transaction_code'] = $subject_data->transaction_code ?? null;
                    $data['sign'] = null;
                    $data['balance'] = null;
                    $data['company_id'] = $company_id;
                    $data['user_id'] = $user_id;
                    $data['activated'] = $timestamp;
                    $data['duration'] = null;
                    $data['device_id'] = $device_id;
                    $data['created'] = $timestamp;

                    $this->transactions->insertTicket($data);
                    break;

                case 'card_refund':
                    break;

                case 'login_shift_supervisor':
                    // get sub user
                    $subuser_id = $subject_data->subuser_id;
                    $station_up_id = $subject_data->station_up_id ?? null;
                    $supervisor_id = $user_id;
                    $supervisor_rfid = $user->rfidcard->rfid;

                    // get shift
                    $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $subuser_id);

                    if (!empty($shift)) {

                        if (!empty($supervisor_id) && intval($supervisor_id) > 0) {

                            $user = $this->users->getUserByKey('id', $supervisor_id);

                            if($user) {

                                if($this->shifts->updateSupervisorIdByShiftId($shift->id,$supervisor_id)){

                                    // login shift supervisor
                                    $data = [
                                      "shift_id" => $shift->id,
                                      "supervisor_id" => $supervisor_id,
                                      "supervisor_rfid" => $supervisor_rfid,
                                      "started" => $timestamp,
                                      "station_up_id" => $station_up_id
                                    ];
                                    $this->shifts->loginShiftSupervisor($data);
                                }
                            }
                        }
                    }

                    break;

                case 'logout_shift_supervisor':

                    // get sub user
                    $subuser_id = $subject_data->subuser_id;
                    $station_down_id = $subject_data->station_down_id ?? null;
                    $supervisor_id = $user_id;
                    $supervisor_rfid = $user->rfidcard->rfid;

                    // get shift
                    $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $subuser_id);

                    if (!empty($shift)) {

                        if (!empty($supervisor_id) && intval($supervisor_id) > 0) {

                            $user = $this->users->getUserByKey('id', $supervisor_id);

                            if($user) {

                                  // login shift supervisor
                                  $data = [
                                    "shift_id" => $shift->id,
                                    "supervisor_id" => $supervisor_id,
                                    "supervisor_rfid" => $supervisor_rfid,
                                    "ended" => $timestamp,
                                    "station_down_id" => $station_down_id
                                  ];
                                  Log::info('logout_shift_supervisor '.json_encode($data));

                                  // logout
                                  $this->shifts->logoutShiftSupervisor($data);
                            }
                        }
                    }

                    break;

                case 'logout':

                    // get sub user
                    $subuser_id = $subject_data->subuser_id ?? null;
                    $total_amount = $subject_data->total_amount ?? null;
                    $subuser_role_name = '';
                    $driver_id = null;
                    $subdriver_id = null;
                    $station_id = 0;

                    // get shift
                    $shift = $this->shifts->getShiftsByDeviceIdAndUserId($device_id, $user_id);

                    if (!empty($shift)) {

                        if (!empty($subuser_id) && intval($subuser_id) > 0) {
                            $subuser = $this->users->getUserByKey('id', $subuser_id);
                            $subuser_role_name = $subuser->role->name;

                            if ($subuser->role->name == 'subdriver') {
                                $subdriver_id = $subuser->id;
                            } else {
                                $driver_id = $subuser->id;
                            }

                            if ($user->role->name == 'driver') {
                                $driver_id = $user_id;
                            } else {
                                $subdriver_id = $user_id;
                            }

                            // update driver and subdriver
                            $this->shifts->updateDriverAndSubDriverByShiftId(
                                $shift->id,
                                $driver_id,
                                $subdriver_id
                            );
                        }

                        // logout
                        $shift_token = $shift->shift_token;
                        $this->shifts->logout($shift_token, $timestamp, $total_amount );
                    }

                    // get shifts by device
                    $shifts = $this->shifts->getShiftsByOptions([
                                    ['device_id', $device_id],
                                    ['ended', '=', null],
                                    ['started', '!=', null]
                                ]);

                    if (count($shifts) > 0) {
                        // logout all shifts is active by device
                        foreach ($shifts as $value) {
                            $this->shifts->logout($value->shift_token, $timestamp, $total_amount);
                        }
                    }

                    break;
            }
        }

        return ['status' => true, 'message' => 'Ok'];
    }
}
