<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Implementations\Async\QueueableActionProxy;

abstract class ActionProxy
{
    /**
     * @var object
     */
    protected $action;

    /**
     * @var array
     */
    protected $chainedActions = [];

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * ActionProxy constructor.
     * @param object $action
     * @param array $chainedActions
     */
    public function __construct(object $action, array $chainedActions = [])
    {
        $this->action = $action;
        $this->chainedActions = $chainedActions;
    }

    /**
     * @param string $class
     * @param array $arguments
     * @return ActionProxy
     */
    public function withCallback(string $class, array $arguments = []): self
    {
        $this->callbacks[] = [
            'class' => $class,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * @param array $actions
     * @return QueueableActionProxy
     */
    public function chain(array $actions): self
    {
        $this->chainedActions = array_merge($this->chainedActions, $actions);

        return $this;
    }

    /**
     * @param mixed ...$parameters
     */
    public abstract function execute(...$parameters);

    /**
     * @return object
     */
    public function getAction(): object
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getChainedActions(): array
    {
        return $this->chainedActions;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }
}