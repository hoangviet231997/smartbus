<?php
namespace App\Services;

use App\Services\IssuedsService;
use App\Services\TicketPricesService;
use App\Services\TicketTypesService;
use App\Models\TicketAllocate;
use App\Models\Transaction;
use DB;

class TicketAllocatesService
{
    /**
     * @var App\Services\IssuedsService
     */
    protected $issueds;

    /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_types;

     /**
     * @var App\Services\TicketTypesService
     */
    protected $ticket_prices;

    public function __construct(
        IssuedsService $issueds,
        TicketPricesService $ticket_prices,
        TicketTypesService $ticket_types
    )
    {
        $this->issueds = $issueds;
        $this->ticket_prices = $ticket_prices;
        $this->ticket_types = $ticket_types;
    }

    public function ticketAllocate($device, $ticket_types = array(), $init = null)
    {
        $device_id = $device->id;

        // get issued by key
        $issued = $this->issueds->getIssuedByKey('device_id', $device_id);

        if (empty($issued)) {
            return response('Company not found', 404);
        }

        $company_id = $issued->company_id;

        //sassco # all company
        if($company_id == 8){

            return $this->ticketAllocateByCompany8($device_id , $ticket_types, $company_id);
        }else{

            // check current ticket number by type
            $ticket_list = [];

            if((int)$init == 1){

                foreach ($ticket_types as $type) {

                    // check null and bigger 0
                    if (!empty($type) && (int) $type > 0) {

                        // get ticket prices by type
                        $type = (int) $type;
                        $ticket_price = $this->ticket_prices->getPriceByTicketTypeId($type);

                        if ($ticket_price) {

                            $price_id = $ticket_price->id;
                            $price = $ticket_price->price;

                            //--check quene syn ticket allocate
                            $ticket_allocate_quene = DB::table('ticket_allocate_quene')
                                                    ->where([
                                                      ['company_id','=',$company_id],
                                                      ['ticket_type_id','=',$type],
                                                      ['ticket_price_id','=',$price_id]
                                                    ])->first();
                            if($ticket_allocate_quene){
                                if($ticket_allocate_quene->status == 1)  continue;
                            }else{
                              $insert_tkt_all_quene = DB::table('ticket_allocate_quene')
                                                      ->insert([
                                                        'status' => 1,
                                                        'company_id' => $company_id,
                                                        'ticket_type_id' => $type,
                                                        'ticket_price_id' => $price_id
                                                      ]);
                            }

                            // get ticket allocate
                            $tickets_current_not_null = TicketAllocate::where('company_id', $company_id)
                                        ->where('ticket_type_id', $type)
                                        ->where('device_id', $device_id)
                                        ->where('current_number', '!=', NULL)
                                        ->orderBy('end_number', 'DESC')
                                        ->orderBy('current_number', 'DESC')
                                        ->orderBy('created_at', 'DESC')
                                        ->limit(2)
                                        ->get();

                            // get ticket allocate
                            $tickets_current_null = TicketAllocate::where('company_id', $company_id)
                                        ->where('ticket_type_id', $type)
                                        ->where('device_id', $device_id)
                                        ->where('current_number', NULL)
                                        ->orderBy('end_number', 'DESC')
                                        ->orderBy('created_at', 'DESC')
                                        ->limit(2)
                                        ->get();

                            $start_number = 1;
                            $end_number = 300;
                            if($ticket_price->ticketType['type'] == 1){
                                $end_number = 100;
                            }

                            //current == null => has data && current != null => has data
                            if(count($tickets_current_not_null) > 0 && count($tickets_current_null) > 0){

                                if((int)$tickets_current_not_null[0]->end_number - (int)$tickets_current_not_null[0]->current_number > 0){

                                    $start_number = (int) $tickets_current_not_null[0]->current_number + $start_number ;
                                    $end_number = (int) $tickets_current_not_null[0]->end_number;
                                }

                                if((int)$tickets_current_not_null[0]->end_number - (int)$tickets_current_not_null[0]->current_number == 0){

                                    if(count($tickets_current_null) == 1){
                                        $start_number = (int) $tickets_current_null[0]->start_number;
                                        $end_number = (int) $tickets_current_null [0]->end_number;
                                    }

                                    if(count($tickets_current_null) == 2){

                                        if((int) $tickets_current_null [0]->end_number > (int) $tickets_current_null [1]->end_number){
                                            $start_number = (int) $tickets_current_null[1]->start_number;
                                            $end_number = (int) $tickets_current_null [1]->end_number;
                                        }else{
                                            $start_number = (int) $tickets_current_null[0]->start_number;
                                            $end_number = (int) $tickets_current_null [0]->end_number;
                                        }
                                    }
                                }
                            }

                            //current == null => hasn't data && current != null => has data
                            if(count($tickets_current_not_null) == 0 && count($tickets_current_null) > 0){

                                if(count($tickets_current_null) == 1){
                                    $start_number = (int) $tickets_current_null[0]->start_number;
                                    $end_number = (int) $tickets_current_null [0]->end_number;
                                }

                                if(count($tickets_current_null) == 2){

                                    if((int) $tickets_current_null [0]->end_number > (int) $tickets_current_null [1]->end_number){
                                        $start_number = (int) $tickets_current_null[1]->start_number;
                                        $end_number = (int) $tickets_current_null [1]->end_number;
                                    }else{
                                        $start_number = (int) $tickets_current_null[0]->start_number;
                                        $end_number = (int) $tickets_current_null [0]->end_number;
                                    }
                                }
                            }

                            //current == null => has data && current != null => hasn't data
                            if(count($tickets_current_not_null) > 0 && count($tickets_current_null) == 0){

                                if((int)$tickets_current_not_null[0]->end_number - (int)$tickets_current_not_null[0]->current_number > 0){

                                    $start_number = (int) $tickets_current_not_null[0]->current_number + $start_number ;
                                    $end_number = (int) $tickets_current_not_null[0]->end_number;
                                }

                                if((int)$tickets_current_not_null[0]->end_number - (int)$tickets_current_not_null[0]->current_number == 0){

                                    // get ticket allocate
                                    $ticket_not_device = TicketAllocate::where('company_id', $company_id)
                                                            ->where('ticket_type_id', $type)
                                                            ->orderBy('end_number', 'DESC')
                                                            ->orderBy('created_at', 'DESC')
                                                            ->first();

                                    // if exist
                                    if (!empty($ticket_not_device)) {

                                        $start_number = (int) $ticket_not_device->end_number + $start_number;
                                        $end_number = (int) $ticket_not_device->end_number + $end_number;
                                    }
                                }
                            }

                            //current == null => has data && current != null => has data
                            if(count($tickets_current_not_null) == 0 && count($tickets_current_null) == 0){

                                // get ticket allocate
                                $ticket_not_device = TicketAllocate::where('company_id', $company_id)
                                                        ->where('ticket_type_id', $type)
                                                        ->orderBy('end_number', 'DESC')
                                                        ->orderBy('created_at', 'DESC')
                                                        ->first();

                                // if exist
                                if (!empty($ticket_not_device)) {

                                    $start_number = (int) $ticket_not_device->end_number + $start_number;
                                    $end_number = (int) $ticket_not_device->end_number + $end_number;
                                }
                            }

                            if(!empty($ticket_price->limit_number)){

                                $check_start_number = (int)$ticket_price->limit_number -  $start_number;
                                $check_end_number = (int)$ticket_price->limit_number -  $end_number;

                                if($check_start_number <= 0 ){
                                    continue;
                                }

                                if($check_start_number > 0  &&   $check_end_number <= 0 ){

                                    $end_number = (int)$ticket_price->limit_number;
                                }
                            }

                            $check_ticket_allocate = $this->checkTicketAllocateExistsByOptions([
                                ['company_id', $company_id],
                                ['device_id', $device_id],
                                ['ticket_type_id', $type],
                                ['ticket_price_id', $price_id],
                                ['start_number', $start_number],
                                ['end_number', $end_number]
                            ]);

                            if($check_ticket_allocate){

                                // push data return
                                $arr_ticket = array(
                                    'ticket_type_id' => $type,
                                    'ticket_price_id' => $price_id,
                                    'price' => $price,
                                    'start_number' => $start_number,
                                    'end_number' => $end_number,
                                    'company_id' => $company_id,
                                    'device_id' => $device_id
                                );
                                array_push($ticket_list, $arr_ticket);

                            }else{

                                $ticket_allocate = new TicketAllocate();
                                $ticket_allocate->company_id = $company_id;
                                $ticket_allocate->device_id = $device_id;
                                $ticket_allocate->ticket_type_id = $type;
                                $ticket_allocate->ticket_price_id = $price_id;
                                $ticket_allocate->start_number = $start_number;
                                $ticket_allocate->end_number = $end_number;
                                $ticket_allocate->created_at = date('Y-m-d H:i:s');
                                $ticket_allocate->updated_at = date('Y-m-d H:i:s');

                                if ($ticket_allocate->save()) {

                                    // push data return
                                    $arr_ticket = array(
                                        'ticket_type_id' => $type,
                                        'ticket_price_id' => $price_id,
                                        'price' => $price,
                                        'start_number' => $start_number,
                                        'end_number' => $end_number,
                                        'company_id' => $company_id,
                                        'device_id' => $device_id
                                    );
                                    array_push($ticket_list, $arr_ticket);
                                }
                            }

                            //update quene ticket allocate status = 0
                            $update_tkt_all_quene = DB::table('ticket_allocate_quene')
                                                    ->where([
                                                      ['company_id' ,'=', $company_id],
                                                      ['ticket_type_id' ,'=', $type],
                                                      ['ticket_price_id' ,'=', $price_id]
                                                    ])
                                                    ->update(['status' => 0]);
                        }
                    }
                }
            }

            if($init == null){

                foreach ($ticket_types as $type) {

                    // check null and bigger 0
                    if (!empty($type) && (int) $type > 0) {

                        // get ticket prices by type
                        $type = (int) $type;
                        $ticket_price = $this->ticket_prices->getPriceByTicketTypeId($type);

                        if ($ticket_price) {

                            $price_id = $ticket_price->id;
                            $price = $ticket_price->price;

                            //--check quene syn ticket allocate
                            $ticket_allocate_quene = DB::table('ticket_allocate_quene')
                                                    ->where([
                                                      ['company_id','=',$company_id],
                                                      ['ticket_type_id','=',$type],
                                                      ['ticket_price_id','=',$price_id]
                                                    ])->first();
                            if($ticket_allocate_quene){
                                if($ticket_allocate_quene->status == 1)  continue;
                            }else{
                              $insert_tkt_all_quene = DB::table('ticket_allocate_quene')
                                                      ->insert([
                                                        'status' => 1,
                                                        'company_id' => $company_id,
                                                        'ticket_type_id' => $type,
                                                        'ticket_price_id' => $price_id
                                                      ]);
                            }

                            // get ticket allocate
                            $ticket_allocate_device = TicketAllocate::where('company_id', $company_id)
                                                        ->where('device_id', $device_id)
                                                        ->where('ticket_type_id', $type)
                                                        ->where('current_number', NULL)
                                                        ->orderBy('end_number', 'DESC')
                                                        ->orderBy('created_at', 'DESC')
                                                        ->first();

                            // if exist
                            $start_number = 1;
                            $end_number = 300;
                            if($ticket_price->ticketType['type'] == 1){
                                $end_number = 100;
                            }

                            if (!empty($ticket_allocate_device)) {

                                $start_number = (int) $ticket_allocate_device->start_number;
                                $end_number = (int) $ticket_allocate_device->end_number;
                            }else{

                                // get ticket allocate
                                $ticket_allocate_device_not = TicketAllocate::where('company_id', $company_id)
                                                    ->where('ticket_type_id', $type)
                                                    ->orderBy('end_number', 'DESC')
                                                    ->orderBy('created_at', 'DESC')
                                                    ->first();
                                if(!empty($ticket_allocate_device_not)){

                                    $start_number = (int) $ticket_allocate_device_not->end_number + $start_number;
                                    $end_number = (int) $ticket_allocate_device_not->end_number + $end_number;
                                }
                            }

                            if(!empty($ticket_price->limit_number)){

                                $check_start_number = (int)$ticket_price->limit_number -  $start_number;
                                $check_end_number = (int)$ticket_price->limit_number -  $end_number;

                                if($check_start_number <= 0 ){
                                    return response('Ticket number is limited, please update limit number', 404);
                                }

                                if($check_start_number > 0  &&   $check_end_number <= 0 ){

                                    $end_number = (int)$ticket_price->limit_number;
                                }

                            }

                            $check_ticket_allocate = $this->checkTicketAllocateExistsByOptions([
                                ['company_id', $company_id],
                                ['device_id', $device_id],
                                ['ticket_type_id', $type],
                                ['ticket_price_id', $price_id],
                                ['start_number', $start_number],
                                ['end_number', $end_number]
                            ]);

                            if($check_ticket_allocate){

                                // push data return
                                $arr_ticket = array(
                                    'ticket_type_id' => $type,
                                    'ticket_price_id' => $price_id,
                                    'price' => $price,
                                    'start_number' => $start_number,
                                    'end_number' => $end_number,
                                    'company_id' => $company_id,
                                    'device_id' => $device_id
                                );
                                array_push($ticket_list, $arr_ticket);

                            }else{

                                $ticket_allocate = new TicketAllocate();
                                $ticket_allocate->company_id = $company_id;
                                $ticket_allocate->device_id = $device_id;
                                $ticket_allocate->ticket_type_id = $type;
                                $ticket_allocate->ticket_price_id = $price_id;
                                $ticket_allocate->start_number = $start_number;
                                $ticket_allocate->end_number = $end_number;
                                $ticket_allocate->created_at = date('Y-m-d H:i:s');
                                $ticket_allocate->updated_at = date('Y-m-d H:i:s');

                                if ($ticket_allocate->save()) {

                                    // push data return
                                    $arr_ticket = array(
                                        'ticket_type_id' => $type,
                                        'ticket_price_id' => $price_id,
                                        'price' => $price,
                                        'start_number' => $start_number,
                                        'end_number' => $end_number,
                                        'company_id' => $company_id,
                                        'device_id' => $device_id
                                    );
                                    array_push($ticket_list, $arr_ticket);
                                }
                            }

                                //update quene ticket allocate status = 0
                                $update_tkt_all_quene = DB::table('ticket_allocate_quene')
                                        ->where([
                                            ['company_id' ,'=', $company_id],
                                            ['ticket_type_id' ,'=', $type],
                                            ['ticket_price_id' ,'=', $price_id]
                                        ])
                                        ->update(['status' => 0]);
                        }
                    }
                }
            }

            return $ticket_list;
        }
    }

