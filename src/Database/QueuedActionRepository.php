<?php

namespace MichielKempen\LaravelActions\Database;

use MichielKempen\LaravelActions\Action;

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

    public function createQueuedAction(string $chainId, int $order, Action $action, array $callbacks): QueuedAction
    {
        return $this->model->create([
            'chain_id' => $chainId,
            'order' => $order,
            'status' => $action->getStatus(),
            'action' => $action->toArray(),
            'callbacks' => $callbacks,
        ]);
    }

    public function updateQueuedAction(string $queuedActionId, Action $action): QueuedAction
    {
        $queuedAction = $this->getQueuedActionOrFail($queuedActionId);

        $queuedAction->update([
            'status' => $action->getStatus(),
            'action' => $action->toArray(),
        ]);

        return $queuedAction;
    }
}
