<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $bus_station_id
 * @property int $route_id
 * @property string $create_at
 * @property BusStation $busStation
 * @property Route $route
 */
class RoutesBusStation extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'routes_bus_station';

    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['bus_station_id', 'route_id', 'create_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function busStation()
    {
        return $this->belongsTo('App\Models\BusStation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function route()
    {
        return $this->belongsTo('App\Models\Route');
    }
}