    public function ticketAllocateByCompany8($device_id, $ticket_types = array(), $company_id) {

        // check current ticket number by type
        $ticket_list = [];

        foreach ($ticket_types as $type) {

            // check null and bigger 0
            if (!empty($type) && (int) $type > 0) {

                // get ticket prices by type
                $type = (int) $type;
                $ticket_price = $this->ticket_prices->getPriceByTicketTypeId($type);

                if ($ticket_price) {

                    $price_id = $ticket_price->id;
                    $price = $ticket_price->price;

                    // get ticket allocate
                    $ticket = TicketAllocate::where('company_id', $company_id)
                                ->where('ticket_type_id', $type)
                                ->orderBy('end_number', 'DESC')
                                ->limit(1)->first();

                    // if exist
                    $start_number = 1;
                    $end_number = 100;

                    if (!empty($ticket)) {

                        $start_number = (int) $ticket->end_number + $start_number;
                        $end_number = (int) $ticket->end_number + $end_number;
                    }

                    $ticket_allocate = new TicketAllocate();
                    $ticket_allocate->company_id = $company_id;
                    $ticket_allocate->device_id = $device_id;
                    $ticket_allocate->ticket_type_id = $type;
                    $ticket_allocate->ticket_price_id = $price_id;
                    $ticket_allocate->start_number = $start_number;
                    $ticket_allocate->end_number = $end_number;
                    $ticket_allocate->created_at = date('Y-m-d H:i:s');

                    if ($ticket_allocate->save()) {

                        // push data return
                        $arr_ticket = array(
                            'ticket_type_id' => $type,
                            'ticket_price_id' => $price_id,
                            'price' => $price,
                            'start_number' => $start_number,
                            'end_number' => $end_number,
                            'company_id' => $company_id,
                            'device_id' => $device_id
                        );

                        array_push($ticket_list, $arr_ticket);
                    }
                }
            }
        }

        return $ticket_list;
    }

