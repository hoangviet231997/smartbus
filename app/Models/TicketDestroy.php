<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property int $shift_id
 * @property int $ticket_price_id
 * @property int $transaction_id
 * @property string $ticket_number
 * @property string $type
 * @property string $created_at
 * @property string $printed_at
 * @property string $updated_at
 * @property string $description
 * @property string $image
 * @property int $user_id
 * @property float $amount
 * @property Company $company
 * @property Shift $shift
 * @property TicketPrice $ticketPrice
 */
class TicketDestroy extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['company_id', 'shift_id', 'transaction_id', 'ticket_price_id', 'ticket_number', 'type', 'user_id', 'amount', 'description', 'image', 'printed_at','created_at', 'updated_at', 'accept', 'subuser_id'];

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
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subuser()
    {
        return $this->belongsTo('App\Models\User', 'subuser_id');
    }
    
}
