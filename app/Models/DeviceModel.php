<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $model
 * @property string $features
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Device[] $devices
 * @property Firmware[] $firmwares
 */
class DeviceModel extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['model', 'features', 'name', 'created_at', 'updated_at', 'deleted_at'];

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
    public function devices()
    {
        return $this->hasMany('App\Models\Device');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function firmwares()
    {
        return $this->hasMany('App\Models\Firmware');
    }
}
