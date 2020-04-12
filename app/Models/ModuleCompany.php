<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleCompany extends Model
{
    use SoftDeletes;

    protected $table = 'module_companys';

    /**
     * @var array
     */
    protected $fillable = ['module_id', 'company_id', 'created_at', 'updated_at', 'deleted_at'];

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
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */

    public function module_app()
    {
        return $this->belongsTo('App\Models\ModuleApp', 'module_id');
    }
}
