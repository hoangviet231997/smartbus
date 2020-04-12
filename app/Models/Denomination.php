<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denomination extends Model
{
    protected $table = 'denominations';

    /**
     * @var array
     */
    protected $fillable = ['price','company_id','type','created_at', 'updated_at'];
}
