<?php
namespace App\Services;

use App\Models\TicketType;
use App\Services\TicketPricesService;
use App\Services\PushLogsService;
use Illuminate\Support\Facades\DB;

class TicketTypesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    /**
     * @var App\Services\TicketPricesService
     */
    protected $ticket_prices;

    public function __construct(PushLogsService $push_logs, TicketPricesService $ticket_prices)
    {
        $this->push_logs = $push_logs;
        $this->ticket_prices = $ticket_prices;
    }

    public function createTicketType($data)
    {
        $name = $data['name'];
        $description = $data['description'] ?? null;
        $order_code = $data['order_code'] ?? null;
        $sign = $data['sign'] ?? null;
        $sign_form = $data['sign_form'] ?? null;
        $company_id = $data['company_id'];
        $price = $data['price'];
        $limit_number = $data['limit_number'] ?? null;
        $sale_of = $data['sale_of'] ?? 0;
        $language = $data['language'] ?? null;
        $duration = $data['duration'] * 3600;
        $number_km = $data['number_km'] ? (double)$data['number_km']*1000 : null;
        $type = (int)$data['type'];
        $charge_limit = $data['charge_limit'] ?? null;

        // save ticket type
        $ticket_type = new TicketType();
        $ticket_type->name = $name;
        $ticket_type->description = $description;
        $ticket_type->order_code = $order_code;
        $ticket_type->sign = $sign;
        $ticket_type->sign_form = $sign_form;
        $ticket_type->company_id = $company_id;
        $ticket_type->duration = $duration;
        $ticket_type->number_km = $number_km;
        $ticket_type->sale_of = $sale_of;
        $ticket_type->language = $language;
        $ticket_type->type = $type;

        if ($ticket_type->save()) {

            //create ticket price
            $ticket_price = $this->ticket_prices->createTicketPrice($ticket_type['id'], $price, $limit_number, $charge_limit);

            if($ticket_price){

                unset($ticket_type['created_at']);
                unset($ticket_type['updated_at']);
                $ticket_type['price'] = $price;
                $ticket_type['ticket_price_id'] = $ticket_price->id;
                $ticket_type['charge_limit'] = $ticket_price->charge_limit;
    
                $push_log = [];
                $push_log['action'] = 'create';
                $push_log['company_id'] = $company_id;
                $push_log['subject_id'] = $ticket_type['id'];
                $push_log['subject_type'] = 'ticket_type';
                $push_log['subject_data'] = $ticket_type;
                $this->push_logs->createPushLog($push_log);
    
                return $ticket_type;
            }
        }

        return response('Create Error', 404);
    }

    public function updateTicketType($data)
    {
        $id = $data['id'];
        $name = $data['name'];
        $description = $data['description'] ?? null;
        $order_code = $data['order_code'] ?? null;
        $sign = $data['sign'] ?? null;
        $sign_form = $data['sign_form'] ?? null;
        $company_id = $data['company_id'];
        $price = $data['price'];
        $limit_number = $data['limit_number'] ? (int) $data['limit_number'] : null;
        $sale_of = $data['sale_of'] ?? 0;
        $language = $data['language'] ?? null;
        $duration = $data['duration'] * 3600;
        $number_km = $data['number_km'] ? (double)$data['number_km']*1000 : null;
        $type = (int)$data['type'];
        $charge_limit = $data['charge_limit'] ?? null;

        $ticket_type = $this->getTicketTypeByIdAndCompanyId($id, $company_id);

        if (empty($ticket_type))
            return response('Ticket Type Not found', 404);

        $ticket_type->name = $name;
        $ticket_type->description = $description;
        $ticket_type->order_code = $order_code;
        $ticket_type->sign = $sign;
        $ticket_type->sign_form = $sign_form;
        $ticket_type->duration = $duration;
        $ticket_type->number_km = $number_km;
        $ticket_type->sale_of = $sale_of;
        $ticket_type->language = $language;
        $ticket_type->type = $type;

        if ($ticket_type->save()) {

            // get current ticket price
            $ticket_price = $ticket_type->ticketPrices->last();
            $ticket_price_new = null;

            if ((double) $price != (double) $ticket_price->price) {
                //create ticket price
                $ticket_price_new = $this->ticket_prices->createTicketPrice($ticket_type->id, $price, $limit_number, $charge_limit);
            }else{
                $ticket_price->limit_number = $limit_number;
                $ticket_price->charge_limit = $charge_limit;
                $ticket_price->save();
            }

            $ticket_type = $ticket_type->toArray();
            unset($ticket_type['ticket_prices']);
            unset($ticket_type['created_at']);
            unset($ticket_type['updated_at']);
            $ticket_type['price'] = $price;

            if((double) $price != (double) $ticket_price->price){
                $ticket_type['ticket_price_id'] = $ticket_price_new->id;
                $ticket_type['charge_limit'] = $ticket_price_new->charge_limit;
            }else{
                $ticket_type['ticket_price_id'] = $ticket_price->id;
                $ticket_type['charge_limit'] = $ticket_price->charge_limit;
            }
           
            $push_log = [];
            $push_log['action'] = 'update';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $ticket_type['id'];
            $push_log['subject_type'] = 'ticket_type';
            $push_log['subject_data'] = $ticket_type;
            $this->push_logs->createPushLog($push_log);

            return $this->getTicketTypeByIdAndCompanyId($ticket_type['id']);
        }

        return response('Update Error', 404);
    }

    public function deleteTicketType($id, $company_id)
    {
        $ticket_type = $this->getTicketTypeByIdAndCompanyId($id, $company_id);

        if (empty($ticket_type))
            return response('Ticket Type Not found', 404);

        // get push_log
        $push_logs = $this->push_logs->getPushLogByOptions([
                        ['subject_id', $id],
                        ['company_id', $company_id],
                        ['subject_type', 'ticket_type']
                    ]);
        if (count($push_logs) > 0) {
            $push_log = $push_logs[0];
            $this->push_logs->deletePushLog($push_log->id);

            $push_log = [];
            $push_log['action'] = 'delete';
            $push_log['company_id'] = $company_id;
            $push_log['subject_id'] = $id;
            $push_log['subject_type'] = 'ticket_type';
            $push_log['subject_data'] = null;
            $this->push_logs->createPushLog($push_log);
        }

        $this->ticket_prices->deleteTicketPriceByTicketType($id);

        if ($ticket_type->delete())
            return response('OK', 200);

        return response('Delete Error', 404);
    }

    public function listTicketTypes($data)
    {
        $limit = $data['limit'];
        $company_id = $data['company_id'];

        if (empty($limit) && $limit < 0)
            $limit = 10;

        $pagination = TicketType::where('company_id', $company_id)
                        ->with('ticketPrices')->paginate($limit)->toArray();

        header("pagination-total: ".$pagination['total']);
        header("pagination-current: ".$pagination['current_page']);
        header("pagination-last: ".$pagination['last_page']);

        return $pagination['data'];
    }

    public function listTicketTypeByTypes($data){

        if ((int) $data['type_param'] < 0) {
            return TicketType::where('company_id', $data['company_id'])->with('ticketPrices')->orderBy('type')->get();
        } else {
            return TicketType::where('company_id', $data['company_id'])
                ->where('type', (int) $data['type_param'])
                ->with('ticketPrices')->get();
        }
    }

    public function getTicketTypeByIdAndCompanyId($id, $company_id = null)
    {
        $ticket_type = TicketType::where('id', $id)
                            ->with('ticketPrices')
                            ->first();

        if (!empty($company_id)) {

            $ticket_type = TicketType::where('company_id', $company_id)
                                ->where('id', $id)
                                ->with('ticketPrices')
                                ->first();
        }

        return $ticket_type;
    }

    public function getTicketTypeByCompanyId($company_id){
        
        if(!empty($company_id)){

            $ticket_types = TicketType::where('company_id', $company_id)
                    ->with('ticketPrices')
                    ->get()
                    ->toArray();
            return $ticket_types;
        }
    }

    public function getTicketTypeByCompanyIdAndByType($company_id, $type){
        
        if(!empty($company_id)){
            if($type == -1){
                $ticket_types = TicketType::where('company_id', $company_id)
                    ->with('ticketPrices')
                    ->get()
                    ->toArray();
            }else{
                $ticket_types = TicketType::where('company_id', $company_id)
                    ->where('type', $type)
                    ->with('ticketPrices')
                    ->get()
                    ->toArray();
            }
            return $ticket_types;
        }
    }

    public function getIdListTicketTypeByCompanyId($company_id){

        return TicketType::where('company_id', $company_id)  
            ->pluck('id')
            ->toArray();
    }

    public function getTicketTypeById($id){
        
        $ticket_type = TicketType::where('id', $id)->first();

        return $ticket_type;
    }

    public function listTicketTypesWhereNotIn($data = array(), $company_id)
    {
        return TicketType::whereNotIn('id', $data)
                    ->where('company_id', $company_id)
                    ->where('type', 0)
                    ->with('ticketPrices')
                    ->get()
                    ->toArray();
    }

    public function getTicketTypesByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return TicketType::where($options)->get();
        }

        return response('Ticket Types Not found', 404);
    }

    public function searchTicketTypesByKeyWord($data)
    {

        $company_id = $data['company_id'];
        $key_input = $data['key_input'];
        $style_search = $data['style_search'];

        $ticket_type = TicketType::where('company_id', $company_id);


        if ($style_search == 'name') {
            $ticket_type->where('name', 'like', "%$key_input%");
        }
        if ($style_search == 'price') {
            $price_arr = $this->ticket_prices->getArrayIdTicketPriceByPrice($key_input);
            $ticket_type->whereIn('id',$price_arr);
        }
        if ($style_search == 'type') {
            $ticket_type->where('type',$key_input);
        }
        return $ticket_type->with('ticketPrices')->get()->toArray();
    }
}
