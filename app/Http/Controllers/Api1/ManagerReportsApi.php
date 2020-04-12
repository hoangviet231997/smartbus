<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\ReportsService;
use App\Services\TicketDestroysService;
use App\Services\ShiftDestroysService;

class ManagerReportsApi extends ApiController
{
    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\ReportsService
     */
    protected $reports;

    /**
     * @var App\Services\ShiftDestroysService
     */
    protected $shift_destroys;

    /**
     * @var App\Services\TicketDestroysService
     */
    protected $ticket_destroys;

    /**
     * Constructor
     */
    public function __construct(Request $request, ReportsService $reports, TicketDestroysService $ticket_destroys, ShiftDestroysService $shift_destroys)
    {
        $this->request = $request;
        $this->reports = $reports;
        $this->ticket_destroys = $ticket_destroys;
        $this->shift_destroys = $shift_destroys;
    }

    /**
     * Operation managerReportsViewReceipt
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewReceipt()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'user_id' => 'required|integer|min:1',
            'date' => 'required',
            'date_to' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewReceipt($input);
    }
    /**
     * Operation managerReportsViewAllReceipt
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewAllReceipt()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewAllReceipt($input);
    }
     /**
     * Operation managerReportsViewNotCollectMoneyReceipt
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewNotCollectMoneyReceipt()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       //path params validation
    //    $this->validate($this->request, [
    //        'date' => 'required'
    //    ]);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->viewNotCollectMoneyReceipt($input);
    }

    /**
     * Operation manmagerReportsReceipt
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportReceipt()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'date' => 'required'
        ]);

        $input = $this->request->all();

        if (count($input['shifts']) <= 0) {
            return response('The given data was invalid.', 404);
        }

        $input['company_id'] = $user->company_id;

        return $this->reports->exportReceipt($input);
    }

    /**
     * Operation managerReportsGetReceiptDetailByShiftId
     *
     * Find by ID.
     *
     * @param int $shift_id  (required)
     *
     * @return Http response
     */
    public function managerReportsGetReceiptDetailByShiftId($shift_id)
    {

        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check shift_id
        if (empty($shift_id) || (int)$shift_id < 0){
            return response('Invalid Shift id supplied', 404);
        }

        $data = [];
        $data['company_id'] = $user->company_id;
        $data['shift_id'] = $shift_id;

        return $this->reports->getReceiptDetailByShiftId($data);
    }

     /**
     * Operation managerReportsExportReceiptTransactionByShiftId
     *
     * report.
     *
     * @param int $shift_id  (required)
     *
     * @return Http response
     */
    public function managerReportsExportReceiptTransactionByShiftId($shift_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check shift_id
        if (empty($shift_id) || (int)$shift_id < 0)
            return response('Invalid Shift id supplied', 404);

        $data = [];
        $data['company_id'] = $user->company_id;
        $data['shift_id'] = $shift_id;

        return $this->reports->exportTransaction($data);
    }

