<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property string $start_time
 * @property string $end time
 * @property int $number
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Company $company
 * @property BusStation[] $busStations
 */
class Route extends Model
{
    /**
     * @var array
     */
    

    protected $fillable = [
        'company_id', 
        'start_time', 
        'end_time', 
        'number', 
        'name', 
        'created_at', 
        'updated_at', 
        'deleted_at', 
        'module_data', 
        'ticket_data',
        'distance_scan',
        'timeout_sound'
    ];

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
    public function busStations()
    {
        return $this->hasMany('App\Models\BusStation');
    }
}
