<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Support\Carbon;

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
     * @param string $name
     * @param string|null $modelType
     * @param string|null $modelId
     * @param Carbon $createdAt
     * @return QueuedActionChain
     */
    public function createQueuedActionChain(
        string $name, ?string $modelType, ?string $modelId, Carbon $createdAt
    ): QueuedActionChain
    {
        return $this->model->create([
            'name' => $name,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'created_at' => $createdAt,
        ]);
    }

    /**
     *
     */
    public function pruneQueuedActionChains(): void
    {
        $this->model->where('created_at', '<', Carbon::now()->subHours(3)->toDateTimeString())->delete();
    }
}
