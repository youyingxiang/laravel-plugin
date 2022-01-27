<?php
namespace Yxx\LaravelPlugin\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yxx\LaravelPlugin\Enums\PluginStatus;

class InstallPlugin extends Model
{
    /**
     * @var array
     */
    public $guarded = [];

    /**
     * @var string[]
     */
    public $casts = [
        'composer' => 'json',
        'status' => PluginStatus::class
    ];

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeEnable(Builder $query): Builder
    {
        return $query->where('status', PluginStatus::enable());
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDisable(Builder $query): Builder
    {
        return $query->where('status', PluginStatus::disable());
    }

    /**
     * @return string
     */
    public function getLowerNameAttribute(): string
    {
        return strtolower($this->name);
    }
}