<?php

namespace MichielKempen\LaravelQueueableActions\Tests;

use MichielKempen\LaravelQueueableActions\QueuedActionStatus;

class QueuedActionStatusTest extends TestCase
{
    /** @test */
    public function it_can_return_all_the_active_queued_action_statuses()
    {
        $queuedActionStatuses = QueuedActionStatus::active();

        $this->assertEquals(['pending', 'running'], array_values($queuedActionStatuses));
    }
}