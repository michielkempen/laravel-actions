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
     * @param string|null $modelType
     * @param string|null $modelId
     * @param Action $action
     * @return QueuedAction
     */
    public function createQueuedAction(?string $modelType, ?string $modelId, Action $action): QueuedAction
    {
        return $this->model->create([
            'model_id' => $modelId,
            'model_type' => $modelType,
            'status' => $action->getStatus(),
            'action' => $action->toArray(),
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
