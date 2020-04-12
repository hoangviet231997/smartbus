<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipType extends Model
{
    use SoftDeletes;

    protected $table = 'membership_types';

    /**
     * @var array
     */
    protected $fillable = ['name', 'deduction', 'code', 'created_at', 'updated_at', 'deleted_at', 'company_id'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

}