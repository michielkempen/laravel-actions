<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Exception as PhpException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;

class QueuedActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var QueuedActionRepository
     */
    private $queuedActionRepository;

    /**
     * @var string
     */
    private $queuedActionId;

    /**
     * @var null|QueuedAction
     */
    private $queuedAction;

    /**
     * @param $action
     * @param string $queuedActionId
     */
    public function __construct($action, string $queuedActionId)
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionId = $queuedActionId;
        $this->resolveQueueableProperties($action);
    }

    /**
     * @param $action
     */
    private function resolveQueueableProperties($action)
    {
        $queueableProperties = ['connection', 'queue', 'delay', 'tries', 'timeout'];

        foreach ($queueableProperties as $queueableProperty) {
            if(property_exists($action, $queueableProperty)) {
                $this->{$queueableProperty} = $action->{$queueableProperty};
            }
        }
    }

    /**
     * ...
     */
    public function handle()
    {
        $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);

        $action = $this->queuedAction->getAction();

        $action->setStartedAt(now());

        $this->executeAction($action);

        $action->setFinishedAt(now());

        $this->queuedAction = $this->queuedActionRepository->updateQueuedAction($this->queuedAction->getId(), $action);

        $this->triggerCallbacks();
    }

    /**
     * @param Action $action
     */
    private function executeAction(Action $action): void
    {
        $actionInstance = $action->instantiateAction();

        if($this->queuedAction->hasChain() && $this->shouldSkipAction($actionInstance)) {
            $action->setStatus(ActionStatus::SKIPPED);
            return;
        }

        try {
            $output = $actionInstance->execute(...$action->getParameters());
        } catch (PhpException $exception) {
            $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
            return;
        }

        $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
    }

    /**
     * @param $actionInstance
     * @return bool
     */
    private function shouldSkipAction($actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        $actionChain = ActionChain::createFromQueuedActionChain($this->queuedAction->getChain());

        return $actionInstance->skip($actionChain);
    }

    /**
     * @param PhpException $exception
     */
    public function failed(PhpException $exception)
    {
        $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);

        $action = $this->queuedAction
            ->getAction()
            ->setFinishedAt(now())
            ->setStatus(ActionStatus::FAILED)
            ->setOutput($exception->getMessage());

        $this->queuedAction = $this->queuedActionRepository->updateQueuedAction($this->queuedActionId, $action);

        $this->triggerCallbacks();

        $actionInstance = $action->instantiateAction();

        if(method_exists($actionInstance, 'failed')) {
            $actionInstance->failed($exception);
        }
    }

    /**
     * ...
     */
    private function triggerCallbacks(): void
    {
        foreach ($this->queuedAction->getCallbacks() as $callback) {
            $action = $this->queuedAction->getAction();

            if($this->queuedAction->hasChain()) {
                $queuedActionChain = $this->queuedAction->getChain();
                $actionChain = ActionChain::createFromQueuedActionChain($queuedActionChain);
                $callback($action, $actionChain);
            } else {
                $callback($action);
            }
        }
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        if(is_null($this->queuedAction)) {
            return get_class($this);
        }

        return $this->queuedAction->getAction()->getActionClass();
    }

    /**
     * @return string
     */
    public function getQueuedActionId(): string
    {
        return $this->queuedActionId;
    }
}
