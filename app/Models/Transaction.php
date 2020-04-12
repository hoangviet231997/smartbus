<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property int $device_id
 * @property int $shift_id
 * @property int $ticket_price_id
 * @property string $ticket_number
 * @property boolean $is_used
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 * @property string $activated
 * @property int $user_id
 * @property int $duration
 * @property float $amount
 * @property Company $company
 * @property Device $device
 * @property Shift $shift
 * @property TicketPrice $ticketPrice
 */
class Transaction extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'company_id', 
        'device_id', 
        'shift_id', 
        'ticket_price_id', 
        'ticket_number', 
        'type', 
        'created_at', 
        'updated_at', 
        'user_id', 
        'amount', 
        'activated', 
        'duration', 
        'station_id', 
        'rfid', 
        'sign', 
        'balance', 
        'ticket_destroy', 
        'transaction_code',
        'link_company_id',
        'station_data',
        'station_down'
    ];

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
    public function shift()
    {
        return $this->belongsTo('App\Models\Shift');
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
    public function busStation()
    {
        return $this->belongsTo('App\Models\BusStation', 'station_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
