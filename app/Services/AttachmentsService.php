<?php
namespace App\Services;
use App\Models\Attachment;

class AttachmentsService
{

    public function __construct()
    {

    }

    public function checkExistsByDeviceAndVehicle($device_id, $vehicle_id)
    {
        return Attachment::where('device_id', $device_id)
                    ->where('vehicle_id', $vehicle_id)
                    ->exists();
    }

    public function createAttachment($device_id, $vehicle_id)
    {
        $attachment = new Attachment();
        $attachment->vehicle_id = $vehicle_id;
        $attachment->device_id = $device_id;
        $attachment->save();

        if ($attachment->save()) {
            return $attachment;
        }

        return false;
    }

    public function deleteAttachment($device_id, $vehicle_id)
    {
        $attachment = Attachment::where('device_id', $device_id)
                    ->where('vehicle_id', $vehicle_id)
                    ->first();

        if (empty($attachment)) 
            return false;

        if ($attachment->delete()) {
            return true;
        }

        return false;                    
    }

    public function getAttachmentsByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return Attachment::where($options)->get();
        }

        return response('Error', 404);
    }    
}