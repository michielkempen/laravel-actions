<?php

namespace MichielKempen\LaravelActions\Tests\Database;

use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use Opis\Closure\SerializableClosure;

class QueuedActionTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_attributes_from_the_database()
    {
        $queuedActionChain = factory(QueuedActionChain::class)->create();
        $modelId = $this->faker->uuid;
        $startedAt = Carbon::now()->subMinutes(5);
        $finishedAt = Carbon::now()->subMinutes(3);
        $action = new Action(
            ReturnTheParametersAsOutputAction::class, [5, 'parameter'], 'send email', ActionStatus::PENDING, 'output',
            $startedAt, $finishedAt
        );
        $callbacks = [
            new SerializableClosure(function(Action $action) {
                return $action->getStatus();
            })
        ];

        /** @var QueuedAction $queuedActionB */
        $queuedActionB = factory(QueuedAction::class)->create([
            'chain_id' => $queuedActionChain->id,
            'order' => 2,
            'status' => ActionStatus::PENDING,
            'action' => $action->toArray(),
            'callbacks' => $callbacks,
        ]);

        $this->assertEquals($queuedActionChain->id, $queuedActionB->getChainId());
        $chain = $queuedActionB->getChain();
        $this->assertInstanceOf(QueuedActionChain::class, $chain);
        $this->assertEquals($queuedActionChain->getId(), $chain->getId());
        $this->assertEquals(2, $queuedActionB->getOrder());
        $this->assertEquals(ActionStatus::PENDING, $queuedActionB->getStatus());
        $this->assertEquals(count($callbacks), $queuedActionB->getCallbacks()->count());
        $this->assertEquals($callbacks[0]($action), $queuedActionB->getCallbacks()->first()($action));
        $this->assertEquals('pending', $callbacks[0]($action));
        $action = $queuedActionB->getAction();
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals([5, 'parameter'], $action->getArguments());
        $this->assertEquals('send email', $action->getName());
        $this->assertEquals(ActionStatus::PENDING, $action->getStatus());
        $this->assertEquals('output', $action->getOutput());
        $this->assertEquals($startedAt->toIso8601String(), $action->getStartedAt()->toIso8601String());
        $this->assertEquals($finishedAt->toIso8601String(), $action->getFinishedAt()->toIso8601String());
        $this->assertNotNull($action->getDuration());
    }
}