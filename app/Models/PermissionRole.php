<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $permission_id
 * @property int $role_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Permission $permission
 * @property Role $role
 */
class PermissionRole extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['permission_id', 'role_id', 'created_at', 'updated_at', 'deleted_at','company_id'];

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
    public function permission()
    {
        return $this->belongsTo('App\Models\Permission');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
}
