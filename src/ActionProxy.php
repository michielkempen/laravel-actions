<?php

namespace MichielKempen\LaravelActions;

use Closure;
use Illuminate\Queue\SerializableClosure;

abstract class ActionProxy
{
    /**
     * @var
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
     * @param $action
     * @param array $chainedActions
     */
    public function __construct($action, array $chainedActions = [])
    {
        $this->action = $action;
        $this->chainedActions = $chainedActions;
    }

    /**
     * @param Closure $callback
     * @return ActionProxy
     */
    public function withCallback($callback): self
    {
        $this->callbacks[] = new SerializableClosure($callback);

        return $this;
    }

    /**
     * @param mixed ...$parameters
     */
    public abstract function execute(...$parameters);

    /**
     * @return mixed
     */
    public function getAction()
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