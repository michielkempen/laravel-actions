<?php

namespace MichielKempen\LaravelActions\Tests\Implementations\Async;

use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\Implementations\Async\QueueableActionProxy;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\SkipAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use MichielKempen\LaravelActions\Tests\TestCase\TestModel;

class QueueableActionProxyTest extends TestCase
{
    /** @test */
    public function it_initializes_the_action_proxy_attributes_correctly()
    {
        $action = new ReturnTheParametersAsOutputAction;

        $proxy = new QueueableActionProxy($action);

        $this->assertEquals($action, $proxy->getAction());
        $this->assertNull($proxy->getModelType());
        $this->assertNull($proxy->getModelId());
        $this->assertEquals([], $proxy->getCallbacks());
        $this->assertEquals([], $proxy->getChainedActions());
    }

    /** @test */
    public function it_can_change_and_retrieve_the_action_proxy_attributes_correctly()
    {
        $actionA = new ReturnTheParametersAsOutputAction;
        $testModel = TestModel::create();
        $callbackA = function(Action $action) {
            return $action->getStatus();
        };
        $callbackB = function(Action $action) {
            return $action->getName();
        };

        $proxy = (new QueueableActionProxy($actionA))
            ->onModel($testModel)
            ->withCallback($callbackA)
            ->withCallback($callbackB)
            ->chain([
                SkipAction::class,
            ])
            ->chain([
                ThrowAnExceptionAction::class,
            ]);

        $this->assertEquals($actionA, $proxy->getAction());
        $this->assertEquals('TestModel', $proxy->getModelType());
        $this->assertEquals($testModel->id, $proxy->getModelId());
        $this->assertEquals([SkipAction::class, ThrowAnExceptionAction::class], $proxy->getChainedActions());
        $callbacks = $proxy->getCallbacks();
        $this->assertEquals(2, count($callbacks));
        $action = Action::createFromAction($actionA, ['hello', 'world']);
        $this->assertEquals($callbackA($action), $callbacks[0]($action));
        $this->assertEquals($callbackB($action), $callbacks[1]($action));
    }
}