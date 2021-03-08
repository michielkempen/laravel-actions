<?php

namespace MichielKempen\LaravelActions\Resources\ActionChain;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Testing\TestCase;

class QueuedActionChainRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_an_existing_queued_action_chain()
    {
        $queuedActionChain = QueuedActionChainFactory::new()->create();

        $result = (new QueuedActionChainRepository)->getQueuedActionChain($queuedActionChain->getId());

        $this->assertInstanceOf(QueuedActionChain::class, $result);
        $this->assertEquals($queuedActionChain->getId(), $result->getId());
    }

    /** @test */
    public function it_throws_an_exception_when_retrieving_a_non_existing_queued_action_chain()
    {
        $this->expectException(ModelNotFoundException::class);

        (new QueuedActionChainRepository)->getQueuedActionChain($this->faker->uuid);
    }

    /** @test */
    public function it_can_create_a_queued_action_chain_without_model()
    {
        $name = "Task";
        $callbacks = new Collection;
        $createdAt = now();

        $result = (new QueuedActionChainRepository)->createQueuedActionChain($name, null, null, $callbacks, $createdAt);

        $this->assertInstanceOf(QueuedActionChain::class, $result);
        $this->assertEquals($name, $result->getName());
        $this->assertNull($result->getModelId());
        $this->assertNull($result->getModelType());
        $this->assertEquals($createdAt->toIso8601String(), $result->getCreatedAt()->toIso8601String());
    }

    /** @test */
    public function it_can_create_a_queued_action_chain_with_model()
    {
        $name = "Task";
        $modelId = $this->faker->uuid;
        $modelType = 'TestModel';
        $callbacks = new Collection;
        $createdAt = now();

        $result = (new QueuedActionChainRepository)->createQueuedActionChain(
            $name, $modelType, $modelId, $callbacks, $createdAt
        );

        $this->assertInstanceOf(QueuedActionChain::class, $result);
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($modelId, $result->getModelId());
        $this->assertEquals($modelType, $result->getModelType());
        $this->assertEquals($callbacks->count(), $result->getCallbacks()->count());
        $this->assertEquals($createdAt->toIso8601String(), $result->getCreatedAt()->toIso8601String());
    }
}
