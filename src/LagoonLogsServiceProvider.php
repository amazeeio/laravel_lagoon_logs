<?php

namespace amazeeio\LagoonLogs;

use Illuminate\Support\ServiceProvider;

class LagoonLogsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make('config')->set('logging.channels.LagoonLogs', [
          "driver" => "custom",
          "via" => "amazeeio\LagoonLogs\LagoonLoggerFactory",
        ]);
    }
}