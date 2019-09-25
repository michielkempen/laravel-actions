<?php

namespace MichielKempen\LaravelActions\Database;

use MichielKempen\LaravelActions\Action;

class QueuedActionRepository
{
    /**
     * @var QueuedAction
     */
    private $model;

    /**
     * QueuedActionRepository constructor.
     */
    public function __construct()
    {
        $this->model = app(QueuedAction::class);
    }

    /**
     * @param string $queuedActionId
     * @return QueuedAction
     */
    public function getQueuedActionOrFail(string $queuedActionId): QueuedAction
    {
        return $this->model->findOrFail($queuedActionId);
    }

    /**
     * @param string $chainId
     * @param int $order
     * @param Action $action
     * @param array $callbacks
     * @return QueuedAction
     */
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

    /**
     * @param string $queuedActionId
     * @param Action $action
     * @return QueuedAction
     */
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
