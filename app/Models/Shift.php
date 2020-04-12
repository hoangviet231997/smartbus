<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $device_id
 * @property int $user_id
 * @property int $vehicle_id
 * @property int $route_id
 * @property boolean $collected 
 * @property boolean $shift_destroy 
 * @property string $started
 * @property string $ended
 * @property string $created_at
 * @property string $updated_at
 * @property int $subdriver_id
 * @property Device $device
 * @property Route $route
 * @property User $user
 * @property Vehicle $vehicle
 * @property Ticket[] $tickets
 */
class Shift extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['device_id', 'user_id', 'vehicle_id', 'started', 'ended', 'created_at', 'updated_at', 'subdriver_id', 'shift_token', 'route_id', 'collected','total_amount', 'station_id','hidden','shift_destroy'];

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
    public function route()
    {
        return $this->belongsTo('App\Models\Route');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket');
    }
}
