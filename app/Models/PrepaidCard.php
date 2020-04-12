<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property int $rfidcard_id
 * @property float $balance
 * @property string $created_at
 * @property string $expiration_date
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $barcode
 * @property Company $company
 * @property RfidCard $rfidcard
 */
class PrepaidCard extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'balance', 'created_at', 'expiration_date', 'updated_at', 'deleted_at', 'rfidcard_id'];

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
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rfidcard()
    {
        return $this->belongsTo('App\Models\RfidCard');
    }        
}
