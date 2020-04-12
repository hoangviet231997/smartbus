<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $template_route_id
 * @property string $name
 * @property string $address
 * @property float $lng
 * @property float $lat
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property TemplateRoute $templateRoute
 */
class TemplateBusStation extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['template_route_id', 'name', 'address', 'position', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function templateRoute()
    {
        return $this->belongsTo('App\Models\TemplateRoute');
    }
}
