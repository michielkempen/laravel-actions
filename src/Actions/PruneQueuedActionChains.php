<?php

namespace MichielKempen\LaravelActions\Actions;

use MichielKempen\LaravelActions\Database\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;

class PruneQueuedActionChains
{
    use QueueableAction;

    private QueuedActionChainRepository $queuedActionChainRepository;

    public function __construct()
    {
        $this->queuedActionChainRepository = app(QueuedActionChainRepository::class);
    }

    public function execute(): void
    {
        $this->queuedActionChainRepository->pruneQueuedActionChains();
    }
}