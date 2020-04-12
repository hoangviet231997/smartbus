<?php
namespace App\Services;

use App\Models\TicketPrice;
use App\Services\PushLogsService;

class TicketPricesService
{
    /**
     * @var App\Services\PushLogsService
     */
    protected $push_logs;

    public function __construct(PushLogsService $push_logs)
    {
        $this->push_logs = $push_logs;
    }

    public function getPriceByTicketTypeIdAndPrice($ticket_type_id)
    {
        return TicketPrice::where('ticket_type_id', $ticket_type_id)
                    ->with('ticketType')
                    ->orderBy('id', 'desc')
                    ->first();
    }

    public function getPriceByTicketTypeId($ticket_type_id)
    {
        return TicketPrice::where('ticket_type_id', $ticket_type_id)
                    ->with('ticketType')
                    ->orderBy('id', 'desc')
                    ->first();
    }

    public function getPriceById($id)
    {
        return TicketPrice::find($id);
    }

    public function getDataPriceById($id){
        return TicketPrice::where('id', $id)
                ->with('ticketType')
                ->first();
    }

    public function deleteTicketPriceByTicketType($ticket_type_id){
        return TicketPrice::where('ticket_type_id',$ticket_type_id)->delete();
    }

    public function createTicketPrice($ticket_type_id, $price, $limit_number, $charge_limit)
    {
        $ticket_price = new TicketPrice();
        $ticket_price->price = (double) $price;
        $ticket_price->limit_number = $limit_number;
        $ticket_price->charge_limit = $charge_limit;
        $ticket_price->ticket_type_id = $ticket_type_id;
        $ticket_price->save();

        if ($ticket_price->save()) {
            return $ticket_price;
        }

        return response('Create Error', 404);
    }

    public function getTicketPricesByOptions($options = [])
    {

        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                if (count($option) == 2 && empty($option[1]))
                    unset($options[$key]);
            }

           return TicketPrice::where($options)->first();
        }

        return response('Ticket Price Not found', 404);
    }

    public function getListTicketPriceBycompany($data){
        $price_id = $data['price_id'] != 0 ? $data['price_id'] : null;
        
        $ticket_prices = TicketPrice::join('ticket_types', 'ticket_prices.ticket_type_id', '=', 'ticket_types.id')
                            ->when($price_id, function($ticket_prices) use ($price_id){
                                return $ticket_prices->where('ticket_prices.id', $price_id);
                            })
                            ->where('ticket_types.company_id', $data['company_id'])
                            ->with('ticketType')
                            ->orderBy('ticket_prices.price')
                            ->select('ticket_prices.*')
                            ->distinct()
                            ->get();
        return $ticket_prices;
    }

    public function getArrayIdTicketPriceByPrice ($price) {
        return TicketPrice::where('price','like','%'.(double)$price.'%')->groupBy('ticket_type_id')->pluck('ticket_type_id')->toArray();
    }

}
