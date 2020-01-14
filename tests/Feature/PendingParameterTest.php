<?php

namespace MichielKempen\LaravelActions\Tests\Feature;

use MichielKempen\LaravelActions\QueueableActionChain;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionOutput;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheFirstParameterAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Actions\ReturnTheParametersAsOutputAction;
use MichielKempen\LaravelActions\Tests\TestCase\Callbacks\LogCallback;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;
use Webpatser\Uuid\Uuid;

class PendingParameterTest extends TestCase
{
    /** @test */
    public function test_is_pending_parameters_work(): void
    {
        $actionChain = new QueueableActionChain;

        $uuid = (string) Uuid::generate(4);

        $queuedActionChainId = $actionChain
            ->queue()
            ->withName("Test action")
            ->addAction(ReturnTheFirstParameterAsOutputAction::class, ['hello', 'world'], "Greetings!", $uuid)
            ->addAction(ReturnTheParametersAsOutputAction::class, ['john', 'doe'])
            ->addAction(ReturnTheParametersAsOutputAction::class, [new ActionOutput($uuid), 'joe'], "Test action output")
            ->withCallback(LogCallback::class)
            ->execute();

        $actions = QueuedActionChain::find($queuedActionChainId)->getActions()->map(function(QueuedAction $queuedAction) {
            return [
                'order' => $queuedAction->getOrder(),
                'name' => $queuedAction->getName(),
                'arguments' => $queuedAction->getArguments(),
                'status' => $queuedAction->getStatus(),
                'output' => $queuedAction->getOutput(),
            ];
        });

        dd($actions);
    }
}