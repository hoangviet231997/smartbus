<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $rfid
 * @property string $barcode
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $usage_type
 * @property int $target_id
 */
class RfidCard extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'rfidcards';

    /**
     * @var array
     */
    protected $fillable = ['rfid', 'barcode', 'deleted_at', 'created_at', 'updated_at', 'usage_type', 'target_id'];

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

}
