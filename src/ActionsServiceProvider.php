<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\ServiceProvider;

class ActionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! class_exists('CreateQueuedActionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_queued_actions_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_queued_actions_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__.'/../config/actions.php' => config_path('actions.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/actions.php', 'actions');
    }
}