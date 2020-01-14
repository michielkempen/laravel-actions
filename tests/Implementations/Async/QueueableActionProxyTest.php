<?php

namespace MichielKempen\LaravelActions\Tests\Implementations\Async;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Implementations\Async\QueueableActionProxy;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\SkipAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ThrowAnExceptionAction;
use MichielKempen\LaravelActions\Tests\TestCase\Callbacks\ReturnStatusCallback;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use MichielKempen\LaravelActions\Tests\TestCase\TestModel;

class QueueableActionProxyTest extends TestCase
{
    /** @test */
    public function it_initializes_the_action_proxy_attributes_correctly()
    {
        $action = new ReturnTheParametersAsOutputAction;

        $proxy = new QueueableActionProxy($action);

        $this->assertEquals($action, $proxy->getActionInstance());
        $this->assertNull($proxy->getModelType());
        $this->assertNull($proxy->getModelId());
        $this->assertInstanceOf(Collection::class, $proxy->getCallbacks());
        $this->assertEquals(0, $proxy->getCallbacks()->count());
        $this->assertInstanceOf(Collection::class, $proxy->getChainedActions());
        $this->assertEquals(0, $proxy->getChainedActions()->count());
    }

    /** @test */
    public function it_can_change_and_retrieve_the_action_proxy_attributes_correctly()
    {
        $actionA = new ReturnTheParametersAsOutputAction;
        $testModel = TestModel::create();

        $proxy = (new QueueableActionProxy($actionA))
            ->onModel($testModel)
            ->chain(SkipAction::class)
            ->chain(ThrowAnExceptionAction::class)
            ->withCallback(ReturnStatusCallback::class);

        $this->assertEquals($actionA, $proxy->getActionInstance());
        $this->assertEquals('TestModel', $proxy->getModelType());
        $this->assertEquals($testModel->id, $proxy->getModelId());
        $this->assertEquals(SkipAction::class, $proxy->getChainedActions()->get(0)->getClass());
        $this->assertEquals(ThrowAnExceptionAction::class, $proxy->getChainedActions()->get(1)->getClass());
        $callbacks = $proxy->getCallbacks();
        $this->assertEquals(1, $callbacks->count());
    }
}