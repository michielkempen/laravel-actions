<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionCallback;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Database\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;

class QueueableActionProxy extends ActionProxy
{
    /**
     * @var QueuedActionRepository
     */
    private $queuedActionRepository;

    /**
     * @var QueuedActionChainRepository
     */
    private $queuedActionChainRepository;

    /**
     * @var string|null
     */
    protected $modelType;

    /**
     * @var string|null
     */
    protected $modelId;

    /**
     * @param object $action
     */
    public function __construct(object $action)
    {
        parent::__construct($action);

        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionChainRepository = app(QueuedActionChainRepository::class);
    }

    /**
     * @param Model $model
     * @return QueueableActionProxy
     */
    public function onModel(Model $model): self
    {
        $this->modelType = class_basename($model);
        $this->modelId = $model->id;

        return $this;
    }

    /**
     * @param mixed ...$parameters
     * @return string
     */
    public function execute(...$parameters): string
    {
        $queuedActionChain = $this->createActionChain($parameters);

        $this->triggerCallbacks($queuedActionChain);

        $queuedActions = $queuedActionChain->getActions();
        $firstQueuedAction = $queuedActions->shift();

        $pendingDispatch = dispatch(
            new QueuedActionJob($firstQueuedAction->getAction()->instantiateAction(), $firstQueuedAction->getId())
        );

        if($queuedActions->isNotEmpty()) {
            $chainedQueuedActions = $queuedActions
                ->map(function(QueuedAction $queuedAction) {
                    return new QueuedActionJob($queuedAction->getAction()->instantiateAction(), $queuedAction->getId());
                })
                ->all();

            $pendingDispatch->chain($chainedQueuedActions);
        }

        return $queuedActionChain->getId();
    }

    /**
     * @param mixed ...$parameters
     * @return QueuedActionJob
     */
    public function getJob(...$parameters): QueuedActionJob
    {
        $action = Action::createFromAction($this->action, $parameters);

        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain(
            $this->modelType, $this->modelId, now()
        );

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            $queuedActionChain->getId(), 1, $action, $this->callbacks
        );

        return new QueuedActionJob($action->instantiateAction(), $queuedAction->getId());
    }

    /**
     * @param array $parameters
     * @return QueuedActionChain
     */
    private function createActionChain(array $parameters): QueuedActionChain
    {
        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain(
            $this->modelType, $this->modelId, now()
        );

        $order = 0;

        $action = Action::createFromAction($this->action, $parameters);
        $this->queuedActionRepository->createQueuedAction(
            $queuedActionChain->getId(), ++$order, $action, $this->callbacks
        );

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction(app($actionClass), $parameters);
            $this->queuedActionRepository->createQueuedAction(
                $queuedActionChain->getId(), ++$order, $action, $this->callbacks
            );
        }

        return $queuedActionChain;
    }

    /**
     * @param QueuedActionChain $queuedActionChain
     */
    private function triggerCallbacks(QueuedActionChain $queuedActionChain): void
    {
        $actionChain = ActionChain::createFromQueuedActionChain($queuedActionChain);
        $actionCallback = new ActionCallback(null, $actionChain);

        foreach ($this->callbacks as $callback) {
            $callback($actionCallback);
        }
    }

    /**
     * @return string|null
     */
    public function getModelType(): ?string
    {
        return $this->modelType;
    }

    /**
     * @return string|null
     */
    public function getModelId(): ?string
    {
        return $this->modelId;
    }
}
