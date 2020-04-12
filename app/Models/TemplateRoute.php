<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $start_time
 * @property string $end time
 * @property int $number
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property TemplateBusStation[] $templateBusStations
 */
class TemplateRoute extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['start_time', 'end_time', 'number', 'name', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templateBusStations()
    {
        return $this->hasMany('App\Models\TemplateBusStation');
    }
}
