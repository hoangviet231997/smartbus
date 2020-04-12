<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $device_id
 * @property string $command
 * @property string $params
 * @property boolean $is_running
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Device $device
 */
class DevicePollCommand extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['device_id', 'command', 'params', 'is_running', 'created_at', 'updated_at', 'deleted_at'];

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
    public function device()
    {
        return $this->belongsTo('App\Models\Device');
    }
}
