<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use MichielKempen\LaravelActions\Action;
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
     * @param $action
     */
    public function __construct($action)
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
     * @param array $parameters
     */
    public function executeAction(array $parameters): void
    {
        $action = Action::createFromAction(get_class($this->action), $parameters);

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            null, null, $this->modelType, $this->modelId, $action, $this->callbacks
        );

        dispatch(new QueuedActionJob($action->instantiateAction(), $queuedAction->getId()));
    }

    /**
     * @param array $parameters
     * @return QueuedActionChain
     */
    public function executeActionChain(array $parameters): QueuedActionChain
    {
        $queuedActionChain = $this->createActionChain($parameters);
        $queuedActions = $queuedActionChain->getActions();

        $queuedAction = $queuedActions->pop();
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
