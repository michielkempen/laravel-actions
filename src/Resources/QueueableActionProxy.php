<?php

namespace MichielKempen\LaravelActions\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\Action\Action;

class QueueableActionProxy
{
    private object $actionInstance;
    private ?string $name;
    private ?Model $model;
    private Collection $callbacks;

    public function __construct(object $actionInstance)
    {
        $this->actionInstance = $actionInstance;
        $this->callbacks = new Collection;
        $this->model = null;
        $this->name = null;
    }

    public function withCallback(string $class, array $arguments = []): QueueableActionProxy
    {
        $this->callbacks->add(new ActionChainCallback($class, $arguments));
        return $this;
    }

    public function onModel(Model $model): QueueableActionProxy
    {
        $this->model = $model;
        return $this;
    }

    public function withName(string $name): QueueableActionProxy
    {
        $this->name = $name;
        return $this;
    }

    public function execute(...$arguments): string
    {
        $action = new Action($this->actionInstance, $arguments, $this->name);
        $actions = new Collection([$action]);
        $actionChainProxy = new QueueableActionChainProxy($actions, $this->callbacks, $this->model, $this->name);

        return $actionChainProxy->execute();
    }
}