<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Database\QueuedActionChain;

class ActionCallback
{
    private ?Action $action;
    private ?ActionChain $actionChain;
    private ?QueuedActionChain $queuedActionChain;

    public function __construct(?Action $action, ?ActionChain $actionChain, ?QueuedActionChain $queuedActionChain)
    {
        $this->action = $action;
        $this->actionChain = $actionChain;
        $this->queuedActionChain = $queuedActionChain;
    }

    public function hasAction(): bool
    {
        return ! is_null($this->action);
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function hasActionChain(): bool
    {
        return ! is_null($this->actionChain);
    }

    public function getActionChain(): ?ActionChain
    {
        return $this->actionChain;
    }

    public function hasQueuedActionChain(): bool
    {
        return ! is_null($this->queuedActionChain);
    }

    public function getQueuedActionChain(): ?QueuedActionChain
    {
        return $this->queuedActionChain;
    }
}