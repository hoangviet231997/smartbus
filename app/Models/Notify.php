<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    protected $table = "notifies";
    // protected $guarded = [];
    protected $fillable = ['title', 'company_id', 'subject_id', 'subject_data', 'readed', 'created_at', 'updated_at'];

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }
}
