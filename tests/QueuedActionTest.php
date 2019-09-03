<?php

namespace MichielKempen\LaravelQueueableActions\Tests;

use MichielKempen\LaravelQueueableActions\Database\QueuedAction;
use MichielKempen\LaravelQueueableActions\QueuedActionStatus;

class QueuedActionTest extends TestCase
{
    /** @test */
    public function the_getters_return_the_correct_attributes_of_the_model()
    {
        /** @var QueuedAction $queuedAction */
        $queuedAction = QueuedAction::create([
            'model_id' => 123,
            'model_type' => 'User',
            'name' => 'Create user',
            'status' => QueuedActionStatus::RUNNING,
            'output' => null,
        ]);

        $this->assertEquals($queuedAction->id, $queuedAction->getId());
        $this->assertEquals(123, $queuedAction->getModelId());
        $this->assertEquals('User', $queuedAction->getModelType());
        $this->assertEquals('Create user', $queuedAction->getName());
        $this->assertEquals(QueuedActionStatus::RUNNING, $queuedAction->getStatus());
        $this->assertEquals(null, $queuedAction->getOutput());
    }
}