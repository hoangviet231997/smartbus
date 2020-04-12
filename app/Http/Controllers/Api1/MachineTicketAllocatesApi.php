<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketAllocatesService;
use App\Services\DevicesService;

class MachineTicketAllocatesApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\TicketAllocatesService
     */
    protected $ticket_allocates;

    /**
     * @var App\Services\DevicesService
     */
    protected $devices;

    public function __construct(Request $request, TicketAllocatesService $ticket_allocates, DevicesService $devices)
    {
        $this->request = $request;       
        $this->ticket_allocates = $ticket_allocates;
        $this->devices = $devices;        
    }

    /**
     * Operation machineTicketAllocates
     *
     * get ticket allocates.
     *
     *
     * @return Http response
     */
    public function machineTicketAllocates()
    {
        $imei = $this->request->header('X-IMEI');
        $input = $this->request->all();
       
        //init == null/0 init data or install app new 
        //init  == 1 init ticket allocates
        $init = $this->request->header('INIT');

        if (empty($imei)) {
            return response('Invalid imei supplied', 404);
        }

        // get device
        $device = $this->devices->getDeviceByIdentity($imei);

        if (empty($device)) {
            return response('Device not found', 404);
        }

        if (count($input) <= 0) {
            return response('The given data was invalid.', 404);      
        } 

        return $this->ticket_allocates->ticketAllocate($device, $input, $init);
    }
}
