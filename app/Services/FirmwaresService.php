<?php
namespace App\Services;

use App\Models\Firmware;
use App\Models\Device;
use App\Services\DevicesService;
use Log;
use DB;

class FirmwaresService
{
    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    public function __construct(DevicesService $devices)
    {
        $this->devices = $devices;
    }

    public function updateVersionDevice($id, $version) {

        $device = $this->devices->getDeviceById($id);
        if (empty($device))
            return response('Not found', 404);

        if($device->version != $version) {
            Device::where('id', $id)->update(['version' => $version]);
        }
    }

    public function getFirmwareByVersion($device, $version, $company_id)
    {
        // Log::info('Firmware:'.json_encode($device).' Version:'.$version);
        $this->updateVersionDevice($device->id, $version);
        // get device
        $device_model_id = $device->device_model_id;

        // get last version
        $firmware = $this->getLastVersionByDevModelId($device->device_model_id, $company_id);

        if (empty($firmware)) {
            return response('Firmware not found', 404);
        }

        // check version
        if ($firmware->version <= $version) {
            return response('No new version', 404);
        }

        return $firmware;
    }

    public function getLastVersionByDevModelId($dev_model_id, $company_id)
    {

        $clause = ['device_model_id' => $dev_model_id];

        if($company_id != null) $clause["company_id"] = $company_id;

        return Firmware::where($clause)
                        ->orderBy('version', 'desc')
                        ->first();
    }

    public function createFirmware($data)
    {
        $firmware = new Firmware;
        $firmware->device_model_id = $data['device_model_id'];
        $firmware->server_ip = $data['server_ip'];
        $firmware->username = $data['username'];
        $firmware->password = $data['password'];
        $firmware->path = $data['path'];
        $firmware->version = $data['version'];
        $firmware->filename = $data['filename'];
        $firmware->company_id = $data['company_id'] ?? null;
        $firmware->update_type = $data['update_type'] ?? null;
        $firmware->note = $data['note'] ?? null;

        if ($firmware->save())
            return $firmware;

        return response('Create Error', 404);
    }

    public function getFirmwareVersions($data) {

        $limit = $data['limit'];
        if (empty($limit) && $limit < 0) $limit = 10;

        $pagination = Firmware::with('deviceModel', 'company')->paginate($limit)->toArray();

         header("pagination-total: " . $pagination['total']);
         header("pagination-current: " . $pagination['current_page']);
         header("pagination-last: " . $pagination['last_page']);
 
         return $pagination['data'];
    }

    public function updateFirmware($data)
    {
        $firmware = $this->getFirmwareById($data['id']);

        if (empty($firmware)) return response('Not found', 404);

        $firmware->device_model_id = $data['device_model_id'];
        $firmware->server_ip = $data['server_ip'];
        $firmware->username = $data['username'];
        $firmware->password = $data['password'];
        $firmware->path = $data['path'];
        $firmware->version = $data['version'];
        $firmware->filename = $data['filename'];

        if ($firmware->save()) return $firmware;

        return response('Update Error', 404);
    }

    public function deleteFirmware($firmware_id)
    {
        $firmware = $this->getFirmwareById($firmware_id);
        if($firmware){
            if($firmware->delete()){
                return response('Delete OK', 200);
            }
            return response('Delete Error', 404);
        }
        return response('Firmware not found', 404);
    }

    public function getFirmwareById($id)
    {
        return Firmware::find($id);
    }

    public function getFirmwareByDevModelId($device_model_id)
    {
        return Firmware::where('device_model_id', $device_model_id)
                    ->orderBy('version')
                    ->get();
    }

    public function deleteFirmwareByIdAndDevModelId($id, $model_id)
    {
        // get firmware
        $firmware = Firmware::where('id', $id)
                        ->where('device_model_id', $model_id)
                        ->first();

        if (empty($firmware))
            return response('Firmware Not found', 404);

        if ($firmware->delete()) return response('OK', 200);

        return response('Delete error', 404);
    }

    public function searchFirmwareByInputAndByTypeSearch($data)
    {
        $style_search = $data['style_search'] ?? '';
        $key_input = $data['key_input'] ?? '';

        $firmware = Firmware::with('deviceModel', 'company');

        switch ($style_search) {
            case 'name':
                $firmware->where('firmwares.filename', 'like', "%$key_input%");
                break;

            case 'model':
                $firmware->join('device_models', 'firmwares.device_model_id', '=', 'device_models.id')
                    ->where('device_models.id', '=', (int) $key_input);
                break;

            case 'company':
                $firmware->join('companies', 'firmwares.company_id', '=', 'companies.id')
                    ->where('companies.id', '=', (int) $key_input);
                break;
        }

        return $firmware->select('firmwares.*')->get()->toArray();
    }
}
