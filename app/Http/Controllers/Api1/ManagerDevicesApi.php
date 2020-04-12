<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\DevicesService;
use App\Services\IssuedsService;
use App\Services\ShiftsService;

class ManagerDevicesApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    /**
     * @var App\Services\ShiftsService
     */
    protected $shifts;

    /**
     * Constructor
     */
    public function __construct(Request $request, DevicesService $devices, IssuedsService $issueds, ShiftsService $shifts)
    {
        $this->request = $request;
        $this->devices = $devices;
        $this->issueds = $issueds;
        $this->shifts = $shifts;
    }

    /**
     * Operation managerListDevices
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerListDevices()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->devices->listDevices($input);
    }
    /**
     * Operation managerGetDeviceById
     *
     * Find by ID.
     *
     * @param int $device_id  (required)
     *
     * @return Http response
     */
    public function managerGetDeviceById($device_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        // check device id
        if (empty($device_id) || (int)$device_id < 0)
            return response('Invalid Device ID supplied', 404);

        if (!$this->issueds->checkExistByDeviceAndCompany($device_id, $company_id)) {
            return response('Device Not found', 404);
        }

        $device = $this->devices->getDeviceById($device_id);

        if (empty($device))
            return response('Device Not found', 404);

        return $device;
    }

    /**
     * Operation managerDeviceGetRevenueById
     *
     * Find by ID.
     *
     * @param int $device_id  (required)
     *
     * @return Http response
     */
    public function managerDeviceGetRevenueByShiftId()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['shift_id'] = $input['shiftId'];
        $input['type_opt'] = $input['typeOpt'];
        $input['tran_id'] = $input['tranId'] ?? null;

        return $this->devices->getRevenueChartByShift($input);
    }
      /**
     * Operation managerListDevicesByIsRunning
     *
     * list Find by Isrunning.
     *
     * @param int $is_running  (required)
     *
     * @return Http response
     */
    public function managerListDevicesByIsRunning($is_running)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        $device = $this->devices->getDeviceByIsRunning($is_running, $company_id);

        if (empty($device))
            return response('Device Not found', 404);

        return $device;
    }
}
