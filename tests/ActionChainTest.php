<?php

namespace MichielKempen\LaravelActions\Tests;

use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChain;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\SkipAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class ActionChainTest extends TestCase
{
    /** @test */
    public function it_can_be_serialized()
    {
        $report = new ActionChain;

        $report->addAction(new Action(
            ReturnTheParametersAsOutputAction::class,
            [],
            'return the parameters as output',
            ActionStatus::SUCCEEDED,
            'string output'
        ));

        $report->addAction(new Action(
            ThrowAnExceptionAction::class,
            [],
            'throw an exception',
            ActionStatus::PENDING,
            ['array output']
        ));

        $report->addAction(new Action(
            SkipAction::class,
            ['parameterA', ['parameterB' => ['parameterC']]],
            'skip',
            ActionStatus::SKIPPED,
            null
        ));

        $serialization = $report->toArray();

        $this->assertEquals([
            'success' => false,
            'actions' => [
                [
                    'action_class' => ReturnTheParametersAsOutputAction::class,
                    'parameters' => [],
                    'name' => 'return the parameters as output',
                    'status' => ActionStatus::SUCCEEDED,
                    'output' => 'string output',
                ],
                [
                    'action_class' => ThrowAnExceptionAction::class,
                    'parameters' => [],
                    'name' => 'throw an exception',
                    'status' => ActionStatus::PENDING,
                    'output' => [
                        'array output',
                    ],
                ],
                [
                    'action_class' => SkipAction::class,
                    'parameters' => ['parameterA', ['parameterB' => ['parameterC']]],
                    'name' => 'skip',
                    'status' => ActionStatus::SKIPPED,
                    'output' => null,
                ],
            ],
        ], $serialization);
    }

    /** @test */
    public function it_can_be_deserialized()
    {
        $serialization = [
            'success' => false,
            'actions' => [
                [
                    'action' => ReturnTheParametersAsOutputAction::class,
                    'name' => 'return the parameters as output',
                    'status' => ActionStatus::SUCCESS,
                    'output' => 'string output',
                ],
                [
                    'action' => ThrowAnExceptionAction::class,
                    'name' => 'throw an exception',
                    'status' => ActionStatus::PENDING,
                    'output' => [
                        'array output',
                    ],
                ],
                [
                    'action' => SkipAction::class,
                    'name' => 'skip',
                    'status' => ActionStatus::SKIPPED,
                    'output' => null,
                ],
            ],
        ];

        $report = ActionChain::deserialize($serialization);

        $this->assertInstanceOf(ActionChain::class, $report);
        $this->assertEquals(3, $report->getNumberOfActions());

        $action = $report->getNthAction(1);
        $this->assertEquals(ReturnTheParametersAsOutputAction::class, $action->getAction());
        $this->assertEquals('return the parameters as output', $action->getName());
        $this->assertEquals(ActionStatus::SUCCESS, $action->getStatus());
        $this->assertEquals('string output', $action->getOutput());

        $action = $report->getNthAction(2);
        $this->assertEquals(ThrowAnExceptionAction::class, $action->getAction());
        $this->assertEquals('throw an exception', $action->getName());
        $this->assertEquals(ActionStatus::PENDING, $action->getStatus());
        $this->assertEquals(['array output'], $action->getOutput());

        $action = $report->getNthAction(3);
        $this->assertEquals(SkipAction::class, $action->getAction());
        $this->assertEquals('skip', $action->getName());
        $this->assertEquals(ActionStatus::SKIPPED, $action->getStatus());
        $this->assertNull($action->getOutput());
    }
}