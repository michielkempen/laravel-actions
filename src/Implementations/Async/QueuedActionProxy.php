<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;
use MichielKempen\LaravelActions\Database\QueuedActionChainRepository;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;

class QueuedActionProxy extends ActionProxy
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
     * @return QueuedActionProxy
     */
    public function onModel(Model $model): self
    {
        $this->modelType = class_basename($model);
        $this->modelId = $model->id;

        return $this;
    }

    /**
     * @param mixed ...$parameters
     */
    public function execute(...$parameters)
    {
        if(empty($this->chainedActions)) {
            $this->executeAction($parameters);
        } else {
            $this->executeActionChain($parameters);
        }
    }

    /**
     * @param array $parameters
     */
    public function executeAction(array $parameters): void
    {
        $action = Action::createFromAction(get_class($this->action), $parameters);

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            null, null, $this->modelType, $this->modelId, $action
        );

        dispatch(new QueuedActionJob($action->instantiateAction(), $queuedAction->getId()));
    }

    /**
     * @param array $parameters
     */
    public function executeActionChain(array $parameters): void
    {
        $queuedActionChain = $this->createActionChain($parameters);
        $queuedActions = $queuedActionChain->getActions();

        $queuedAction = $queuedActions->pop();
        $action = $queuedAction->getAction()->instantiateAction();
        $pendingDispatch = dispatch(new QueuedActionJob($action, $queuedAction->getId()));

        $chainedQueuedActions = $queuedActions->each(function(QueuedAction $queuedAction) {
            $action = $queuedAction->getAction()->instantiateAction();
            return new QueuedActionJob($action, $queuedAction->getId());
        })->toArray();

        $pendingDispatch->chain($chainedQueuedActions);
    }

    /**
     * @param array $parameters
     * @return QueuedActionChain
     */
    private function createActionChain(array $parameters): QueuedActionChain
    {
        $queuedActionChain = $this->queuedActionChainRepository->createQueuedActionChain($this->callbacks);

        $order = 0;

        $action = Action::createFromAction(get_class($this->action), $parameters);
        $this->queuedActionRepository->createQueuedAction(
            $queuedActionChain->getId(), ++$order, $this->modelType, $this->modelId, $action
        );

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction($actionClass, $parameters);
            $this->queuedActionRepository->createQueuedAction(
                $queuedActionChain->getId(), ++$order, $this->modelType, $this->modelId, $action
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
