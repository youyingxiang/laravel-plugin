<?php
namespace Yxx\LaravelPlugin\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * Class PluginStatus
 * @package Yxx\LaravelPlugin\Enums
 * @method static self enable()
 * @method static self disable()
 */
class PluginStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'disable' => 0,
            'enable' => 1,
        ];
    }

}