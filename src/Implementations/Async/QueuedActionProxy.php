<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;

class QueuedActionProxy extends ActionProxy
{
    /**
     * @var QueuedActionRepository
     */
    private $queuedActionRepository;

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
        $actionChain = $this->createActionChain($parameters);

        $pendingDispatch = dispatch($this->mapActionToQueuedActionJob($this->action, $parameters));

        if(empty($this->chainedActions)) {
            return;
        }

        $chainedActions = array_map(function(string $actionClass) use ($parameters) {
            $action = app($actionClass);
            return $this->mapActionToQueuedActionJob($action, $parameters);
        }, $this->chainedActions);

        $pendingDispatch->chain($chainedActions);
    }

    /**
     * @param array $parameters
     * @return ActionChain
     */
    private function createActionChain(array $parameters): ActionChain
    {
        $actionChain = new QueuedActionChain($this->callbacks);

        $action = Action::createFromAction(get_class($this->action), $parameters);
        $actionChain->addAction($action);

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction($actionClass, $parameters);
            $actionChain->addAction($action);
        }

        return $actionChain;
    }

    /**
     * @param $action
     * @param array $parameters
     * @return QueuedActionJob
     */
    private function mapActionToQueuedActionJob($action, array $parameters = []): QueuedActionJob
    {
        $action = Action::createFromAction($action, $parameters);

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            $this->modelType, $this->modelId, $action
        );

        return new QueuedActionJob($action, $queuedAction->getId());
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
