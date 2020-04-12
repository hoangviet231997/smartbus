<?php
namespace App\Http\Controllers\Api1;;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api1\ApiController;
use Illuminate\Http\Request;
use App\Services\TicketTypesService;
use App\Services\TicketAllocatesService;
use App\Models\TicketType;
use App\Models\TicketPrice;

class ManagerTicketTypesApi extends ApiController
{

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var App\Services\TicketTypesService
     */
    protected $ticket_types;

    /**
     * @var App\Services\TicketAllocatesService
     */
    protected $ticket_allocates;

    /**
     * Constructor
     */
    public function __construct(Request $request, TicketTypesService $ticket_types, TicketAllocatesService $ticket_allocates)
    {
        $this->request = $request;
        $this->ticket_types = $ticket_types;
        $this->ticket_allocates = $ticket_allocates;
    }

    /**
     * Operation managerUpdateTicketType
     *
     * update.
     *
     *
     * @return Http response
     */
    public function managerUpdateTicketType()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $company_id = $user->company_id;

        //path params validation
        $this->validate($this->request, [
            'id' => 'bail|required|integer|min:1',            
            'name' => 'required|max:50',
            'price' => 'required|numeric|min:0'
            // 'description' => 'nullable',
            // 'order_code' => 'nullable|max:100',
            // 'sign' => 'nullable|max:100'
        ]);

        // save
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_types->updateTicketType($input);        
    }
    /**
     * Operation managerlistTicketTypes
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistTicketTypes()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_types->listTicketTypes($input);
    }
    /**
     * Operation manmagerCreateTicketType
     *
     * create.
     *
     *
     * @return Http response
     */
    public function managerCreateTicketType()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        //path params validation
        $this->validate($this->request, [
            'name' => 'required|max:50',
            'price' => 'required|numeric|min:0'
            // 'description' => 'nullable',
            // 'order_code' => 'nullable|max:100',
            // 'sign' => 'nullable|max:100',
        ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_types->createTicketType($input);
    }
    /**
     * Operation managerDeleteticketType
     *
     * Delete.
     *
     * @param int $ticket_type_id  (required)
     *
     * @return Http response
     */
    public function managerDeleteticketType($ticket_type_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        return $this->ticket_types->deleteTicketType($ticket_type_id, $user->company_id);
    }
    /**
     * Operation managerGetTicketTypeById
     *
     * Find by ID.
     *
     * @param int $company_id  (required)
     * @param int $ticket_type_id  (required)
     *
     * @return Http response
     */
    public function managerGetTicketTypeById($ticket_type_id)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // check ticket type id
        if (empty($ticket_type_id) || (int)$ticket_type_id < 0) 
            return response('Invalid Ticket Type ID supplied', 404);

        $ticket_type = $this->ticket_types->getTicketTypeByIdAndCompanyId(
                            $ticket_type_id, 
                            $user->company_id
                        );

        if (empty($ticket_type)) 
            return response('Ticket Type Not found', 404);

        return $ticket_type;      
    }    

     /**
     * Operation managerlistTicketAllocateSearchs
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistTicketAllocateSearchs()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // //path params validation
        // $this->validate($this->request, [
        //     'identity' => 'required',
        //     'ticket_type_id' => 'required|numeric|min:0',
        //     'from_date' => 'required',
        //     'to_date' => 'required'
        // ]);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_allocates->listTicketAllocatesBySearchBy($input);
    }

    /**
     * Operation managerlistTicketAllocates
     *
     * list.
     *
     *
     * @return Http response
     */
    public function managerlistTicketAllocates()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);
        
        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_allocates->listTicketAllocatesByCompanyId($input);
    }

     /**
     * Operation managerListTicketTypesByTypeParam
     *
     * list.
     *
     * @param int $type_param  (required)
     *
     * @return Http response
     */
    public function managerListTicketTypesByTypeParam($type_param)
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        // $input = $this->request->all();
        $input['company_id'] = $user->company_id;
        $input['type_param'] = $type_param ?? null;

        return $this->ticket_types->listTicketTypeByTypes($input);
    }

    public function managerSearchTicketTypesByKeyWord()
    {
        // check login
        $user = $this->requiredAuthUser();
        if (empty($user)) return response('token_invalid', 401);

        $input = $this->request->all();
        $input['company_id'] = $user->company_id;

        return $this->ticket_types->searchTicketTypesByKeyWord($input);
    }
}
