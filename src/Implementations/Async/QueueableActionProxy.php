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
     * @return string|null
     */
    public function execute(...$parameters): ?string
    {
        if(empty($this->chainedActions)) {
            $this->executeAction($parameters);
            return null;
        } else {
            $queuedActionChain = $this->executeActionChain($parameters);
            return $queuedActionChain->getId();
        }
    }

    /**
     * @param mixed ...$parameters
     * @return QueuedActionJob
     */
    public function getJob(...$parameters): QueuedActionJob
    {
        $action = Action::createFromAction($this->action, $parameters);

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            null, null, $this->modelType, $this->modelId, $action, $this->callbacks
        );

        return new QueuedActionJob($action->instantiateAction(), $queuedAction->getId());
    }

    /**
     * @param array $parameters
     */
    private function executeAction(array $parameters): void
    {
        $action = Action::createFromAction($this->action, $parameters);

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            null, null, $this->modelType, $this->modelId, $action, $this->callbacks
        );

        dispatch(new QueuedActionJob($action->instantiateAction(), $queuedAction->getId()));
    }

    /**
     * @param array $parameters
     * @return QueuedActionChain
     */
    private function executeActionChain(array $parameters): QueuedActionChain
    {
        $queuedActionChain = $this->createActionChain($parameters);

        $this->triggerCallbacks($queuedActionChain);

        $queuedActions = $queuedActionChain->getActions();
        $queuedAction = $queuedActions->shift();
        $action = $queuedAction->getAction()->instantiateAction();
        $pendingDispatch = dispatch(new QueuedActionJob($action, $queuedAction->getId()));

        $chainedQueuedActions = $queuedActions->map(function(QueuedAction $queuedAction) {
            $action = $queuedAction->getAction()->instantiateAction();
            return new QueuedActionJob($action, $queuedAction->getId());
        })->all();

        $pendingDispatch->chain($chainedQueuedActions);

        return $queuedActionChain;
    }

    /**
     * @param array $parameters
     * @return QueuedActionChain
     */
    private function createActionChain(array $parameters): QueuedActionChain
    {
        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain();

        $order = 0;

        $action = Action::createFromAction($this->action, $parameters);
        $this->queuedActionRepository->createQueuedAction(
            $queuedActionChain->getId(), ++$order, $this->modelType, $this->modelId, $action, $this->callbacks
        );

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction(app($actionClass), $parameters);
            $this->queuedActionRepository->createQueuedAction(
                $queuedActionChain->getId(), ++$order, $this->modelType, $this->modelId, $action, $this->callbacks
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
