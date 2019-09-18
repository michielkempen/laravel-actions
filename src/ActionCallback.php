<?php

namespace MichielKempen\LaravelActions;

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
     * @param Action|null $action
     * @param ActionChain|null $actionChain
     */
    public function __construct(?Action $action, ?ActionChain $actionChain)
    {
        $this->action = $action;
        $this->actionChain = $actionChain;
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
}