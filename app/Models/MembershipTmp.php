<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTmp extends Model
{
    /**
     * @var array
     */
    protected $table = 'memberships_tmp';

    protected $fillable = [
        'company_id',
        'fullname',
        'gender',
        'birthday',
        'address',
        'phone',
        'cmnd',
        'email',
        'avatar',
        'accept',
        'membershiptype_id',
        'created_at',
        'updated_at'
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function membershipType()
    {
        return $this->belongsTo('App\Models\MembershipType', 'membershiptype_id');
    }
}
