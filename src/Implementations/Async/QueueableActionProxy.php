<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChainReport;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\ActionChainCallback;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Database\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;

class QueueableActionProxy extends ActionProxy
{
    private QueuedActionRepository $queuedActionRepository;
    private QueuedActionChainRepository $queuedActionChainRepository;
    protected ?string $name = null;
    protected ?string $modelType = null;
    protected ?string $modelId = null;

    public function __construct(object $actionInstance)
    {
        parent::__construct($actionInstance);
        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionChainRepository = app(QueuedActionChainRepository::class);
    }

    public function withName(string $name): QueueableActionProxy
    {
        $this->name = $name;

        return $this;
    }

    public function onModel(Model $model): QueueableActionProxy
    {
        $this->modelType = class_basename($model);
        $this->modelId = $model->id;

        return $this;
    }

    public function execute(...$arguments): string
    {
        $queuedActionChain = $this->createActionChain($arguments);

        $this->triggerCallbacks($queuedActionChain);

        $queuedActionJobs = $queuedActionChain->getActions()
            ->map(fn(QueuedAction $queuedAction) => $this->mapQueuedActionToQueuedActionJob($queuedAction));

        list($firstJob, $chainedJobs) = $this->splitCollection($queuedActionJobs);

        dispatch($firstJob)->chain($chainedJobs);

        return $queuedActionChain->getId();
    }

    private function createActionChain(array $arguments): QueuedActionChain
    {
        $action = new Action($this->actionInstance, $arguments);
        $name = $this->name ?? $action->getName();

        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain(
            $name, $this->modelType, $this->modelId, $this->callbacks, now()
        );

        $this->queuedActionRepository->createQueuedAction($queuedActionChain->getId(), 0, $action);

        $order = 1;
        $this->chainedActions->each(function(Action $action) use ($queuedActionChain, &$order) {
            $this->queuedActionRepository->createQueuedAction($queuedActionChain->getId(), $order++, $action);
        });

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

    public function getModelType(): ?string
    {
        return $this->modelType;
    }

    public function getModelId(): ?string
    {
        return $this->modelId;
    }
}
