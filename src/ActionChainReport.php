<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Database\QueuedActionChain;

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