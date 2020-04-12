<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $logo
 * @property string $tax_code
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $email
 * @property Issued[] $issueds
 * @property Membership[] $memberships
 * @property PrepaidCard[] $prepaidCards
 * @property Route[] $routes
 * @property SubscriptionType[] $subscriptionTypes
 * @property TicketAllocate[] $ticketAllocates
 * @property TicketType[] $ticketTypes
 * @property Transaction[] $transactions
 * @property User[] $users
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point $position
 */
class Company extends Model
{
    use SoftDeletes;
    use SpatialTrait;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 
        'address', 
        'phone', 
        'logo', 
        'tax_code', 
        'created_at', 
        'updated_at', 
        'deleted_at', 
        'email', 
        'subname', 
        'fullname',
        'layout_cards'
    ];

    protected $spatialFields = ['position'];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issueds()
    {
        return $this->hasMany('App\Models\Issued');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function memberships()
    {
        return $this->hasMany('App\Models\Membership');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prepaidCards()
    {
        return $this->hasMany('App\Models\PrepaidCard');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routes()
    {
        return $this->hasMany('App\Models\Route');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptionTypes()
    {
        return $this->hasMany('App\Models\SubscriptionType');
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
    public function ticketTypes()
    {
        return $this->hasMany('App\Models\TicketType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupKeys(){
        return $this->belongsToMany('App\Models\GroupKey');
    }
}
