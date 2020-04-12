<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupBusStation extends Model
{
    protected $table = 'group_bus_stations';

    /**
     * @var array
     */
    protected $fillable = ['name', 'key', 'created_at','bus_stations','updated_at'];
}
