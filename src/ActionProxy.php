<?php

namespace MichielKempen\LaravelActions;

abstract class ActionProxy
{
    protected object $action;
    protected array $chainedActions = [];
    protected array $callbacks = [];

    public function __construct(object $action, array $chainedActions = [])
    {
        $this->action = $action;
        $this->chainedActions = $chainedActions;
    }

    public function withCallback(string $class, array $arguments = []): ActionProxy
    {
        $this->callbacks[] = [
            'class' => $class,
            'arguments' => $arguments,
        ];

        return $this;
    }

    public function chain(array $actions): ActionProxy
    {
        $this->chainedActions = array_merge($this->chainedActions, $actions);

        return $this;
    }

    public abstract function execute(...$parameters);

    public function getAction(): object
    {
        return $this->action;
    }

    public function getChainedActions(): array
    {
        return $this->chainedActions;
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }
}