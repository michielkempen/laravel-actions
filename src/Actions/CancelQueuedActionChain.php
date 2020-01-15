<?php

namespace MichielKempen\LaravelActions\Actions;

use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\Action\QueuedActionRepository;
use MichielKempen\LaravelActions\Resources\ActionStatus;

class CancelQueuedActionChain
{
    private QueuedActionRepository $queuedActionRepository;

    public function __construct()
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);
    }

    public function execute(string $actionChainId): void
    {
        $this->queuedActionRepository
            ->getPendingQueuedActionsForChain($actionChainId)
            ->each(function(QueuedAction $action) {
                $action->setStatus(ActionStatus::SKIPPED);
                $action->setFinishedAt(now());
                $action->save();
            });
    }
}