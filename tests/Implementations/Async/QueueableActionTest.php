<?php

namespace MichielKempen\LaravelActions\Tests\Implementations\Async;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChainReport;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Implementations\Async\QueuedActionJob;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\SkipAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Tests\TestCase\Callbacks\LogCallback;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use MichielKempen\LaravelActions\Tests\TestCase\TestModel;

class QueueableActionTest extends TestCase
{
    /** @test */
    public function it_can_chain_multiple_instances_of_the_same_action()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $queuedActionChainId = (new ReturnTheParametersAsOutputAction)
            ->queue()
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute($parameterA, $parameterB);

        $actionChain = QueuedActionChain::findOrFail($queuedActionChainId);

        $this->assertInstanceOf(QueuedActionChain::class, $actionChain);
        $this->assertInstanceOf(Collection::class, $actionChain->getActions());
        $this->assertEquals(3, $actionChain->getNumberOfActions());

        $action = $actionChain->getNthAction(1);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(2);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(3);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());
    }

    /** @test */
    public function it_pushes_the_actions_on_the_queue()
    {
        Queue::fake();

        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new ReturnTheParametersAsOutputAction)
            ->queue()
            ->chain(SkipAction::class)
            ->chain(ThrowAnExceptionAction::class)
            ->chain(SkipAction::class)
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute($parameterA, $parameterB);

        Queue::assertPushedWithChain(QueuedActionJob::class, [
            QueuedActionJob::class,
            QueuedActionJob::class,
            QueuedActionJob::class,
            QueuedActionJob::class,
        ]);
    }

    /** @test */
    public function it_skips_actions_of_which_the_skip_condition_is_fulfilled()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $queuedActionChainId = (new ReturnTheParametersAsOutputAction)
            ->queue()
            ->chain(SkipAction::class)
            ->chain(ThrowAnExceptionAction::class)
            ->chain(SkipAction::class)
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute($parameterA, $parameterB);

        $actionChain = QueuedActionChain::findOrFail($queuedActionChainId);

        $this->assertInstanceOf(QueuedActionChain::class, $actionChain);
        $this->assertInstanceOf(Collection::class, $actionChain->getActions());
        $this->assertEquals(5, $actionChain->getNumberOfActions());

        $action = $actionChain->getNthAction(1);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(2);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(SkipAction::class, $action->getClass());
        $this->assertEquals("skip", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals("not skipped at all", $action->getOutput());

        $action = $actionChain->getNthAction(3);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ThrowAnExceptionAction::class, $action->getClass());
        $this->assertEquals("throw an exception", $action->getName());
        $this->assertEquals(ActionStatus::FAILED, $action->getStatus());
        $this->assertEquals("Let's break all the things!", $action->getOutput());

        $action = $actionChain->getNthAction(4);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(SkipAction::class, $action->getClass());
        $this->assertEquals("skip", $action->getName());
        $this->assertEquals(ActionStatus::SKIPPED, $action->getStatus());
        $this->assertEquals(null, $action->getOutput());

        $action = $actionChain->getNthAction(5);
        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());
    }

    /** @test */
    public function it_triggers_the_callbacks_after_every_action_in_a_chain()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new ReturnTheParametersAsOutputAction)
            ->queue()
            ->chain(SkipAction::class)
            ->chain(ThrowAnExceptionAction::class)
            ->chain(SkipAction::class)
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->withCallback(LogCallback::class)
            ->execute($parameterA, $parameterB);

        $actionClass = ReturnTheParametersAsOutputAction::class;
        $status = ActionStatus::SUCCEEDED;
        $this->assertLogHas("{$actionClass} - $status");

        $actionClass = SkipAction::class;
        $status = ActionStatus::SUCCEEDED;
        $this->assertLogHas("{$actionClass} - $status");

        $actionClass = ThrowAnExceptionAction::class;
        $status = ActionStatus::FAILED;
        $this->assertLogHas("{$actionClass} - $status");

        $actionClass = SkipAction::class;
        $status = ActionStatus::SKIPPED;
        $this->assertLogHas("{$actionClass} - $status");

        $actionClass = ReturnTheParametersAsOutputAction::class;
        $status = ActionStatus::SUCCEEDED;
        $this->assertLogHas("{$actionClass} - $status");
    }

    /** @test */
    public function it_can_queue_a_single_action()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $testModel = TestModel::create();

        (new ReturnTheParametersAsOutputAction)
            ->queue()
            ->onModel($testModel)
            ->withCallback(LogCallback::class)
            ->execute($parameterA, $parameterB);

        $actionClass = ReturnTheParametersAsOutputAction::class;
        $status = ActionStatus::SUCCEEDED;
        $this->assertLogHas("{$actionClass} - $status");
    }
}