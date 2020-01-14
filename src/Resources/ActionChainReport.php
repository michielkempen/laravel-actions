<?php

namespace MichielKempen\LaravelActions\Resources;

use MichielKempen\LaravelActions\Resources\Action\ActionContract;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChainContract;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;

class ActionChainReport
{
    private ?ActionContract $action;
    private ?ActionChainContract $actionChain;

    public function __construct(?ActionContract $action, ?ActionChainContract $actionChain)
    {
        $this->action = $action;
        $this->actionChain = $actionChain;
    }

    public function hasAction(): bool
    {
        return ! is_null($this->action);
    }

    public function getAction(): ?ActionContract
    {
        return $this->action;
    }

    public function hasActionChain(): bool
    {
        return ! is_null($this->actionChain);
    }

    public function getActionChain(): ?ActionChainContract
    {
        return $this->actionChain;
    }

    public function hasQueuedActionChain(): bool
    {
        return is_a($this->actionChain, QueuedActionChain::class);
    }
}