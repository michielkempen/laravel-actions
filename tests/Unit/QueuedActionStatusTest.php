<?php

namespace MichielKempen\LaravelActions\Tests\Unit;

use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class QueuedActionStatusTest extends TestCase
{
    /** @test */
    public function it_can_return_all_the_active_queued_action_statuses()
    {
        $queuedActionStatuses = ActionStatus::active();

        $this->assertEquals(['pending', 'running'], array_values($queuedActionStatuses));
    }
}