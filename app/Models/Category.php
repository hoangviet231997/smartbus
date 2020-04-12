<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $table = "categories";
    protected $guarded = [];
    protected $hidden = ['deleted_at'];
    protected $dates = ['deleted_at'];
}
