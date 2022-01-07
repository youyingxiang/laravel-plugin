<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $listens = config('plugins.listen');
        if (is_array($listens)) {
            foreach ($listens as $event => $listen) {
                Event::listen($event, $listen);
            }
        }
    }
}