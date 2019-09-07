<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Unit;

use MichielKempen\LaravelQueueableActions\QueuedActionJob;
use MichielKempen\LaravelQueueableActions\Tests\Support\ComplexAction;
use MichielKempen\LaravelQueueableActions\Tests\Support\DataObject;
use MichielKempen\LaravelQueueableActions\Tests\Support\SimpleAction;
use MichielKempen\LaravelQueueableActions\Tests\TestCase;

class QueuedActionJobTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_the_action_class()
    {
        $queuedActionJob = new QueuedActionJob(SimpleAction::class);

        $this->assertInstanceOf(QueuedActionJob::class, $queuedActionJob);
        $this->assertEquals(SimpleAction::class, $queuedActionJob->displayName());
    }

    /** @test */
    public function it_can_be_instantiated_from_an_action_instance()
    {
        $complexAction = app(ComplexAction::class);
        $actionJob = new QueuedActionJob($complexAction, null, [new DataObject('foo')]);

        $this->assertInstanceOf(QueuedActionJob::class, $actionJob);
        $this->assertEquals(ComplexAction::class, $actionJob->displayName());
    }
}