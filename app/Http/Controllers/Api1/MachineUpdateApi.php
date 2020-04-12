<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\GpsService;
use App\Services\FirmwaresService;
use App\Services\PushLogsService;
use App\Services\UpdatesService;
use App\Services\DevicesService;
use App\Services\ShiftsService;
use Log;
class MachineUpdateApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\GpsService
     */
    protected $gps;

    /**
     * @var App\Services\FirmwaresService
     */
    protected $firmwares;

    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\UpdatesService
     */
    protected $updates;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

      /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * Constructor
     */
    public function __construct(
        Request $request,
        GpsService $gps,
        FirmwaresService $firmwares,
        PushLogsService $push_logs,
        UpdatesService $updates,
        DevicesService $devices,
        ShiftsService $shifts

    ){
        $this->request = $request;
        $this->gps = $gps;
        $this->firmwares = $firmwares;
        $this->push_logs = $push_logs;
        $this->updates = $updates;
        $this->devices = $devices;
        $this->shifts = $shifts;
    }

    /**
     * Operation machineUpdateActivities
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineUpdateActivities()
    {
        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if (count($input) <= 0) {
            return response('The given data was invalid.', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }
        try {
            Log::info('data logs:'.json_encode($input).'::::DEVICE ID:'.$device->id);
            return $this->updates->createActivities($device, $input);
        } catch (Exception $e) {
            return response('oki', 200);
        }

    }

    /**
     * Operation machineUpdateDatabase
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineUpdateDatabase()
    {
        $query = [];
        $imei = $this->request->header('X-IMEI');
        $from = $this->request->query('from') ?? null;
        $subject_type = $this->request->query('subject_type') ?? null;
        $action = $this->request->query('action') ?? null;

        $query['from'] = $from;
        $query['subject_type'] = $subject_type;
        $query['action'] = $action;

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }

        return $this->push_logs->getAllPushLogs($device, $query);
    }

    /**
     * Operation machineUpdateFirmware
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineUpdateFirmware()
    {
        $imei = $this->request->header('X-IMEI');
        $version = $this->request->query('version');
        $company_id = $this->request->query('company_id') ?? null;

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if ((int)$version <= 0) {
            return response('The version must be bigger 0.', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) return response('Device not found', 404);

        return $this->firmwares->getFirmwareByVersion($device, $version, $company_id);
    }

    /**
     * Operation machineUpdatePostion
     *
     * create.
     *
     *
     * @return Http response
     */
    public function machineUpdatePostion()
    {
        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if (count($input) <= 0) {
            return response('The given data was invalid.', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }

        return $this->gps->createGps($device, $input);
    }

    /**
     * Operation machineUpdatePing
     *
     * get.
     *
     *
     * @return Http response
     */
    public function machineUpdatePing()
    {
        // $imei = $this->request->header('X-IMEI');
        //
        // if (empty($imei)) {
        //     return response('Invalid imei supplied', 404);
        // }

        return ['ip' => $_SERVER['REMOTE_ADDR']];
    }

     /**
     * Operation machineUpdateDeviceStatus
     *
     * update.
     *
     *
     * @return Http response
     */
    public function machineUpdateDeviceStatus()
    {
        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        if (count($input) <= 0) {
            return response('The given data was invalid.', 404);
        }

        return $this->devices->updateStatusDevice($imei, $input);
    }

    /**
     * Operation machineUpdateGetTotalBillsByDevice
     *
     * get.
     *
     *
     * @return Http response
     */
    public function machineUpdateGetTotalBillsByDevice()
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

        return $this->shifts->getTotalBillsByDeviceId($input);
    }
}
