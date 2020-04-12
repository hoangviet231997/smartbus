<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $membership_id
 * @property int $subscription_id
 * @property string $expiration_date
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Membership $membership
 * @property SubscriptionType $subscriptionType
 */
class MembershipSubscription extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'memberships_subscription';

    /**
     * @var array
     */
    protected $fillable = ['membership_id', 'subscription_id', 'expiration_date', 'created_at', 'updated_at', 'deleted_at'];

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
    public function membership()
    {
        return $this->belongsTo('App\Models\Membership');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriptionType()
    {
        return $this->belongsTo('App\Models\SubscriptionType', 'subscription_id');
    }
}
