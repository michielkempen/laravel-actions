<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\Collection;

abstract class ActionProxy
{
    protected object $actionInstance;
    protected Collection $chainedActions;
    protected Collection $callbacks;

    public function __construct(object $actionInstance, Collection $chainedActions = null)
    {
        $this->actionInstance = $actionInstance;
        $this->chainedActions = $chainedActions ?? new Collection;
        $this->callbacks = new Collection;
    }

    public abstract function execute(...$arguments);

    public function getActionInstance(): object
    {
        return $this->actionInstance;
    }

    public function chain(string $class, array $arguments = []): ActionProxy
    {
        $this->chainedActions->add(new Action(resolve($class), $arguments));

        return $this;
    }

    public function withCallback(string $class, array $arguments = []): ActionProxy
    {
        $this->callbacks->add(new ActionChainCallback($class, $arguments));

        return $this;
    }

    public function getChainedActions(): Collection
    {
        return $this->chainedActions;
    }

    public function getCallbacks(): Collection
    {
        return $this->callbacks;
    }
}