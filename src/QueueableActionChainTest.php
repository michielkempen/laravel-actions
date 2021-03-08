<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Resources\ActionOutput;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Resources\QueuedActionJob;
use MichielKempen\LaravelActions\Testing\Actions\ReturnTheFirstParameterAsOutputAction;
use MichielKempen\LaravelActions\Testing\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Testing\Actions\SkipAction;
use MichielKempen\LaravelActions\Testing\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Testing\Callbacks\LogCallback;
use MichielKempen\LaravelActions\Testing\TestCase;

class QueueableActionChainTest extends TestCase
{
    /** @test */
    public function it_pushes_the_actions_on_the_queue_when_running_asynchronously()
    {
        Queue::fake();

        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new QueueableActionChain)
            ->queue()
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(SkipAction::class)
            ->addAction(ThrowAnExceptionAction::class)
            ->addAction(SkipAction::class)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute();

        Queue::assertPushedWithChain(QueuedActionJob::class, [
            QueuedActionJob::class,
            QueuedActionJob::class,
            QueuedActionJob::class,
            QueuedActionJob::class,
        ]);
    }

    /** @test */
    public function it_can_execute_multiple_identical_actions_when_running_synchronously(): void
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $actionChain = (new QueueableActionChain)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute();

        $this->assertInstanceOf(ActionChain::class, $actionChain);
        $this->assertInstanceOf(Collection::class, $actionChain->getActions());
        $this->assertEquals(3, $actionChain->getNumberOfActions());

        $action = $actionChain->getNthAction(1);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(2);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(3);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());
    }

    /** @test */
    public function it_can_execute_multiple_identical_actions_when_running_asynchronously()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $queuedActionChainId = (new QueueableActionChain)
            ->queue()
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute();

        $actionChain = QueuedActionChainRepository::new()->getQueuedActionChain($queuedActionChainId);

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
    public function it_can_skip_actions_when_running_synchronously(): void
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;
        $parameterC = $this->faker->uuid;

        $actionChain = (new QueueableActionChain)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(SkipAction::class, [])
            ->addAction(ThrowAnExceptionAction::class, [])
            ->addAction(SkipAction::class, [])
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterB', 'parameterC'))
            ->execute();

        $this->assertInstanceOf(ActionChain::class, $actionChain);
        $this->assertInstanceOf(Collection::class, $actionChain->getActions());
        $this->assertEquals(6, $actionChain->getNumberOfActions());

        $action = $actionChain->getNthAction(1);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(2);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(SkipAction::class, $action->getClass());
        $this->assertEquals("skip", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals("not skipped at all", $action->getOutput());

        $action = $actionChain->getNthAction(3);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ThrowAnExceptionAction::class, $action->getClass());
        $this->assertEquals("throw an exception", $action->getName());
        $this->assertEquals(ActionStatus::FAILED, $action->getStatus());
        $this->assertEquals("Let's break all the things!", $action->getOutput());

        $action = $actionChain->getNthAction(4);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(SkipAction::class, $action->getClass());
        $this->assertEquals("skip", $action->getName());
        $this->assertEquals(ActionStatus::SKIPPED, $action->getStatus());
        $this->assertEquals(null, $action->getOutput());

        $action = $actionChain->getNthAction(5);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterA, $parameterB], $action->getOutput());

        $action = $actionChain->getNthAction(6);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getClass());
        $this->assertEquals("return the parameters as output", $action->getName());
        $this->assertEquals(ActionStatus::SUCCEEDED, $action->getStatus());
        $this->assertEquals([$parameterB, $parameterC], $action->getOutput());
    }

    /** @test */
    public function it_can_skip_actions_when_running_asynchronously()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $queuedActionChainId = (new QueueableActionChain)
            ->queue()
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(SkipAction::class)
            ->addAction(ThrowAnExceptionAction::class)
            ->addAction(SkipAction::class)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute();

        $actionChain = QueuedActionChainRepository::new()->getQueuedActionChain($queuedActionChainId);

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
    public function it_can_trigger_callbacks_when_running_synchronously(): void
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new QueueableActionChain)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(SkipAction::class, [])
            ->addAction(ThrowAnExceptionAction::class, [])
            ->addAction(SkipAction::class, [])
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->withCallback(LogCallback::class)
            ->execute();

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
    public function it_can_trigger_callbacks_when_running_asynchronously()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new QueueableActionChain)
            ->queue()
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->addAction(SkipAction::class)
            ->addAction(ThrowAnExceptionAction::class)
            ->addAction(SkipAction::class)
            ->addAction(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->withCallback(LogCallback::class)
            ->execute();

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
    public function it_can_use_the_output_of_an_action_as_argument_for_another_action_when_running_asynchronously(): void
    {
        $uuid = Str::uuid();
        $argument = new ActionOutput($uuid);

        $queuedActionChainId = (new QueueableActionChain)
            ->queue()
            ->withName("Test action")
            ->addAction(ReturnTheFirstParameterAsOutputAction::class, ['hello', 'world'], "Greetings!", $uuid)
            ->addAction(ReturnTheParametersAsOutputAction::class, ['john', 'doe'])
            ->addAction(ReturnTheParametersAsOutputAction::class, [$argument, 'joe'], "Test action output")
            ->withCallback(LogCallback::class)
            ->execute();

        $actions = QueuedActionChainRepository::new()
            ->getQueuedActionChain($queuedActionChainId)
            ->getActions()
            ->map(function(QueuedAction $queuedAction) {
                return [
                    'order' => $queuedAction->getOrder(),
                    'name' => $queuedAction->getName(),
                    'arguments' => $queuedAction->getArguments(),
                    'status' => $queuedAction->getStatus(),
                    'output' => $queuedAction->getOutput(),
                ];
            });

        $this->assertEquals([
            [
                "order" => 0,
                "name" => "Greetings!",
                "arguments" => ["hello", "world"],
                "status" => "succeeded",
                "output" => "hello",
            ],
            [
                "order" => 1,
                "name" => "return the parameters as output",
                "arguments" => ["john", "doe"],
                "status" => "succeeded",
                "output" => ["john", "doe"],
            ],
            [
                "order" => 2,
                "name" => "Test action output",
                "arguments" => [$argument, "joe"],
                "status" => "succeeded",
                "output" => ["hello", "joe"]
            ]
        ], $actions->all());
    }
}
