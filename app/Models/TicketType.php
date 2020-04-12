<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property int $duration
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $sign
 * @property string $sign_form
 * @property string $order_code
 * @property Company $company
 * @property TicketAllocate[] $ticketAllocates
 * @property TicketPrice[] $ticketPrices
 */
class TicketType extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'description', 'created_at', 'updated_at', 'deleted_at', 'sign', 'sign_form', 'order_code', 'duration', 'safe_of', 'language', 'type'];

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
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
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
    public function ticketPrices()
    {
        return $this->hasMany('App\Models\TicketPrice');
    }
}
