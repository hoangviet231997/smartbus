<?php
namespace App\Http\Controllers\Api1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\AppsService;

class AppTicketsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\AppsService
     */
    protected $apps_service;

    /**
     * Constructor
     */
    public function __construct(Request $request, AppsService $apps_service)
    {
        $this->request = $request;      
        $this->apps_service = $apps_service;
    }

    /**
     * Operation getTicketInfo
     *
     * Allow thirdparty applications to get their tickets information.
     *
     *
     * @return Http response
     */
    public function getTicketInfo()
    {
        $app_key = $this->request->header('X-AppKey');
        $ticket_code = $this->request->query('ticket_code');

        if (empty($app_key)) {
            return response('Invalid app key supplied', 404);
        }

        if (empty($ticket_code)) {
            return response('Invalid ticket code supplied', 404);
        }

        return $this->apps_service->getTicketInfo($ticket_code, $app_key);
    }

    /**
     * Operation insertTicket
     *
     * Allow thirdparty application to.
     *
     *
     * @return Http response
     */
    public function insertTicket()
    {
        //path params validation
        $this->validate($this->request, [
            'ticket_code' => 'required',
        ]);

        $app_key = $this->request->header('X-AppKey');

        if (empty($app_key)) {
            return response('Invalid app key supplied', 404);
        }

        $input = $this->request->all();

        return $this->apps_service->insertTicket($input, $app_key);
    }
}
