<?php

namespace MichielKempen\LaravelActions\Resources\Action;

class QueuedActionRepository
{
    private QueuedAction $model;

    public function __construct()
    {
        $this->model = app(QueuedAction::class);
    }

    public function getQueuedActionOrFail(string $queuedActionId): QueuedAction
    {
        return $this->model->findOrFail($queuedActionId);
    }

    public function createQueuedAction(string $chainId, int $order, Action $action): QueuedAction
    {
        return $this->model->create([
            'id' => $action->getId(),
            'chain_id' => $chainId,
            'order' => $order,
            'class' => $action->getClass(),
            'arguments' => $action->getArguments(),
            'name' => $action->getName(),
            'status' => $action->getStatus(),
            'output' => $action->getOutput(),
            'started_at' => $action->getStartedAt(),
            'finished_at' => $action->getFinishedAt(),
        ]);
    }
}