    /**
     * Operation managerReportsExportStaff
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportStaff()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'route_id' => 'required|integer',
            'from_date' => 'required',
            'to_date' => 'required',
            'position' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportStaff($input);
    }
     /**
     * Operation managerReportsViewStaff
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewStaff()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

         //path params validation
         $this->validate($this->request, [
            'route_id' => 'required|integer',
            'from_date' => 'required',
            'to_date' => 'required',
            'position' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewStaff($input);
    }
    /**
     * Operation updateShifts
     *
     * update.
     *
     *
     * @return Http response
     */
    public function updateShifts()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'shift_id' => 'required',
            'user_id' => 'required',
            'subdriver_id' => 'required'
        ]);

        $input = $this->request->all();
        // $input['company_id'] = $user->company_id;

        //path params validation


        //not path params validation
        return $this->reports->updateShiftByUserId($input);
    }
    /**
     * Operation managerReportsExportInvoices
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportInvoices()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportInvoice($input);
    }
    /**
     * Operation managerReportsExportDaily
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportDaily()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'route_id' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportDaily($input);
    }
    /**
     * Operation managerReportsViewDaily
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewDaily()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'route_id' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewDaily($input);

    }

    /**
     * Operation managerReportsExportVehicles
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportVehicles()
    {
        //return response($this->request->all(),200);

        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'route_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'vehicle_id' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportVehicleByRoute($input);
    }

     /**
     * Operation managerReportsViewVehicles
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewVehicles()
    {

        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'route_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'vehicle_id' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewVehicleByRoute($input);
    }
/**
     * Operation managerReportsExportVehicleAll
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportVehicleAll()
    {
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       // //path params validation
       $this->validate($this->request, [
           'from_date' => 'required',
           'to_date' => 'required'
       ]);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->exportVehicleAll($input);
    }
    /**
     * Operation managerReportsViewVehicleAll
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewVehicleAll()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewVehicleAll($input);
    }
    /**
     * Operation managerReportsExportVehicleByPeriod
     *
     * report.
     *
     *
     * @return Http response
     */
    public function managerReportsExportVehicleByPeriod()
    {
        $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       // //path params validation
       $this->validate($this->request, [
           'from_date' => 'required',
           'to_date' => 'required'
       ]);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->exportVehicleByPeriod($input);
    }
    /**
     * Operation managerReportsViewVehicleByPeriod
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewVehicleByPeriod()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewVehicleByPeriod($input);
    }
    public function managerReportsExportTickets()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTickets($input);
    }
    public function managerReportsExportTicketsByStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTicketsByStation($input);
    }
    /**
     * Operation managerReportsViewTickets
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewTickets()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->viewTicket($input);
    }
    /**
     * Operation managerReportsOnChangeViewTicket
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsOnChangeViewTicket()
    {
        // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

        // //path params validation
       $this->validate($this->request, [
            'type' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->OnChangeViewTicket($input);
    }
    /**
     * Operation managerReportsViewTicketsByStation
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewTicketsByStation()
    {
         // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->viewTicketByStation($input);

    }
    /**
     * Operation managerReportsPrintTickets
     *
     * print.
     *
     *
     * @return Http response
     */
    public function managerReportsPrintTickets()
    {
        // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       // //path params validation
       $this->validate($this->request, [
           'from_date' => 'required',
           'to_date' => 'required'
       ]);

       $input = $this->request->all();
       $input['company_id'] = $user->company_id;

       return $this->reports->printTicket($input);
    }

    /**
     * Operation managerReportsViewCard
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewCard($input);
    }

    /**
     * Operation managerReportsExportCard
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCard()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportCard($input);
    }

    /**
     * Operation managerReportsConvertNumberToString
     *
     * number convert string.
     *
     *
     * @return Http response
     */
    public function managerReportsConvertNumberToString()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->convertNumberToString($input);
    }

    /**
     * Operation managerTransactionDetailSearch
     *
     * number convert string.
     *
     *
     * @return Http response
     */
    public function managerTransactionDetailSearch()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewDetailTransactionSearch($input);
    }

    /**
     * Operation managerTransactionDetailReport
     *
     * number convert string.
     *
     *
     * @return Http response
     */
    public function managerTransactionDetailReport()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportDetailTransactionSearch($input);
    }

     /**
     * Operation managerReportsAddTicketDestroyInTransaction
     *
     * create object.
     *
     *
     * @return Http response
     */
    public function managerReportsAddTicketDestroyInTransaction()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['user_id'] = $user->id;

        return $this->ticket_destroys->addTicketDestroyInTransaction($input);
    }

     /**
     * Operation managerReportsViewTicketDestroy
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewTicketDestroy()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_destroys->viewTicketDestroy($input);
    }

     /**
     * Operation managerReportsAcceptTicketDestroy
     *
     * Delete a Ticket Destroy.
     *
     *
     * @return Http response
     */
    public function managerReportsAcceptTicketDestroy()
    {
       // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);
       // //path params validation
       $this->validate($this->request, [
           'id' => 'required',
           'type' => 'required'
       ]);

       $input = $this->request->all();

       return $this->ticket_destroys->acceptTicketDestroy($input);
    }

     /**
     * Operation managerReportsExportTransactionOnline
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsExportTransactionOnline()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'type' => 'required',
            'partner' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTransactionOnline($input);
    }
    /**
     * Operation managerReportsViewTransactionOnline
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewTransactionOnline()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'type' => 'required',
            'partner' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewTransactionOnline($input);
    }
    /**
     * Operation managerReportsAddShiftDestroy
     *
     * create object.
     *
     *
     * @return Http response
     */
    public function managerReportsAddShiftDestroy()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $this->validate($this->request, [
            'description' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['user_id'] = $user->id;

        return $this->shift_destroys->addShiftDestroy($input);

    }
    /**
     * Operation managerReportsViewShiftDestroys
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewShiftDestroys()
    {
         // check login
         $user = $this->requiredAuthUser();
         if (empty($user)) return response('token_invalid', 401);

         // path params validation
         $this->validate($this->request, [
             'from_date' => 'required',
             'to_date' => 'required'
         ]);

         $input = $this->request->all();
         $input['company_id'] = $user->company_id;

         return $this->shift_destroys->viewShiftDestroy($input);
    }
    /**
     * Operation managerReportsAcceptShiftDestroy
     *
     * Delete a Shift Destroy.
     *
     *
     * @return Http response
     */
    public function managerReportsAcceptShiftDestroy()
    {
        // check login
       $user = $this->requiredAuthUser();
       if (empty($user)) return response('token_invalid', 401);

       //path params validation
       $this->validate($this->request, [
           'id' => 'required',
           'type' => 'required'
       ]);

       $input = $this->request->all();

       return $this->shift_destroys->acceptShiftDestroy($input);
    }
    /**
     * Operation managerReportsViewCardMonthForGeneral
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCardMonthForGeneral()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewCardMonthForGeneral($input);
    }
    /**
     * Operation managerReportsExportCardMonthForGeneral
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCardMonthForGeneral()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportCardMonthForGeneral($input);
    }
     /**
     * Operation managerReportsViewCardMonthRevenue
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCardMonthRevenue()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewCardMonthForRevenue($input);
    }

    /**
     * Operation managerReportsExportCardMonthRevenue
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCardMonthRevenue()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;


        return $this->reports->exportCardMonthForRevenue($input);
    }

     /**
     * Operation managerReportsExportCardMonthByStaff
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCardMonthByStaff()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportCardMonthByStaff($input);
    }
    /**
     * Operation managerReportsViewCardMonthByStaff
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCardMonthByStaff()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewCardMonthByStaff($input);
    }

    /**
     * Operation managerReportsViewCardMonthByGroupBusStation
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCardMonthByGroupBusStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewCardMonthByGroupBusStation($input);
    }
    /**
     * Operation managerReportsExportCardMonthByGroupBusStation
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCardMonthByGroupBusStation()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportCardMonthByGroupBusStation($input);
    }


     /**
     * Operation managerReportsViewTrip
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewTrip()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewTripTimes($input);
    }
    /**
     * Operation managerReportsExportTrip
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportTrip()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTripTimes($input);
    }
     /**
     * Operation managerReportsExportTrip
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportTripTimeDetail()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTripsTimeDetail($input);
    }

    public function managerReportsExportTimeKeeping()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportTimeKeeping($input);
    }

    public function managerReportsViewOutputByVehicle()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewOutputByVehicle($input);
    }

    public function managerReportsExportOutputByVehicle()
    {
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportOutputByVehicle($input);
    }

   /**
     * Operation managerReportsViewCardExemption
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewCardExemption()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'route_id' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        return $this->reports->viewCardExemption($input);
    }

    /**
     * Operation managerReportsExportCardExemption
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsExportCardExemption()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
            'route_id' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportCardExemption($input);
    }

    /**
     * Operation managerReportsExportShiftSupervisor
     *
     * export.
     *
     *
     * @return Http response
     */
    public function managerReportsExportShiftSupervisor()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportShiftSupervisor($input);
    }
    /**
     * Operation managerReportsViewShiftSupervisor
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewShiftSupervisor()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'from_date' => 'required',
            'to_date' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewShiftSupervisor($input);
    }

    /**
     * Operation managerReportsViewVehicleRoutePriod
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerReportsViewVehicleRoutePriod()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        $this->validate($this->request, [
            'now_from_date' => 'required', 
            'now_to_date' => 'required',
            'last_from_date' =>'required',
            'last_to_date' => 'required',
            'object_report' => 'required'
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->viewVehicleRoutePeriod($input);
    }

    public function managerReportsExportVehicleRoutePriod()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->reports->exportVehicleRoutePeriod($input);
    }
}
