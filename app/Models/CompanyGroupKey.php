<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyGroupKey extends Model
{
    use SoftDeletes;

    protected $table = 'company_group_key';

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'key', 'created_at', 'updated_at','deleted_at'];
}
