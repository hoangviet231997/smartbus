<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $subject_id
 * @property string $subject_type
 * @property string $active
 * @property string $created_at
 * @property Company $company_id
 */
class PushLogs extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['action', 'subject_id', 'subject_type', 'subject_data', 'created_at', 'company_id'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
