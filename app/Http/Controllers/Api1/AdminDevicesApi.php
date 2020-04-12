<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\DevicesService;
use App\Services\DevicesModelService;
use App\Services\FirmwaresService;
use App\Services\CompaniesService;
use App\Services\IssuedsService;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Firmware;
use App\Models\Company;
use App\Models\Issued;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminDevicesApi extends ApiController
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
     * @var App\Services\DevicesModelService
     */
    protected $devices_model;

    /**
     * @var App\Services\FirmwaresService
     */
    protected $firmwares;

    /**
     * @var App\Services\CompaniesService
     */
    protected $companies;

    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    /**
     * Constructor
     */
    public function __construct(Request $request, DevicesService $devices, DevicesModelService $devices_model, FirmwaresService $firmwares, CompaniesService $companies, IssuedsService $issueds)
    {
        $this->request = $request;
        $this->devices = $devices;
        $this->devices_model = $devices_model;
        $this->firmwares = $firmwares;
        $this->companies = $companies;
        $this->issueds = $issueds;
    }

    /**
     * Operation createDevice
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createDevice()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'device_model_id' => 'bail|required|integer|min:1',
            'identity' => 'required',
        ]);

        // save device
        $input = $this->request->all();

        return $this->devices->createDevice($input);
    }

    /**
     * Operation listDevices
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listDevices()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->devices->listDevices($input);
    }

    /**
     * Operation updateDevice
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateDevice()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'device_model_id' => 'bail|required|integer|min:1',
            'identity' => 'required',
        ]);

        // save device
        $input = $this->request->all();

        return $this->devices->updateDevice($input);
    }

    /**
     * Operation deleteDevice
     *
     * delete.
     *
     * @param int $device_id  (required)
     *
     * @return Http response
     */
    public function deleteDevice($device_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($device_id) || (int)$device_id < 0)
            return response('Invalid ID supplied', 404);

        return $this->devices->deleteDevice($device_id);
    }

    /**
     * Operation getDeviceById
     *
     * Find by ID.
     *
     * @param int $device_id  (required)
     *
     * @return Http response
     */
    public function getDeviceById($device_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($device_id) || (int)$device_id < 0)
            return response('Invalid ID supplied', 404);

        // get Device
        $device = $this->devices->getDeviceById($device_id);

        if (empty($device)) return response('Not found', 404);

        return $device;
    }

    /**
     * Operation createModel
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createDevModel()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'bail|required|max:100',
            'model' => 'nullable|max:100',
            'features' => 'nullable'
        ]);

        // save device
        $input = $this->request->all();

        return $this->devices_model->createDeviceModel($input);
    }

    /**
     * Operation listModels
     *
     * list.
     *
     *
     * @return Http response
     */
    public function listDevModels()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->devices_model->listDevicesModel();
    }

    /**
     * Operation updateModel
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateDevModel()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',
            'name' => 'bail|required|max:100',
            'model' => 'nullable|max:100',
            'features' => 'nullable'
        ]);

        // get Device
        $input = $this->request->all();

        return $this->devices_model->updateDeviceModel($input);
    }

    /**
     * Operation deleteModel
     *
     * delete.
     *
     * @param int $model_id  (required)
     *
     * @return Http response
     */
    public function deleteDevModel($model_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        return $this->devices_model->deleteDevicesModel($model_id);
    }

    /**
     * Operation getModelById
     *
     * Find by ID.
     *
     * @param int $model_id  (required)
     *
     * @return Http response
     */
    public function getDevModelById($model_id)
    {
        // // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        // get Device
        $device_model = $this->devices_model->getDeviceModelById($model_id);

        if (empty($device_model)) return response('Not found', 404);

        return $device_model;
    }

    /**
     * Operation createFirmware
     *
     * create.
     *
     * @param int $model_id  (required)
     *
     * @return Http response
     */
    public function createFirmware($model_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id and device model exits
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        if (!$this->devices_model->checkExistsById($model_id))
            return response('Device Model Not found', 404);

        //path params validation
        $this->validate($this->request, [
            'server_ip' => 'required|max:50',
            'username' => 'required|max:100',
            'password' => 'required',
            'path' => 'required',
            'version' => 'required|integer|min:1',
            'filename' => 'required',
        ]);

        // save device
        $input = $this->request->all();
        $input['device_model_id'] = $model_id;

        return $this->firmwares->createFirmware($input);
    }

    /**
     * Operation listFirmwares
     *
     * list.
     *
     * @param int $model_id  (required)
     *
     * @return Http response
     */
    public function listFirmwares($model_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id and device model exits
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        if (!$this->devices_model->checkExistsById($model_id))
            return response('Device Model Not found', 404);

        return $this->firmwares->getFirmwareByDevModelId($model_id);
    }

    /**
     * Operation updateFirmware
     *
     * update.
     *
     * @param int $model_id  (required)
     *
     * @return Http response
     */
    public function updateFirmware($model_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id and device model exits
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        if (!$this->devices_model->checkExistsById($model_id))
            return response('Device Model Not found', 404);

        //path params validation
        $this->validate($this->request, [
            'server_ip' => 'required|max:50',
            'username' => 'required|max:100',
            'password' => 'required',
            'path' => 'required',
            'version' => 'required|integer|min:1',
            'filename' => 'required',
        ]);

        // get Device
        $input = $this->request->all();
        $input['device_model_id'] = $model_id;

        return $this->firmwares->updateFirmware($input);
    }

    /**
     * Operation deleteFirmware
     *
     * delete.
     *
     * @param int $model_id  (required)
     * @param int $firmware_id  (required)
     *
     * @return Http response
     */
    public function deleteFirmware($model_id, $firmware_id)
    {
        //check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id and device model exits
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        if (!$this->devices_model->checkExistsById($model_id))
            return response('Device Model Not found', 404);

        return $this->firmwares->deleteFirmwareByIdAndDevModelId($firmware_id, $model_id);
    }

    /**
     * Operation getFirmwareByIdAndModelId
     *
     * Find by ID.
     *
     * @param int $model_id  (required)
     * @param int $firmware_id  (required)
     *
     * @return Http response
     */
    public function getFirmwareByIdAndModelId($model_id, $firmware_id)
    {
        // // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id and device model exits
        if (empty($model_id) || (int)$model_id < 0)
            return response('Invalid ID supplied', 404);

        if (!$this->devices_model->checkExistsById($model_id))
            return response('Device Model Not found', 404);

        // get Firmware
        $firmware = $this->firmwares->getFirmwareById($firmware_id);

        if (empty($firmware)) return response('Firmware Not found', 404);

        return $firmware;
    }

    /**
     * Operation assignCompanyToDeviceId
     *
     * assign Company To Device.
     *
     * @param int $device_id  (required)
     *
     * @return Http response
     */
    public function assignCompanyToDevice($device_id, $company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if ((empty($device_id) || (int)$device_id < 0) ||
            (empty($company_id) || (int)$company_id < 0))
            return response('Invalid ID supplied', 404);

        // check device exist
        if (!$this->devices->checkExistsById($device_id))
            return response('Device Not found', 404);

        // check company exist
        if (!$this->companies->checkExistsById($company_id))
            return response('Company Not found', 404);

        // check assigned
        if ($this->issueds->getIssuedByKey('device_id', $device_id))
            return response('Device assigned', 404);

        return $this->issueds->createIssued($company_id, $device_id);
    }

    /**
     * Operation deleteAssignCompanyToDevice
     *
     * delete Assign Company To Device.
     *
     * @param int $device_id  (required)
     * @param int $company_id  (required)
     *
     * @return Http response
     */
    public function deleteAssignCompanyToDevice($device_id, $company_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check id
        if ((empty($device_id) || (int)$device_id < 0) ||
            (empty($company_id) || (int)$company_id < 0))
            return response('Invalid ID supplied', 404);

        return $this->issueds->deleteIssued($company_id, $device_id);
    }

     /**
     * Operation createFirmWareDeviceVersion
     *
     * create.
     *
     *
     * @return Http response
     */
    public function createFirmWareDeviceVersion()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //params validation
        $this->validate($this->request, [
            'device_model_id' => 'required|integer|min:1',
            'version' => 'required|integer|min:1',
            'filename' => 'required'
        ]);

        $input = $this->request->all();
        $device_model_id = (int) $input['device_model_id'];
        $company_id = $input['company_id'] ? (int)$input['company_id'] : null;
        $input['device_model_id'] = $device_model_id;
        $checkVer = $this->firmwares->getLastVersionByDevModelId($device_model_id, $company_id);
        //return $checkVer;
        if ($checkVer) {
            if ($checkVer->version >= $input['version']) {
                return response('Enter version greater than the current version '.$input['version'], 404);
            }
        }
        $file_name = 'app-release-v' . $input['version'] . '.apk';
        $input['filename'] = $file_name;

        return $this->firmwares->createFirmware($input);

    }

    /**
     * Operation deleteFirmWareDeviceVersion
     *
     * Delete a firmware.
     *
     * @param int $firmware_id  (required)
     *
     * @return Http response
     */
    public function deleteFirmWareDeviceVersion($firmware_id)
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       return $this->firmwares->deleteFirmware($firmware_id);
    }

    /**
     * Operation getFirmwaresById
     *
     * Find by ID.
     *
     * @param int $firmware_id  (required)
     *
     * @return Http response
     */
    public function getFirmwaresById($firmware_id)
    {
        // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       return $this->firmwares->getFirmwareById($firmware_id);
    }

    /**
     * Operation listFirmwareVersions
     *
     * create.
     *
     *
     * @return Http response
     */
    public function listFirmwareVersions()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        return $this->firmwares->getFirmwareVersions($input);
    }

    /**
     * Operation getDeviceByIdentitySearch
     *
     * Find by Identity.
     *
     * @param string $txt_identity  (required)
     *
     * @return Http response
     */
    public function getDeviceByIdentitySearch($txt_identity)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        if(empty($txt_identity)){
            return response('Invalid ID supplied', 404);
        }
        $company_id = $user->company_id ?? null;

        return $this->devices->getDeviceByIdentitySearch($txt_identity, $company_id);
    }

    public function searchFirmwareByInputAndByTypeSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();

        return $this->firmwares->searchFirmwareByInputAndByTypeSearch($input);
    }
}
