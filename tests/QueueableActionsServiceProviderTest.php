<?php

namespace MichielKempen\LaravelQueueableActions\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class QueueableActionsServiceProviderTest extends TestCase
{
    /** @test */
    public function it_can_create_the_queued_actions_table()
    {
        Artisan::call('vendor:publish', [
            "--provider" => "MichielKempen\QueueableActions\QueueableActionsServiceProvider",
            "--tag" => "migrations"
        ]);

        Artisan::call('migrate');

        $this->assertTrue(Schema::hasTable('queued_actions'));
    }
}