<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use MichielKempen\LaravelActions\Testing\TestCase;

class ActionsServiceProviderTest extends TestCase
{
    /** @test */
    public function it_can_create_the_queued_actions_table()
    {
        Artisan::call('vendor:publish', [
            "--provider" => "MichielKempen\LaravelActions\ActionsServiceProvider",
            "--tag" => "migrations"
        ]);

        Artisan::call('migrate');

        $this->assertTrue(Schema::hasTable('queued_actions'));
        $this->assertTrue(Schema::hasTable('queued_action_chains'));
    }
}
