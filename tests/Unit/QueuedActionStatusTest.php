<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Unit;

use MichielKempen\LaravelQueueableActions\QueuedActionStatus;
use MichielKempen\LaravelQueueableActions\Tests\TestCase;

class QueuedActionStatusTest extends TestCase
{
    /** @test */
    public function it_can_return_all_the_active_queued_action_statuses()
    {
        $queuedActionStatuses = QueuedActionStatus::active();

        $this->assertEquals(['pending', 'running'], array_values($queuedActionStatuses));
    }
}