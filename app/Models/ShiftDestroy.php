<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int $shift_id
 * @property int $driver_id
 * @property int $subdriver_id
 * @property string $description
 * @property string $work_time
 * @property int $accept
 * @property int $route_id
 * @property double $total_pos
 * @property double $total_charge
 * @property double $total_deposit
 * 
 */

class ShiftDestroy extends Model
{
    use SoftDeletes;

    protected $table = 'shift_destroys';

     /**
     * @var array
     */
    protected $fillable = ['company_id', 'shift_id', 'user_id', 'driver_id', 'subdriver_id', 'description', 'work_time', 'accept', 'license_plates', 'route_id', 'total_pos', 'total_charge', 'total_deposit'];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subdriver()
    {
        return $this->belongsTo('App\Models\User', 'subdriver_id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function route()
    {
        return $this->belongsTo('App\Models\Route', 'route_id');
    }
}
