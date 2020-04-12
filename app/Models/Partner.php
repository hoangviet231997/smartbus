<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $company_fullnanme
 * @property string $company_nanme
 * @property string $address
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $email
 * @property string $url
 * @property string $partner_code
*/

class Partner extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['company_name', 'company_fullnanme', 'partner_code', 'address', 'phone', 'url', 'created_at', 'updated_at', 'deleted_at', 'email', 'app_key'];

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
