<?php

namespace MichielKempen\LaravelActions\Tests\Unit;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Tests\TestCase;

class QueuedActionRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_an_existing_queued_action()
    {
        $queuedAction = QueuedAction::create([
            'model_id' => 123,
            'model_type' => 'User',
            'name' => 'Create user',
            'status' => ActionStatus::RUNNING,
            'output' => null,
        ]);

        /** @var QueuedActionRepository $repository */
        $repository = app(QueuedActionRepository::class);
        $result = $repository->getQueuedActionOrFail($queuedAction->id);

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals($queuedAction->id, $result->id);
        $this->assertEquals(123, $result->model_id);
        $this->assertEquals('User', $result->model_type);
        $this->assertEquals('Create user', $result->name);
        $this->assertEquals(ActionStatus::RUNNING, $result->status);
        $this->assertEquals(null, $result->output);
    }

    /** @test */
    public function it_throws_an_error_when_retrieving_a_non_existing_queued_action()
    {
        $this->expectException(ModelNotFoundException::class);

        /** @var QueuedActionRepository $repository */
        $repository = app(QueuedActionRepository::class);
        $repository->getQueuedActionOrFail(321);
    }

    /** @test */
    public function it_can_create_a_queued_action()
    {
        /** @var QueuedActionRepository $repository */
        $repository = app(QueuedActionRepository::class);
        $result = $repository->createQueuedAction(
            'User', 123, 'Create user', ActionStatus::RUNNING
        );

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals(123, $result->model_id);
        $this->assertEquals('User', $result->model_type);
        $this->assertEquals('Create user', $result->name);
        $this->assertEquals(ActionStatus::RUNNING, $result->status);
        $this->assertEquals(null, $result->output);
    }

    /** @test */
    public function it_can_update_an_existing_queued_action()
    {
        $queuedAction = QueuedAction::create([
            'model_id' => 123,
            'model_type' => 'User',
            'name' => 'Create user',
            'status' => ActionStatus::RUNNING,
            'output' => null,
        ]);

        /** @var QueuedActionRepository $repository */
        $repository = app(QueuedActionRepository::class);
        $result = $repository->updateQueuedAction(
            $queuedAction->id, ActionStatus::FAILED, 'error'
        );

        $this->assertInstanceOf(QueuedAction::class, $result);
        $this->assertEquals($queuedAction->id, $result->id);
        $this->assertEquals(123, $result->model_id);
        $this->assertEquals('User', $result->model_type);
        $this->assertEquals('Create user', $result->name);
        $this->assertEquals(ActionStatus::FAILED, $result->status);
        $this->assertEquals('error', $result->output);
    }

    /** @test */
    public function it_throws_an_exception_when_updating_a_non_existing_queued_action()
    {
        $this->expectException(ModelNotFoundException::class);

        /** @var QueuedActionRepository $repository */
        $repository = app(QueuedActionRepository::class);
        $repository->updateQueuedAction(
            321, ActionStatus::FAILED, 'error'
        );
    }
}