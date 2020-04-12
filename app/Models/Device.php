<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

/**
 * @property int $id
 * @property int $device_model_id
 * @property float $lng
 * @property float $lat
 * @property string $identity
 * @property int $version
 * @property boolean $is_running
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property DeviceModel $deviceModel
 * @property Attachment[] $attachments
 * @property DevicePollCommand[] $devicePollCommands
 * @property Gp[] $gps
 * @property Issued[] $issueds
 * @property Shift[] $shifts
 * @property TicketAllocate[] $ticketAllocates
 * @property Transaction[] $transactions
 */
class Device extends Model
{
    use SoftDeletes;
    use SpatialTrait;    

    /**
     * @var array
     */
    protected $fillable = ['device_model_id', 'identity', 'version', 'is_running', 'created_at', 'updated_at', 'deleted_at', 'status_device'];

    protected $spatialFields = ['position'];

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
    public function deviceModel()
    {
        return $this->belongsTo('App\Models\DeviceModel');
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
    public function devicePollCommands()
    {
        return $this->hasMany('App\Models\DevicePollCommand');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gps()
    {
        return $this->hasMany('App\Models\Gp');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issueds()
    {
        return $this->hasMany('App\Models\Issued');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shifts()
    {
        return $this->hasMany('App\Models\Shift');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ticketAllocates()
    {
        return $this->hasMany('App\Models\TicketAllocate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }
}
