<?php

namespace MichielKempen\LaravelActions\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use MichielKempen\LaravelActions\Implementations\Async\QueuedActionJob;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ComplexAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\DataObject;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class QueueableActionTest extends TestCase
{
    /** @test */
    public function an_action_can_be_queued()
    {
        Queue::fake();

        $action = new SimpleAction;
        $action->queue()->execute();

        Queue::assertPushed(QueuedActionJob::class);
    }

    /** @test */
    public function an_action_with_dependencies_and_input_can_be_executed_on_the_queue()
    {
        /** @var ComplexAction $action */
        $action = app(ComplexAction::class);
        $action->queue()->execute(new DataObject('foo'));

        $this->assertLogHas('foo bar');
    }

    /** @test */
    public function an_action_can_be_executed_on_a_queue()
    {
        Queue::fake();

        /** @var ComplexAction $action */
        $action = app(ComplexAction::class);
        $action->queue = 'other';
        $action->queue()->execute(new DataObject('foo'));

        Queue::assertPushedOn('other', QueuedActionJob::class);
    }

    /** @test */
    public function an_action_is_executed_immediately_if_not_queued()
    {
        Queue::fake();

        /** @var ComplexAction $action */
        $action = app(ComplexAction::class);
        $action->queue = 'other';
        $action->execute(new DataObject('foo'));

        Queue::assertNotPushed(QueuedActionJob::class);
        $this->assertLogHas('foo bar');
    }

    /** @test */
    public function an_action_can_be_queued_with_a_chain_of_other_actions_jobs()
    {
        Queue::fake();

        /** @var ComplexAction $action */
        $action = app(ComplexAction::class);
        $action->queue()
            ->execute(new DataObject('foo'))
            ->chain([
                new QueuedActionJob(SimpleAction::class),
            ]);

        Queue::assertPushedWithChain(QueuedActionJob::class, [
            new QueuedActionJob(SimpleAction::class),
        ]);
    }
}