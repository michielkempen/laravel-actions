<?php

namespace MichielKempen\LaravelActions\Resources\Action;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainFactory;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Testing\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Testing\TestCase;

class QueuedActionRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_a_queued_action()
    {
        $startedAt = Carbon::now()->subMinutes(5);
        $finishedAt = Carbon::now()->subMinutes(3);

        $queuedAction = QueuedActionFactory::new()
            ->order(2)
            ->class(ReturnTheParametersAsOutputAction::class)
            ->arguments([5, 'parameter'])
            ->name('send email')
            ->status(ActionStatus::SUCCEEDED)
            ->output('email sent')
            ->started_at($startedAt)
            ->finished_at($finishedAt)
            ->create();

        $result = (new QueuedActionRepository)->getQueuedActionOrFail($queuedAction->getId());

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertInstanceOf(QueuedActionChain::class, $result->getChain());
        $this->assertEquals(2, $result->getOrder());

        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $result->getClass());
        $this->assertInstanceOf(ReturnTheParametersAsOutputAction::class, $result->instantiate());
        $this->assertEquals([5, 'parameter'], $result->getArguments());
        $this->assertEquals('send email', $result->getName());

        $this->assertEquals(ActionStatus::SUCCEEDED, $result->getStatus());
        $this->assertEquals('email sent', $result->getOutput());

        $this->assertEquals($startedAt->toIso8601String(), $result->getStartedAt()->toIso8601String());
        $this->assertEquals($finishedAt->toIso8601String(), $result->getFinishedAt()->toIso8601String());
        $this->assertNotNull($result->getDuration());
    }

    /** @test */
    public function it_throws_an_exception_when_retrieving_a_non_existing_queued_action()
    {
        $this->expectException(ModelNotFoundException::class);

        (new QueuedActionRepository)->getQueuedActionOrFail($this->faker->uuid);
    }

    /** @test */
    public function it_can_create_a_queued_action()
    {
        $queuedActionChain = QueuedActionChainFactory::new()->create();
        $action = new Action(new ReturnTheParametersAsOutputAction, [5, 'parameter'], 'send email');

        $result = (new QueuedActionRepository)->createQueuedAction($queuedActionChain->id, 2, $action);

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals($queuedActionChain->id, $result->getChainId());
        $this->assertInstanceOf(QueuedActionChain::class, $result->getChain());
        $this->assertEquals(2, $result->getOrder());

        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $result->getClass());
        $this->assertInstanceOf(ReturnTheParametersAsOutputAction::class, $result->instantiate());
        $this->assertEquals([5, 'parameter'], $result->getArguments());
        $this->assertEquals('send email', $result->getName());

        $this->assertEquals(ActionStatus::PENDING, $result->getStatus());
        $this->assertNull($result->getOutput());

        $this->assertNull($result->getStartedAt());
        $this->assertNull($result->getFinishedAt());
        $this->assertNull($result->getDuration());
    }
}
