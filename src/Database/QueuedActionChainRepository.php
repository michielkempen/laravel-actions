<?php

namespace MichielKempen\LaravelActions\Database;

class QueuedActionChainRepository
{
    /**
     * @var QueuedActionChain
     */
    private $model;

    /**
     * QueuedActionRepository constructor.
     */
    public function __construct()
    {
        $this->model = app(QueuedActionChain::class);
    }

    /**
     * @param string $queuedActionChainId
     * @return QueuedActionChain
     */
    public function getQueuedActionChainOrFail(string $queuedActionChainId): QueuedActionChain
    {
        return $this->model->findOrFail($queuedActionChainId);
    }

    /**
     * @param array $callbacks
     * @return QueuedActionChain
     */
    public function createQueuedActionChain(array $callbacks): QueuedActionChain
    {
        return $this->model->create([
            'callbacks' => $callbacks,
        ]);
    }
}
