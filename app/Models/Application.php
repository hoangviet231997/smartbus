<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $company_name
 * @property string $company_address
 * @property string $email
 * @property string $url
 * @property string $api_key
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class Application extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'apps';

    /**
     * @var array
     */
    protected $fillable = ['company_name', 'company_address', 'email', 'url', 'api_key', 'created_at', 'updated_at', 'deleted_at', 'company_id'];

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
}
