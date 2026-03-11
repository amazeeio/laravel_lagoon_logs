<?php

declare(strict_types=1);

namespace amazeeio\LagoonLogs;

use Illuminate\Support\ServiceProvider;

class LagoonLogsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make('config')->set('logging.channels.LagoonLogs', [
            'driver' => 'custom',
            'via' => LagoonLoggerFactory::class,
        ]);
    }
}
