<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Support\Carbon;

class QueuedActionChainRepository
{
    private QueuedActionChain $model;

    public function __construct()
    {
        $this->model = app(QueuedActionChain::class);
    }

    public function getQueuedActionChainOrFail(string $queuedActionChainId): QueuedActionChain
    {
        return $this->model->findOrFail($queuedActionChainId);
    }

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

    public function pruneQueuedActionChains(): void
    {
        $this->model
            ->newQuery()
            ->where('created_at', '<', Carbon::now()->subHours(3)->toDateTimeString())
            ->delete();
    }
}
