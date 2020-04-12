<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property int $device_id
 * @property int $ticket_price_id
 * @property int $ticket_type_id
 * @property int $start_number
 * @property int $end_number
 * @property string $created_at
 * @property Company $company
 * @property Device $device
 * @property TicketPrice $ticketPrice
 * @property TicketType $ticketType
 */
class TicketAllocate extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'ticket_allocate';

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'device_id', 'ticket_price_id', 'ticket_type_id', 'start_number', 'end_number', 'created_at', 'updated_at'];

    public $timestamps = false;
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketPrice()
    {
        return $this->belongsTo('App\Models\TicketPrice');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketType()
    {
        return $this->belongsTo('App\Models\TicketType');
    }
}
