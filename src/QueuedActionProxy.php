<?php

namespace MichielKempen\LaravelQueueableActions;

use MichielKempen\LaravelQueueableActions\Database\QueuedAction;
use MichielKempen\LaravelQueueableActions\Database\QueuedActionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;

class QueuedActionProxy
{
    /**
     * @var QueuedActionRepository
     */
    private $queuedActionRepository;

    /**
     * @var
     */
    protected $action;

    /**
     * @var string|null
     */
    private $queuedActionId = null;

    /**
     * @param $action
     * @param Model|null $model
     */
    public function __construct($action, ?Model $model = null)
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);

        $this->action = $action;

        if(is_null($model)) {
            return;
        }

        $this->queuedActionId = $this->createQueuedAction($model)->getId();
    }

    /**
     * @param mixed ...$parameters
     * @return PendingDispatch
     */
    public function execute(...$parameters)
    {
        return dispatch(new QueuedActionJob($this->action, $this->queuedActionId, $parameters));
    }

    /**
     * @param Model $model
     * @return QueuedAction
     */
    private function createQueuedAction(Model $model): QueuedAction
    {
        $modelType = class_basename($model);
        $modelId = $model->id;
        $name = $this->getActionName();

        $queuedAction = $this->queuedActionRepository->createQueuedAction(
            $modelType, $modelId, $name, QueuedActionStatus::PENDING
        );

        return $queuedAction;
    }

    /**
     * @return string
     */
    private function getActionName(): string
    {
        if(property_exists($this->action, 'name')) {
            return $this->action->name;
        }

        $name = class_basename($this->action);
        $name = Str::replaceLast('Action', '', $name);
        $name = Str::snake($name, ' ');

        return $name;
    }
}
