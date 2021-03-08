<?php

namespace MichielKempen\LaravelActions\Resources\ActionChain;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\Action\QueuedActionFactory;
use MichielKempen\LaravelActions\Testing\TestCase;

class QueuedActionChainTest extends TestCase
{
    /** @test */
    public function it_returns_the_correct_attributes_from_the_database()
    {
        $modelId = $this->faker->uuid;
        $modelType = 'TestModel';
        $createdAt = now();

        $queuedActionChain = QueuedActionChainFactory::new()
            ->modelId($modelId)
            ->modelType($modelType)
            ->createdAt($createdAt)
            ->create();

        $queuedActionA = QueuedActionFactory::new()
            ->chain($queuedActionChain)
            ->order(3)
            ->create();

        $queuedActionB = QueuedActionFactory::new()
            ->chain($queuedActionChain)
            ->order(1)
            ->create();

        QueuedActionFactory::new()
            ->order(1)
            ->create();

        $queuedActionD = QueuedActionFactory::new()
            ->chain($queuedActionChain)
            ->order(2)
            ->create();

        $actions = $queuedActionChain->getActions();
        $this->assertInstanceOf(Collection::class, $actions);
        $this->assertEquals(3, $actions->count());
        $this->assertEquals($queuedActionB->getId(), $actions->get(0)->getId());
        $this->assertEquals($queuedActionD->getId(), $actions->get(1)->getId());
        $this->assertEquals($queuedActionA->getId(), $actions->get(2)->getId());
        $this->assertEquals($modelId, $queuedActionChain->getModelId());
        $this->assertEquals($modelType, $queuedActionChain->getModelType());
        $this->assertEquals($createdAt->toIso8601String(), $queuedActionChain->getCreatedAt()->toIso8601String());
    }
}
