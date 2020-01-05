<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\ActionChainCallback;

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
        string $name, ?string $modelType, ?string $modelId, Collection $callbacks, Carbon $createdAt
    ): QueuedActionChain
    {
        return $this->model->create([
            'name' => $name,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'callbacks' => $callbacks->map(fn(ActionChainCallback $callback) => $callback->serialize())->all(),
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
