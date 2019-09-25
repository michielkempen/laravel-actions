<?php

namespace MichielKempen\LaravelActions\Tests\Database;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\SimpleAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use Opis\Closure\SerializableClosure;

class QueuedActionRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_an_existing_queued_action()
    {
        $queuedAction = factory(QueuedAction::class)->create();

        $repository = new QueuedActionRepository;
        $result = $repository->getQueuedActionOrFail($queuedAction->getId());

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals($queuedAction->getId(), $result->getId());
    }

    /** @test */
    public function it_throws_an_exception_when_retrieving_a_non_existing_queued_action()
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new QueuedActionRepository;
        $repository->getQueuedActionOrFail($this->faker->uuid);
    }

    /** @test */
    public function it_can_create_a_queued_action()
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
        
        $repository = new QueuedActionRepository;
        $result = $repository->createQueuedAction($queuedActionChain->id, 2, $action, $callbacks);

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals($queuedActionChain->id, $result->getChainId());
        $chain = $result->getChain();
        $this->assertInstanceOf(QueuedActionChain::class, $chain);
        $this->assertEquals($queuedActionChain->getId(), $chain->getId());
        $this->assertEquals(2, $result->getOrder());
        $this->assertEquals(ActionStatus::PENDING, $result->getStatus());
        $this->assertEquals(count($callbacks), count($result->getCallbacks()));
        $this->assertEquals($callbacks[0]($action), $result->getCallbacks()[0]($action));
        $this->assertEquals('pending', $callbacks[0]($action));
        $action = $result->getAction();
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getActionClass());
        $this->assertEquals([5, 'parameter'], $action->getParameters());
        $this->assertEquals('send email', $action->getName());
        $this->assertEquals(ActionStatus::PENDING, $action->getStatus());
        $this->assertEquals('output', $action->getOutput());
        $this->assertEquals($startedAt->toIso8601String(), $action->getStartedAt()->toIso8601String());
        $this->assertEquals($finishedAt->toIso8601String(), $action->getFinishedAt()->toIso8601String());
        $this->assertNotNull($action->getDuration());
    }

    /** @test */
    public function it_can_update_an_existing_queued_action()
    {
        $oldAction = new Action(
            ReturnTheParametersAsOutputAction::class, [5, 'parameter'], 'send email', ActionStatus::PENDING
        );

        $queuedAction = factory(QueuedAction::class)->create([
            'action' => $oldAction->toArray(),
        ]);

        $startedAt = Carbon::now()->subMinutes(5);
        $finishedAt = Carbon::now()->subMinutes(3);
        $updatedAction = new Action(
            ReturnTheParametersAsOutputAction::class, [5, 'parameter'], 'send email', ActionStatus::SUCCEEDED, 'output',
            $startedAt, $finishedAt
        );

        $repository = new QueuedActionRepository;
        $result = $repository->updateQueuedAction($queuedAction->getId(), $updatedAction);

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals(ActionStatus::SUCCEEDED, $result->getStatus());
        $updatedAction = $result->getAction();
        $this->assertInstanceOf(Action::class, $updatedAction);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $updatedAction->getActionClass());
        $this->assertEquals([5, 'parameter'], $updatedAction->getParameters());
        $this->assertEquals('send email', $updatedAction->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $updatedAction->getStatus());
        $this->assertEquals('output', $updatedAction->getOutput());
        $this->assertEquals($startedAt->toIso8601String(), $updatedAction->getStartedAt()->toIso8601String());
        $this->assertEquals($finishedAt->toIso8601String(), $updatedAction->getFinishedAt()->toIso8601String());
        $this->assertNotNull($updatedAction->getDuration());
    }

    /** @test */
    public function it_throws_an_exception_when_updating_a_non_existing_queued_action()
    {
        $this->expectException(ModelNotFoundException::class);

        $startedAt = Carbon::now()->subMinutes(5);
        $finishedAt = Carbon::now()->subMinutes(3);
        $action = new Action(
            ReturnTheParametersAsOutputAction::class, [5, 'parameter'], 'send email', ActionStatus::PENDING, 'output',
            $startedAt, $finishedAt
        );

        $repository = new QueuedActionRepository;
        $repository->updateQueuedAction($this->faker->uuid, $action);
    }
}