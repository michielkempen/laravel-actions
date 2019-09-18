<?php

namespace MichielKempen\LaravelActions\Tests\Unit;

use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use MichielKempen\LaravelActions\Implementations\Async\QueueableActionProxy;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ComplexAction;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use MichielKempen\LaravelActions\Tests\TestCase\TestModel;
use Mockery\MockInterface;

class QueuedActionProxyTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_when_specifying_no_model()
    {
        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        /** @var QueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueueableActionProxy::class, [
            'action' => $action,
        ]);

        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNull($queuedActionProxy->getQueuedActionId());
    }

    /** @test */
    public function it_can_be_instantiated_when_specifying_a_null_model()
    {
        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        /** @var QueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueueableActionProxy::class, [
            'action' => $action,
            'model' => null,
        ]);

        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNull($queuedActionProxy->getQueuedActionId());
    }

    /** @test */
    public function it_can_be_instantiated_when_specifying_a_model()
    {
        $model = TestModel::create();

        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        $this->mock(QueuedActionRepository::class, function(MockInterface $mock) use ($model) {
            $mock
                ->shouldReceive('createQueuedAction')
                ->once()
                ->with('TestModel', $model->id, 'simple', 'pending')
                ->andReturn(factory(QueuedAction::class)->create());
        });

        /** @var QueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueueableActionProxy::class, [
            'action' => $action,
            'model' => $model,
        ]);

        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNotNull($queuedActionProxy->getQueuedActionId());
    }

    /** @test */
    public function it_can_be_instantiated_when_specifying_a_model_and_an_action_with_a_custom_name()
    {
        $model = TestModel::create();

        /** @var SimpleAction $action */
        $action = app(ComplexAction::class);

        $this->mock(QueuedActionRepository::class, function(MockInterface $mock) use ($model) {
            $mock
                ->shouldReceive('createQueuedAction')
                ->once()
                ->with('TestModel', $model->id, 'custom action name', 'pending')
                ->andReturn(factory(QueuedAction::class)->create());
        });

        /** @var QueueableActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueueableActionProxy::class, [
            'action' => $action,
            'model' => $model,
        ]);

        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNotNull($queuedActionProxy->getQueuedActionId());
    }
}