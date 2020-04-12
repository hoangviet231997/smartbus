<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftSupervisor extends Model
{
    protected $table = 'shift_supervisor';
    /**
     * @var array
     */
    protected $fillable = ['shift_id', 'user_id', 'started', 'ended', 'created_at', 'updated_at', 'shift_supervisor_token', 'station_up_id', 'station_down_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function shift()
    {
        return $this->belongsTo('App\Models\Shift');
    }
}
