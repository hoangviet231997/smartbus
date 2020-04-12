<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

/**
 * @property int $id
 * @property int $company_id
 * @property int $rfidcard_id
 * @property int $route_id
 * @property string $license_plates
 * @property boolean $is_running
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property RfidCard $rfidcard
 * @property Route $route
 * @property Attachment[] $attachments
 * @property Shift[] $shifts
 */
class Vehicle extends Model
{
    use SoftDeletes;
    use SpatialTrait;

    /**
     * @var array
     */

    protected $fillable = [
      'id',
      'company_id',
      'rfidcard_id',
      'license_plates',
      'is_running',
      'created_at',
      'updated_at',
      'deleted_at',
      'route_id',
      'device_imei',
      'bluetooth_mac_add',
      'bluetooth_pass'
    ];

    protected $spatialFields = ['location'];

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
    public function rfidcard()
    {
        return $this->belongsTo('App\Models\RfidCard');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function route()
    {
        return $this->belongsTo('App\Models\Route');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('App\Models\Attachment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shifts()
    {
        return $this->hasMany('App\Models\Shift');
    }
}
