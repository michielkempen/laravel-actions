<?php

namespace MichielKempen\LaravelActions\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Exceptions\EmptyActionChainException;
use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\Action\QueuedActionRepository;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainRepository;

class QueueableActionChainProxy
{
    private QueuedActionRepository $queuedActionRepository;
    private QueuedActionChainRepository $queuedActionChainRepository;

    private ?string $name;
    private ?Model $model;
    private Collection $actions;
    private Collection $callbacks;

    public function __construct(
        Collection $actions = null, Collection $callbacks = null, Model $model = null, string $name = null
    )
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionChainRepository = app(QueuedActionChainRepository::class);

        $this->actions = $actions === null ? new Collection : $actions;
        $this->callbacks = $callbacks === null ? new Collection : $callbacks;
        $this->model = $model;
        $this->name = $name;
    }

    public function addAction(
        string $class, array $arguments = [], ?string $name = null, ?string $uuid = null
    ): QueueableActionChainProxy
    {
        $this->actions->add(new Action(resolve($class), $arguments, $name, $uuid));
        return $this;
    }

    public function withCallback(string $class, array $arguments = []): QueueableActionChainProxy
    {
        $this->callbacks->add(new ActionChainCallback($class, $arguments));
        return $this;
    }

    public function onModel(Model $model): QueueableActionChainProxy
    {
        $this->model = $model;
        return $this;
    }

    public function withName(string $name): QueueableActionChainProxy
    {
        $this->name = $name;
        return $this;
    }

    public function execute(): string
    {
        if($this->actions->isEmpty()) {
            throw new EmptyActionChainException;
        }

        $queuedActionChain = $this->createActionChain();

        $this->triggerCallbacks($queuedActionChain);

        $queuedActionJobs = $queuedActionChain->getActions()
            ->map(fn(QueuedAction $queuedAction) => $this->mapQueuedActionToQueuedActionJob($queuedAction));

        list($firstJob, $chainedJobs) = $this->splitCollection($queuedActionJobs);

        dispatch($firstJob)->chain($chainedJobs);

        return $queuedActionChain->getId();
    }

    private function createActionChain(): QueuedActionChain
    {
        $name = $this->name ?? $this->actions->first()->getName();

        if($this->model !== null) {
            $modelType = class_basename($this->model);
            $modelId = $this->model->id;
        } else {
            $modelType = null;
            $modelId = null;
        }

        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain(
            $name, $modelType, $modelId, $this->callbacks, now()
        );

        $order = 0;
        foreach ($this->actions as $action) {
            $this->queuedActionRepository->createQueuedAction($queuedActionChain->getId(), $order, $action);
            $order++;
        }

        return $queuedActionChain;
    }

    private function triggerCallbacks(QueuedActionChain $queuedActionChain): void
    {
        $actionChainReport = new ActionChainReport(null, $queuedActionChain);

        $this->callbacks->each(fn(ActionChainCallback $callback) => $callback->trigger($actionChainReport));
    }

    private function splitCollection(Collection $actionChainJobs): array
    {
        $firstQueuedAction = $actionChainJobs->shift();

        return array($firstQueuedAction, $actionChainJobs->all());
    }

    private function mapQueuedActionToQueuedActionJob(QueuedAction $queuedAction): QueuedActionJob
    {
        return new QueuedActionJob($queuedAction->instantiate(), $queuedAction->getId());
    }
}