<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppNotify extends Model
{
    use SoftDeletes;

    protected $table = "app_notifies";
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at'];

}