    public function getTicketAllocateByOptions($options = [], $orderBy = 'id'){

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return TicketAllocate::where($options)->orderBy($orderBy, 'asc')->get();
        }

        return response('Error', 404);
    }

    public function getTicketAllocateByCompanyInTicketTypeId($ticket_type_id_arr){

        return TicketAllocate::whereIn('ticket_type_id', $ticket_type_id_arr)
                ->with('ticketType')
                ->orderBy('ticket_price_id', 'asc')
                ->get();
    }

    public function updateCurrentNumberByDeviceIdAndTicketPriceId($company_id,$device_id, $ticket_price_id, $current_number, $updated_at){

        $ticket_allocate =  TicketAllocate::where('company_id', $company_id)
                    ->where('device_id', $device_id)
                    ->where('ticket_price_id', $ticket_price_id)
                    ->where('start_number', '<=', (int)$current_number)
                    ->where('end_number', '>=', (int)$current_number)
                    ->orderBy('start_number', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->first();

        if($ticket_allocate){

            if(strlen($current_number) >=  14){

                $ticket_allocate->current_number = NULL;
            }else{

                $ticket_allocate->current_number = $current_number;
            }
            $ticket_allocate->updated_at = $updated_at;

            if($ticket_allocate->save()){
                return $ticket_allocate;
            }
        }
    }

    public function listTicketAllocatesByCompanyId($company_id){

        $ticket_type_id_arr = $this->ticket_types->getIdListTicketTypeByCompanyId($company_id);

        if($ticket_type_id_arr){

            $ticket_allocates = $this->getTicketAllocateByCompanyInTicketTypeId($ticket_type_id_arr);

            $ticket_allocate_result = [];
            if (count($ticket_allocates) > 0) {

                $n = 0; $m = 0; $q=0;
                for($i = 0; $i < count($ticket_allocates); $i++){

                    $ticket_price_id = $ticket_allocates[$i]['ticket_price_id'];

                    if(($i+1 == count($ticket_allocates)) || $ticket_price_id != $ticket_allocates[$i+1]['ticket_price_id']){

                        $start_number = $ticket_allocates[$n]['start_number'];
                        $end_number = $ticket_allocates[$m]['end_number'];
                        $current_number = $ticket_allocates[$q]['current_number'] ?  (double)$ticket_allocates[$q]['current_number'] : 0;
                        $prices = $this->ticket_prices->getPriceById($ticket_price_id);
                        $order_code = $ticket_allocates[$i]['ticketType']['order_code'] ?? '';
                        $sign = $ticket_allocates[$i]['ticketType']['sign'] ?? '';
                        $price  = 0;
                        if(empty($prices)){
                            break;
                        }else{
                            $price = $prices->price;
                        }

                        $limit_number = $prices->limit_number;
                        $status_warning = 0;

                        if(!empty($limit_number)){
                            $check_allocate = (double)($end_number/$limit_number)*100;
                            if( $check_allocate >= 90.0) $status_warning = 1;
                        }

                        $ticket_tmp = array(
                            'price' => $price,
                            'order_code' => $order_code,
                            'sign' => $sign,
                            'start_number' => $start_number,
                            'end_number' => $end_number,
                            'current_number' => $current_number,
                            'status_warning' => $status_warning,
                            'ticket_price_id' => $ticket_price_id
                        );
                        array_push($ticket_allocate_result, $ticket_tmp);
                        $n = $i+1; $m = $i+1; $q= $i+1;
                    }else {
                        if($ticket_price_id == $ticket_allocates[$i+1]['ticket_price_id']){

                            if($ticket_allocates[$n]['start_number'] > $ticket_allocates[$i+1]['start_number'])
                            $n = $i+1;
                            if($ticket_allocates[$m]['end_number'] < $ticket_allocates[$i+1]['end_number'])
                            $m = $i+1;
                            if((int)$ticket_allocates[$q]['current_number'] < (int)$ticket_allocates[$i+1]['current_number'])
                            $q = $i+1;
                        }
                    }
                }
            }
            return $ticket_allocate_result;
        }
    }

    public function checkTicketAllocateExistsByOptions($options){

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return TicketAllocate::where($options)->exists();
        }

        return response('Ticket allocate not found', 404);
    }

    public function listTicketAllocatesBySearchBy($data){


        $from_date = $data['from_date'] ? date("Y-m-d 00:00:00", strtotime($data['from_date'])) : null;
        $to_date = $data['to_date'] ? date("Y-m-d 23:59:59", strtotime($data['to_date'])) : null;
        $company_id = $data['company_id'] ;
        $ticket_type_id = $data['ticket_type_id'] ?? null;
        $device_id = $data['device_id'] ?? null;

        $where_option = [];

        if(!empty($device_id)){
            $where_option['ticket_allocate.device_id'] = $device_id;
        }

        if(!empty($ticket_type_id)){
            $where_option['ticket_allocate.ticket_type_id'] = $ticket_type_id;
        }

        $ticket_allocates = [];

        if($from_date != null || $to_date != null){

            $ticket_allocates = TicketAllocate::join('ticket_types', 'ticket_types.id', '=', 'ticket_allocate.ticket_type_id')
                        ->join('ticket_prices', 'ticket_prices.id', '=', 'ticket_allocate.ticket_price_id')
                        ->where('ticket_types.deleted_at', '=', null)
                        ->where('ticket_allocate.company_id', $company_id)
                        ->where('ticket_allocate.created_at', '>=', $from_date)
                        ->where('ticket_allocate.created_at', '<=', $to_date)
                        ->where($where_option)
                        ->with('device')
                        ->select('ticket_allocate.*', 'ticket_types.sign', 'ticket_types.order_code', 'ticket_prices.price')
                        ->orderBy('ticket_allocate.device_id')
                        ->get();
        }

        if( ($ticket_type_id != null ||  $device_id != null) && ($from_date == null || $to_date == null)){

            $ticket_allocates = TicketAllocate::join('ticket_types', 'ticket_types.id', '=', 'ticket_allocate.ticket_type_id')
                        ->join('ticket_prices', 'ticket_prices.id', '=', 'ticket_allocate.ticket_price_id')
                        ->where('ticket_types.deleted_at', '=', null)
                        ->where('ticket_allocate.company_id', $company_id)
                        ->where($where_option)
                        ->with('device')
                        ->select('ticket_allocate.*', 'ticket_types.sign', 'ticket_types.order_code', 'ticket_prices.price')
                        ->orderBy('ticket_allocate.device_id')
                        ->get();
        }

        if( ($ticket_type_id != null ||  $device_id != null) && ($from_date != null || $to_date != null)) {

            $ticket_allocates = TicketAllocate::join('ticket_types', 'ticket_types.id', '=', 'ticket_allocate.ticket_type_id')
                        ->join('ticket_prices', 'ticket_prices.id', '=', 'ticket_allocate.ticket_price_id')
                        ->where('ticket_types.deleted_at', '=', null)
                        ->where('ticket_allocate.company_id', $company_id)
                        ->where('ticket_allocate.created_at', '>=', $from_date)
                        ->where('ticket_allocate.created_at', '<=', $to_date)
                        ->where($where_option)
                        ->with('device')
                        ->select('ticket_allocate.*', 'ticket_types.sign', 'ticket_types.order_code', 'ticket_prices.price')
                        ->orderBy('ticket_allocate.device_id')
                        ->get();
        }

        return $ticket_allocates;
    }

    function getTotalTicketCreated($data) {

        $ticketAllocate = TicketAllocate::where('company_id', $data['company_id'])
                            ->where('ticket_price_id', $data['ticket_price_id'])
                            ->where('created_at', '<=', $data['to_date'])
                            ->orderBy('end_number', 'desc')
                            ->first();
        return $ticketAllocate;
    }
}
