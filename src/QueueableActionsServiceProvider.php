<?php

namespace MichielKempen\QueueableActions;

use Illuminate\Support\ServiceProvider;

class QueueableActionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/queueable-actions.php' => config_path('queueable-actions.php'),
        ], 'config');

        if (! class_exists('CreateQueuedActionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_queued_actions_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_queued_actions_table.php'),
            ], 'migrations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/queueable-actions.php', 'queueable-actions');
    }
}