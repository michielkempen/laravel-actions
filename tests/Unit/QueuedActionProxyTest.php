<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Unit;

use MichielKempen\LaravelQueueableActions\Database\QueuedAction;
use MichielKempen\LaravelQueueableActions\Database\QueuedActionRepository;
use MichielKempen\LaravelQueueableActions\QueuedActionProxy;
use MichielKempen\LaravelQueueableActions\Tests\Support\ComplexAction;
use MichielKempen\LaravelQueueableActions\Tests\Support\SimpleAction;
use MichielKempen\LaravelQueueableActions\Tests\Support\TestModel;
use MichielKempen\LaravelQueueableActions\Tests\TestCase;
use Mockery\MockInterface;

class QueuedActionProxyTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_when_specifying_no_model()
    {
        /** @var SimpleAction $action */
        $action = app(SimpleAction::class);

        /** @var QueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueuedActionProxy::class, [
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

        /** @var QueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueuedActionProxy::class, [
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
                ->andReturn(QueuedAction::create([
                    'model_id' => $model->id,
                    'model_type' => 'TestModel',
                    'name' => 'simple',
                    'status' => 'pending',
                    'output' => null,
                ]));
        });

        /** @var QueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueuedActionProxy::class, [
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
                ->andReturn(QueuedAction::create([
                    'model_id' => $model->id,
                    'model_type' => 'TestModel',
                    'name' => 'custom action name',
                    'status' => 'pending',
                    'output' => null,
                ]));
        });

        /** @var QueuedActionProxy $queuedActionProxy */
        $queuedActionProxy = app()->makeWith(QueuedActionProxy::class, [
            'action' => $action,
            'model' => $model,
        ]);

        $this->assertEquals($action, $queuedActionProxy->getAction());
        $this->assertNotNull($queuedActionProxy->getQueuedActionId());
    }
}