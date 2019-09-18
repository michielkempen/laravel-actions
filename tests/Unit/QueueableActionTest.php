<?php

namespace MichielKempen\LaravelActions\Tests\Unit;

use MichielKempen\LaravelActions\Implementations\Async\QueuedActionJob;
use MichielKempen\LaravelActions\Implementations\Async\QueueableActionProxy;
use MichielKempen\LaravelActions\Tests\TestCase\Mocks\MockedQueueableActionProxy;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use MichielKempen\LaravelActions\Tests\TestCase\TestModel;

class QueueableActionTest extends TestCase
{
    /** @test */
    public function a_queued_action_job_can_be_retrieved()
    {
        $queuedActionJob = SimpleAction::job();

        $this->assertInstanceOf(QueuedActionJob::class, $queuedActionJob);
        $this->assertEquals(SimpleAction::class, $queuedActionJob->displayName());
    }

    /** @test */
    public function a_queued_action_proxy_can_be_retrieved_without_specifying_a_model()
    {
        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        $this->app->bind(QueueableActionProxy::class, MockedQueueableActionProxy::class);

        /** @var MockedQueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = $action->queue();

        $this->assertInstanceOf(MockedQueueableActionProxy::class, $queuedActionProxy);
        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNull($queuedActionProxy->getModel());
    }

    /** @test */
    public function a_queued_action_proxy_can_be_retrieved_with_specifying_a_model()
    {
        $model = TestModel::create();

        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        $this->app->bind(QueueableActionProxy::class, MockedQueueableActionProxy::class);

        /** @var MockedQueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = $action->queue($model);

        $this->assertInstanceOf(MockedQueueableActionProxy::class, $queuedActionProxy);
        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertEquals($model, $queuedActionProxy->getModel());
    }
}