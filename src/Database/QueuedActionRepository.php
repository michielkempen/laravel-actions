<?php

namespace MichielKempen\LaravelQueueableActions\Database;

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
     * @param string $modelType
     * @param string $modelId
     * @param string $name
     * @param string $status
     * @return QueuedAction
     */
    public function createQueuedAction(string $modelType, string $modelId, string $name, string $status): QueuedAction
    {
        return $this->model->create([
            'model_id' => $modelId,
            'model_type' => $modelType,
            'name' => $name,
            'status' => $status,
            'output' => null,
        ]);
    }

    /**
     * @param string $queuedActionId
     * @param string $status
     * @param string|null $output
     * @return QueuedAction
     */
    public function updateQueuedAction(string $queuedActionId, string $status, string $output = null): QueuedAction
    {
        $queuedAction = $this->getQueuedActionOrFail($queuedActionId);

        $queuedAction->update([
            'status' => $status,
            'output' => $output,
        ]);

        return $queuedAction;
    }
}
