<?php
namespace Yxx\LaravelPlugin\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Yxx\LaravelPlugin\Support\Plugin;

class PluginDeleted
{
    use SerializesModels;

    public Plugin $plugin;

    public Carbon $happenedAt;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->happenedAt = now();
    }
}