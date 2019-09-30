<?php

namespace MichielKempen\LaravelActions\Actions;

use MichielKempen\LaravelActions\Database\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;

class PruneQueuedActionChains
{
    use QueueableAction;

    /**
     * @var QueuedActionChainRepository
     */
    private $queuedActionChainRepository;

    /**
     * CancelTimedOutActions constructor.
     */
    public function __construct()
    {
        $this->queuedActionChainRepository = app(QueuedActionChainRepository::class);
    }

    /**
     *
     */
    public function execute(): void
    {
        $this->queuedActionChainRepository->pruneQueuedActionChains();
    }
}