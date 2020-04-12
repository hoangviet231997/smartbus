<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $display_name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $duration
 * @property float $price
 * @property Company $company
 * @property MembershipsSubscription[] $membershipsSubscriptions
 */
class SubscriptionType extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'display_name', 'created_at', 'updated_at', 'deleted_at', 'duration', 'price'];

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
    public function membershipsSubscriptions()
    {
        return $this->hasMany('App\Models\MembershipsSubscription', 'subscription_id');
    }
}
