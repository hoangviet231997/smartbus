<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property float $lng
 * @property float $lat
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point $position
 */
class BusStation extends Model
{
    use SpatialTrait;

    /**
     * @var array
     */
    protected $fillable = [
        'id', 
        'name', 
        'address', 
        'created_at', 
        'updated_at', 
        'deleted_at', 
        'distance', 
        'station_order', 
        'url_sound',
        'direction',
        'station_relative'
    ];

    protected $hidden = ['deleted_at', 'updated_at', 'created_at'];

    protected $spatialFields = ['position'];
}
