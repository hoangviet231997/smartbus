<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupKey extends Model
{
    use SoftDeletes;

    protected $table = 'group_key';

    /**
     * @var array
     */
    protected $fillable = ['name', 'key', 'created_at', 'updated_at','deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies(){
        return $this->belongsToMany('App\Models\Company');
    }
}