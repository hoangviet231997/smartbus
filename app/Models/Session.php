<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $ip_address
 * @property string $mac
 * @property string $agent
 * @property string $deleted_at
 * @property User $user
 */
class Session extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'token', 'ip_address', 'mac', 'agent', 'deleted_at'];

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
