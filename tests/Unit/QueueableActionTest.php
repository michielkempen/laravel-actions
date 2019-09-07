<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Unit;

use MichielKempen\LaravelQueueableActions\QueuedActionJob;
use MichielKempen\LaravelQueueableActions\QueuedActionProxy;
use MichielKempen\LaravelQueueableActions\Tests\Support\Mocks\MockedQueuedActionProxy;
use MichielKempen\LaravelQueueableActions\Tests\Support\SimpleAction;
use MichielKempen\LaravelQueueableActions\Tests\Support\TestModel;
use MichielKempen\LaravelQueueableActions\Tests\TestCase;

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

        $this->app->bind(QueuedActionProxy::class, MockedQueuedActionProxy::class);

        /** @var MockedQueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = $action->onQueue();

        $this->assertInstanceOf(MockedQueuedActionProxy::class, $queuedActionProxy);
        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNull($queuedActionProxy->getModel());
    }

    /** @test */
    public function a_queued_action_proxy_can_be_retrieved_with_specifying_a_model()
    {
        $model = TestModel::create();

        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        $this->app->bind(QueuedActionProxy::class, MockedQueuedActionProxy::class);

        /** @var MockedQueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = $action->onQueue($model);

        $this->assertInstanceOf(MockedQueuedActionProxy::class, $queuedActionProxy);
        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertEquals($model, $queuedActionProxy->getModel());
    }
}