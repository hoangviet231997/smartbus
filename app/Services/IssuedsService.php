<?php
namespace App\Services;
use App\Models\Issued;

class IssuedsService
{
    public function __construct()
    {

    }

    public function checkExistByDeviceAndCompany($device_id, $company_id)
    {
        return Issued::where('device_id', $device_id)
                    ->where('company_id', $company_id)
                    ->exists();
    }

    public function getIssuedByKey($key, $value)
    {
        return Issued::where($key, $value)->first();
    }

    public function createIssued($company_id, $device_id)
    {
        $issued = new Issued();
        $issued->company_id = $company_id;
        $issued->device_id = $device_id;
        $issued->issued_date = date("Y-m-d h:i:s");
        $issued->return_date = date("Y-m-d h:i:s");

        if ($issued->save()) {
            return response('OK', 200);
        }

        return response('Create error', 200);
    }

    public function deleteIssued($company_id, $device_id)
    {
        // get firmware
        $issued = Issued::where('device_id', $device_id)
                    ->where('company_id', $company_id)
                    ->first();

        if (empty($issued))
            return response('Issued Not found', 404);

        if ($issued->delete()) return response('OK', 200);

        return response('Delete Error', 404);
    }

    public function getIssuedByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return Issued::where($options)->get();
        }

        return response('Not found', 404);
    }
}
