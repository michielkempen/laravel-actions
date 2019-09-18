<?php

namespace MichielKempen\LaravelActions\Tests\Unit;

use Exception as PhpException;
use Illuminate\Support\Facades\Event;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use MichielKempen\LaravelActions\Events\QueuedActionUpdated;
use MichielKempen\LaravelActions\Implementations\Async\QueuedActionJob;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ComplexAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\DataObject;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use Mockery;
use Mockery\MockInterface;

class QueuedActionJobTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_when_an_action_class_is_provided()
    {
        $queuedActionJob = new QueuedActionJob(SimpleAction::class);

        $this->assertInstanceOf(QueuedActionJob::class, $queuedActionJob);
        $this->assertEquals(SimpleAction::class, $queuedActionJob->displayName());
    }

    /** @test */
    public function it_can_instantiate_when_an_action_instance_is_provided()
    {
        $complexAction = app(ComplexAction::class);
        $actionJob = new QueuedActionJob($complexAction, null, [new DataObject('foo')]);

        $this->assertInstanceOf(QueuedActionJob::class, $actionJob);
        $this->assertEquals(ComplexAction::class, $actionJob->displayName());
    }

    /** @test */
    public function it_can_instantiate_when_a_queued_action_id_is_provided()
    {
        $queuedAction = factory(QueuedAction::class)->create();

        $complexAction = app(ComplexAction::class);
        $actionJob = new QueuedActionJob($complexAction, $queuedAction->id, [new DataObject('foo')]);

        $this->assertInstanceOf(QueuedActionJob::class, $actionJob);
        $this->assertEquals(ComplexAction::class, $actionJob->displayName());
    }

    /** @test */
    public function it_can_handle_a_queued_action()
    {
        Event::fake();

        $complexAction = app(ComplexAction::class);
        $queuedAction = factory(QueuedAction::class)->create();
        $parameters = [new DataObject('foo')];
        $actionJob = new QueuedActionJob($complexAction, $queuedAction->id, $parameters);

        $this->mock(QueuedActionRepository::class, function(MockInterface $mock) use ($queuedAction) {
            $queuedAction->status = 'running';
            $mock
                ->shouldReceive('updateQueuedAction')
                ->with($queuedAction->id, 'running', null)
                ->andReturn($queuedAction);

            $queuedAction->status = 'succeeded';
            $mock
                ->shouldReceive('updateQueuedAction')
                ->with($queuedAction->id, 'succeeded', null)
                ->andReturn($queuedAction);

            $queuedAction->status = 'failed';
            $mock
                ->shouldNotReceive('updateQueuedAction')
                ->with($queuedAction->id, 'failed', "error message");
        });

        $this->mock(ComplexAction::class, function(MockInterface $mock) use ($parameters) {
            $mock
                ->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function($parameter) use ($parameters) {
                    return $parameter instanceof DataObject
                        && $parameter->foo == $parameters[0]->foo;
                }))
                ->andReturn();

            $mock
                ->shouldNotReceive('failed');
        });

        dispatch($actionJob);

        Event::assertDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'running'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });

        Event::assertDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'succeeded'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });

        Event::assertNotDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'failed'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });
    }

    /** @test */
    public function it_can_handle_a_failing_queued_action()
    {
        $this->markTestIncomplete("Cannot seem to find a way to let a job fail without throwing an exception.");

        Event::fake();

        $complexAction = app(ComplexAction::class);
        $queuedAction = factory(QueuedAction::class)->create();
        $parameters = [new DataObject('foo')];
        $actionJob = new QueuedActionJob($complexAction, $queuedAction->id, $parameters);

        $this->mock(QueuedActionRepository::class, function(MockInterface $mock) use ($queuedAction) {
            $queuedAction->status = 'running';
            $mock
                ->shouldReceive('updateQueuedAction')
                ->with($queuedAction->id, 'running', null)
                ->andReturn($queuedAction);

            $queuedAction->status = 'succeeded';
            $mock
                ->shouldNotReceive('updateQueuedAction')
                ->with($queuedAction->id, 'succeeded', null)
                ->andReturn($queuedAction);

            $queuedAction->status = 'failed';
            $mock
                ->shouldReceive('updateQueuedAction')
                ->with($queuedAction->id, 'failed', "error message")
                ->andReturn($queuedAction);
        });

        $exception = new PhpException("error message", 500);

        $this->mock(ComplexAction::class, function(MockInterface $mock) use ($exception, $parameters) {
            $mock
                ->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function($parameter) use ($parameters) {
                    return $parameter instanceof DataObject
                        && $parameter->foo == $parameters[0]->foo;
                }))
                ->andThrow($exception);

            $mock
                ->shouldReceive('failed')
                ->once()
                ->with($exception)
                ->andReturn();
        });

        dispatch($actionJob);

        Event::assertDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'running'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });

        Event::assertNotDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'succeeded'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });

        Event::assertDispatched(QueuedActionUpdated::class, function(QueuedActionUpdated $event) use ($queuedAction) {
            return $event->getQueuedAction()->getStatus() == 'failed'
                && $event->getQueuedAction()->getId() == $queuedAction->getId();
        });
    }
}