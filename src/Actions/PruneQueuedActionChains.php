<?php

namespace MichielKempen\LaravelActions\Actions;

use MichielKempen\LaravelActions\QueueableAction;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainRepository;

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