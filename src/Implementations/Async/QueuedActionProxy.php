<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Database\Eloquent\Model;
use MichielKempen\LaravelActions\Action;
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
     * @param array $actions
     * @return QueuedActionProxy
     */
    public function chain(array $actions): self
    {
        $this->chainedActions = array_merge($this->chainedActions, $actions);

        return $this;
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
        $pendingDispatch = dispatch($this->mapActionToQueuedActionJob($this->action, $parameters));

        if(empty($this->chainedActions)) {
            return;
        }

        $chainedActions = array_map(function($actionClass) use ($parameters) {
            $action = app($actionClass);
            return $this->mapActionToQueuedActionJob($action, $parameters);
        }, $this->chainedActions);

        $pendingDispatch->chain($chainedActions);
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
