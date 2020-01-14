<?php

namespace MichielKempen\LaravelActions\Tests\Implementations\Sync;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChain;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\SkipAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Tests\TestCase\Callbacks\LogCallback;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class ChainableActionTest extends TestCase
{
    /** @test */
    public function it_can_chain_multiple_instances_of_the_same_action()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        $actionChain = (new ReturnTheParametersAsOutputAction)
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->execute($parameterA, $parameterB);

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
    public function it_skips_actions_of_which_the_skip_condition_is_fulfilled()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;
        $parameterC = $this->faker->uuid;

        $actionChain = (new ReturnTheParametersAsOutputAction)
            ->chain(SkipAction::class)
            ->chain(ThrowAnExceptionAction::class)
            ->chain(SkipAction::class)
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterA', 'parameterB'))
            ->chain(ReturnTheParametersAsOutputAction::class, compact('parameterB', 'parameterC'))
            ->execute($parameterA, $parameterB);

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
    public function it_triggers_the_callbacks_after_every_action()
    {
        $parameterA = $this->faker->uuid;
        $parameterB = $this->faker->uuid;

        (new ReturnTheParametersAsOutputAction)
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
}