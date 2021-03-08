<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\Facades\Queue;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Resources\QueuedActionJob;
use MichielKempen\LaravelActions\Testing\Actions\ComplexAction;
use MichielKempen\LaravelActions\Testing\Actions\DataObject;
use MichielKempen\LaravelActions\Testing\Actions\DumpTheFirstParameterAction;
use MichielKempen\LaravelActions\Testing\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Testing\Callbacks\LogCallback;
use MichielKempen\LaravelActions\Testing\PendingModel;
use MichielKempen\LaravelActions\Testing\TestCase;
use MichielKempen\LaravelActions\Testing\TestModel;

class QueueableActionTest extends TestCase
{
    /** @test */
    public function it_can_execute_an_action_when_running_synchronously()
    {
        Queue::fake();

        (new ComplexAction)->execute(new DataObject('foo'));

        Queue::assertNotPushed(QueuedActionJob::class);
        $this->assertLogHas('foo bar');
    }

    /** @test */
    public function it_pushes_the_action_on_the_queue_when_running_asynchronously()
    {
        Queue::fake();

        (new ComplexAction)->queue()->execute(new DataObject('foo'));

        Queue::assertPushed(QueuedActionJob::class);
    }

    /** @test */
    public function it_can_execute_an_action_when_running_asynchronously()
    {
        (new ComplexAction)->queue()->execute(new DataObject('foo'));

        $this->assertLogHas('foo bar');
    }

    /** @test */
    public function it_can_execute_an_action_with_callback_when_running_asynchronously()
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

    /** @test */
    public function it_can_use_a_complex_object_as_argument_when_running_asynchronously(): void
    {
        $model = PendingModel::create()
            ->applicationId('123abc')
            ->type('model')
            ->name('hello world');

        $queuedActionChainId = (new DumpTheFirstParameterAction)
            ->queue()
            ->execute($model);

        $action = QueuedActionChainRepository::new()
            ->getQueuedActionChain($queuedActionChainId)
            ->getActions()
            ->first();

        $this->assertInstanceOf(QueuedAction::class, $action);
        $this->assertEquals("dump the first parameter", $action->getName());

        $this->assertCount(1, $action->getArguments());
        $this->assertInstanceOf(PendingModel::class, $action->getArguments()[0]);

        $this->assertEquals($model->id, $action->getArguments()[0]->id);
        $this->assertEquals($model->applicationId, $action->getArguments()[0]->applicationId);
        $this->assertEquals($model->type, $action->getArguments()[0]->type);
        $this->assertEquals($model->name, $action->getArguments()[0]->name);
        $this->assertEquals($model->replicas, $action->getArguments()[0]->replicas);

        $this->assertEquals(ActionStatus::FAILED, $action->getStatus());
        $this->assertEquals("", $action->getOutput());
    }
}
