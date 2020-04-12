<?php

namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\BusStationsService;

class ApplicatonUpdateApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\BusStationsService
     */
    protected $bus_stations; 

    /**
     * Constructor
     */
    public function __construct(
        Request $request, 
        BusStationsService $bus_stations
    )
    {
        $this->request = $request;
        $this->bus_stations = $bus_stations;
    }

    /**
     * Operation updateBusStationByCompanyId
     *
     * Update bus station by company ID.
     *
     *
     * @return Http response
     */
    public function updateBusStationByCompanyId()
    {
        $token = $this->request->header('Token');
        $input = $this->request->all();

        if(empty($token)) {  return ['status'=> 404, 'message'=> 'Mã token không tìm thấy', 'data' => []]; }
        if(empty($input)) {  return ['status'=> 404, 'message'=> 'Không tìm thấy tham số', 'data' => []]; }
        
        try {
            return $this->bus_stations->updateBusStationByCompanyIdForApp($token, $input);
        } catch (Exception $e) {
            return response('ok', 200);
        }
    }
}
