<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $device_model_id
 * @property string $server_ip
 * @property string $username
 * @property string $password
 * @property string $path
 * @property int $version
 * @property string $filename
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property DeviceModel $deviceModel
 */
class Firmware extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'firmwares';

    /**
     * @var array
     */
    protected $fillable = ['device_model_id', 'server_ip', 'username', 'password', 'path', 'version', 'filename', 'created_at', 'updated_at', 'deleted_at', 'note', 'company_id','update_type'];

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
        return $this->belongsTo('App\Models\DeviceModel', 'device_model_id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }
}
