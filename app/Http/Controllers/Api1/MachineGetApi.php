<?php

/**
 * SMARTBUS API
 * No description provided (generated by Swagger Codegen https://github.com/swagger-api/swagger-codegen)
 *
 * OpenAPI spec version: 1.0.0
 * 
 *
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen.git
 * Do not edit the class manually.
 */


namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\TransactionsService;
use App\Services\DevicesService;

class MachineGetApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\TransactionsService
     */
    protected $transactions; 

    /**
     * @var App\Services\DevicesService
     */
    protected $devices; 

    /**
     * Constructor
     */
    public function __construct(Request $request, TransactionsService $transactions, DevicesService $devices)
    {
        $this->request = $request;
        $this->transactions = $transactions;
        $this->devices = $devices;
    }

    /**
     * Operation machineGetCountTicketGoodsByDevice
     *
     * get.
     *
     *
     * @return Http response
     */
    public function machineGetCountTicketGoodsByDevice()
    {
        $imei = $this->request->header('X-IMEI');
        $timestamp = $this->request->header('timestamp');

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if (empty($timestamp)) {
            return response('Invalid timestamp supplied', 404);
        }

        $device = $this->devices->getDeviceByIdentity($imei);
        if (empty($device)) {
            return response('Device not found', 404);
        }

        $company_id = null;
        foreach($device['issueds'] as $vl){
            $company_id = $vl->company_id;         
        }

        $input = [];
        $input['timestamp'] = $timestamp;
        $input['device_id'] = $device->id;
        $input['company_id'] = $company_id;

        return $this->transactions->getCountTicketGoodsByDevice($input);
    }
}
