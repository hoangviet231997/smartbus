<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $ticket_type_id
 * @property float $price
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property TicketType $ticketType
 * @property TicketAllocate[] $ticketAllocates
 * @property Ticket[] $tickets
 */
class TicketPrice extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['ticket_type_id', 'price', 'created_at', 'updated_at', 'deleted_at', 'limit_number', 'charge_limit'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketType()
    {
        return $this->belongsTo('App\Models\TicketType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ticketAllocates()
    {
        return $this->hasMany('App\Models\TicketAllocate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket');
    }
}
