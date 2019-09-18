<?php

namespace MichielKempen\LaravelActions\Tests\Database;

use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use Opis\Closure\SerializableClosure;

class QueuedActionTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_attributes_from_the_database()
    {
        $action = new Action(SimpleAction::class, [], 'send email', ActionStatus::PENDING);

        /** @var QueuedAction $queuedActionA */
        $queuedActionA = factory(QueuedAction::class)->create([
            'chain_id' => null,
            'order' => null,
            'model_id' => null,
            'model_type' => null,
            'status' => ActionStatus::PENDING,
            'action' => $action->toArray(),
            'callbacks' => [],
        ]);

        $this->assertFalse($queuedActionA->hasChain());
        $this->assertNull($queuedActionA->getChainId());
        $this->assertNull($queuedActionA->getChain());
        $this->assertNull($queuedActionA->getOrder());
        $this->assertNull($queuedActionA->getModelId());
        $this->assertNull($queuedActionA->getModelType());
        $this->assertEquals(ActionStatus::PENDING, $queuedActionA->getStatus());
        $this->assertEquals([], $queuedActionA->getCallbacks());
        $action = $queuedActionA->getAction();
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(SimpleAction::class, $action->getActionClass());
        $this->assertEquals([], $action->getParameters());
        $this->assertEquals('send email', $action->getName());
        $this->assertEquals(ActionStatus::PENDING, $action->getStatus());
        $this->assertNull($action->getOutput());
        $this->assertNull($action->getStartedAt());
        $this->assertNull($action->getFinishedAt());
        $this->assertNull($action->getDuration());

        $queuedActionChain = factory(QueuedActionChain::class)->create();
        $modelId = $this->faker->uuid;
        $startedAt = Carbon::now()->subMinutes(5);
        $finishedAt = Carbon::now()->subMinutes(3);
        $action = new Action(SimpleAction::class, [5, 'parameter'], 'send email', ActionStatus::PENDING, 'output', $startedAt, $finishedAt);
        $callbacks = [
            new SerializableClosure(function(Action $action) {
                return $action->getStatus();
            })
        ];

        /** @var QueuedAction $queuedActionB */
        $queuedActionB = factory(QueuedAction::class)->create([
            'chain_id' => $queuedActionChain->id,
            'order' => 2,
            'model_id' => $modelId,
            'model_type' => 'TestModel',
            'status' => ActionStatus::PENDING,
            'action' => $action->toArray(),
            'callbacks' => $callbacks,
        ]);

        $this->assertTrue($queuedActionB->hasChain());
        $this->assertEquals($queuedActionChain->id, $queuedActionB->getChainId());
        $chain = $queuedActionB->getChain();
        $this->assertInstanceOf(QueuedActionChain::class, $chain);
        $this->assertEquals($queuedActionChain->getId(), $chain->getId());
        $this->assertEquals(2, $queuedActionB->getOrder());
        $this->assertEquals($modelId, $queuedActionB->getModelId());
        $this->assertEquals('TestModel', $queuedActionB->getModelType());
        $this->assertEquals(ActionStatus::PENDING, $queuedActionB->getStatus());
        $this->assertEquals(count($callbacks), count($queuedActionB->getCallbacks()));
        $this->assertEquals($callbacks[0]($action), $queuedActionB->getCallbacks()[0]($action));
        $this->assertEquals('pending', $callbacks[0]($action));
        $action = $queuedActionB->getAction();
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(SimpleAction::class, $action->getActionClass());
        $this->assertEquals([5, 'parameter'], $action->getParameters());
        $this->assertEquals('send email', $action->getName());
        $this->assertEquals(ActionStatus::PENDING, $action->getStatus());
        $this->assertEquals('output', $action->getOutput());
        $this->assertEquals($startedAt->toIso8601String(), $action->getStartedAt()->toIso8601String());
        $this->assertEquals($finishedAt->toIso8601String(), $action->getFinishedAt()->toIso8601String());
        $this->assertNotNull($action->getDuration());
    }
}