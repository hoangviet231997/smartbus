<?php
namespace App\Services;

use App\Models\DeviceModel;
use App\Services\PushLogsService;

class DevicesModelService
{
    public function __construct()
    {

    }

    public function checkExistsById($id)
    {
        return DeviceModel::where('id', $id)->exists();
    }

    public function createDeviceModel($data)
    {
        $device_model = new DeviceModel;
        $device_model->name = $data['name'];
        $device_model->model = $data['model'];
        $device_model->features = json_encode($data['features']);

        if ($device_model->save())
            return $this->getDeviceModelById($device_model['id']);

        return response('Create error', 404);
    }

    public function updateDeviceModel($data)
    {
        $device_model = $this->getDeviceModelById($data['id']);

        if (empty($device_model)) return response('Not found', 404);

        // update Device
        $device_model->name = $data['name'];
        $device_model->model = $data['model'];
        $device_model->features = json_encode($data['features']);

        if ($device_model->save()) return $device_model;
        
        return response('Update error', 404);
    }

    public function getDeviceModelById($id)
    {
        $device_model = DeviceModel::find($id);

        if ($device_model) {
            $device_model->features =  json_decode($device_model->features);
        }

        return $device_model;
    }

    public function listDevicesModel()
    {
        $device_models = DeviceModel::all()->toArray();
        $dev_models = [];
        foreach ($device_models as $device_model) {
            $device_model['features'] = json_decode($device_model['features']);
            array_push($dev_models, $device_model);
        }

        return $dev_models;
    }

    public function deleteDevicesModel($id)
    {
        // get Device
        $device_model = $this->getDeviceModelById($id);

        if (empty($device_model)) return response('Not found', 404);

        if ($device_model->delete()) return response('OK', 200);
        
        return response('Delete Error', 404);
    }
}