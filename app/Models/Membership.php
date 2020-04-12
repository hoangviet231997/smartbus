<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property int $rfidcard_id
 * @property string $fullname
 * @property string $address
 * @property string $phone
 * @property float $balance
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $sidn
 * @property string $email
 * @property string $birthday
 * @property Company $company
 * @property RfidCard $rfidcard
 * @property MembershipsSubscription[] $membershipsSubscriptions
 */
class Membership extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $table = 'memberships';

    protected $fillable = [
        'company_id', 
        'fullname', 
        'address', 
        'phone', 
        'balance', 
        'expiration_date', 
        'membershiptype_id', 
        'duration', 
        'created_at', 
        'updated_at', 
        'deleted_at', 
        'sidn', 
        'email', 
        'birthday', 
        'rfidcard_id', 
        'actived', 
        'user_id', 
        'cmnd', 
        'avatar',
        'gender',
        'station_data', 
        'charge_limit',
        'charge_count',
        'ticket_price_id',
        'start_expiration_date',
        'gr_bus_station_id',
        'code',
        'password'
    ];
    
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rfidcard()
    {
        return $this->belongsTo('App\Models\RfidCard', 'rfidcard_id');
    }    

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function membershipType()
    {
        return $this->belongsTo('App\Models\MembershipType', 'membershiptype_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketPrice()
    {
        return $this->belongsTo('App\Models\TicketPrice', 'ticket_price_id');
    }
    
}
