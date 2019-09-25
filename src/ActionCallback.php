<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Database\QueuedActionChain;

class ActionCallback
{
    /**
     * @var Action|null
     */
    private $action;

    /**
     * @var ActionChain|null
     */
    private $actionChain;

    /**
     * @var QueuedActionChain|null
     */
    private $queuedActionChain;

    /**
     * @param Action|null $action
     * @param ActionChain|null $actionChain
     * @param QueuedActionChain|null $queuedActionChain
     */
    public function __construct(?Action $action, ?ActionChain $actionChain, ?QueuedActionChain $queuedActionChain)
    {
        $this->action = $action;
        $this->actionChain = $actionChain;
        $this->queuedActionChain = $queuedActionChain;
    }

    /**
     * @return bool
     */
    public function hasAction(): bool
    {
        return ! is_null($this->action);
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        return $this->action;
    }

    /**
     * @return bool
     */
    public function hasActionChain(): bool
    {
        return ! is_null($this->actionChain);
    }

    /**
     * @return ActionChain|null
     */
    public function getActionChain(): ?ActionChain
    {
        return $this->actionChain;
    }

    /**
     * @return bool
     */
    public function hasQueuedActionChain(): bool
    {
        return ! is_null($this->queuedActionChain);
    }

    /**
     * @return QueuedActionChain|null
     */
    public function getQueuedActionChain(): ?QueuedActionChain
    {
        return $this->queuedActionChain;
    }
}