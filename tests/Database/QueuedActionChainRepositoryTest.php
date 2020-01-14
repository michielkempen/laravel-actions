<?php

namespace MichielKempen\LaravelActions\Tests\Database;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class QueuedActionChainRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_an_existing_queued_action_chain()
    {
        $queuedActionChain = factory(QueuedActionChain::class)->create();

        $repository = new QueuedActionChainRepository;
        $result = $repository->getQueuedActionChainOrFail($queuedActionChain->getId());

        $this->assertInstanceOf(QueuedActionChain::class, $result);
        $this->assertEquals($queuedActionChain->getId(), $result->getId());
    }

    /** @test */
    public function it_throws_an_exception_when_retrieving_a_non_existing_queued_action_chain()
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = new QueuedActionChainRepository;
        $repository->getQueuedActionChainOrFail($this->faker->uuid);
    }

    /** @test */
    public function it_can_create_a_queued_action_chain_without_model()
    {
        $name = "Task";
        $createdAt = now();

        $repository = new QueuedActionChainRepository;
        $result = $repository->createQueuedActionChain($name, null, null, $createdAt);

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
        $createdAt = now();

        $repository = new QueuedActionChainRepository;
        $result = $repository->createQueuedActionChain($name, $modelType, $modelId, $createdAt);

        $this->assertInstanceOf(QueuedActionChain::class, $result);
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($modelId, $result->getModelId());
        $this->assertEquals($modelType, $result->getModelType());
        $this->assertEquals($createdAt->toIso8601String(), $result->getCreatedAt()->toIso8601String());
    }
}