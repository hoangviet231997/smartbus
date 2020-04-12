<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryNews extends Model
{
    use SoftDeletes;

    protected $table = "category_news";
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at'];
}
