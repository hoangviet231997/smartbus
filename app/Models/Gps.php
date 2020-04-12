<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

/**
 * @property int $id
 * @property int $device_id
 * @property string $date
 * @property Device $device
 * @property \Grimzy\LaravelMysqlSpatial\Types\Point $position
 */
class Gps extends Model
{
    use SpatialTrait;
    
    /**
     * @var array
     */
    protected $fillable = ['device_id', 'date'];

    protected $spatialFields = ['position'];

    public $timestamps = false;
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device');
    }
}
