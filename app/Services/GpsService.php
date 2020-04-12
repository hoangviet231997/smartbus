<?php
namespace App\Services;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Models\Gps;
use App\Services\DevicesService;
use App\Services\AttachmentsService;
use App\Services\VehiclesService;

class GpsService
{
    /**
     * @var App\Services\AttachmentsService
     */
    protected $attachments;

    /**
     * @var App\Services\VehiclesService
     */
    protected $vehicles;

    public function __construct(AttachmentsService $attachments, VehiclesService $vehicles)
    {
        $this->attachments = $attachments;
        $this->vehicles = $vehicles;
    }

    public function createGps($device, $data)
    {
        // get device
        $device_id = $device->id;

        foreach ($data as $value) {

            $gps_device = $this->getGpsByDeviceId($device_id);

            if(!empty($gps_device)){

                $gps_device->device_id = $device->id;
                $gps_device->position = new Point($value['lat'], $value['lng']);// (lat, lng)
                $gps_device->date = date("Y-m-d h:i:s", $value['timestamp']);
                $gps_device->save();

            }else{

                $gps = new Gps();
                $gps->device_id = $device->id;
                $gps->position = new Point($value['lat'], $value['lng']);// (lat, lng)
                $gps->date = date("Y-m-d h:i:s", $value['timestamp']);
                $gps->save();
            }
        }

        // update last location of device
        $last_gps = $this->getLastLocationByDeviceId($device->id);

        if ($last_gps->position) {
            $lat = $last_gps->position->getLat(); 
            $lng = $last_gps->position->getLng();

            $device->position = new Point($lat, $lng);// (lat, lng)
            $device->save();

            // update location for vehicle
            $attachments = $this->attachments->getAttachmentsByOptions([
                                ['device_id', $device_id]
                            ])->toArray();
            $attachment = end($attachments);
                
            if ($attachment) {
                $vehicle_id = $attachment['vehicle_id'];

                // get vehicle by id
                $vehicle = $this->vehicles->getVehicleById($vehicle_id);

                if ($vehicle) {
                    $vehicle->location = new Point($lat, $lng);// (lat, lng)
                    $vehicle->save();
                }
            }

        }

        return ['status' => true, 'message' => 'Ok'];
    }

    public function getGpsByDeviceId($device_id){

        return Gps::where('device_id', $device_id)->orderBy('id', 'desc')->first();
    }

    public function getLastLocationByDeviceId($device_id)
    {
        return Gps::where('device_id', $device_id)->orderBy('id', 'desc')->first();
    }
}