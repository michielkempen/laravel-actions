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
use MichielKempen\LaravelActions\Events\QueuedActionUpdated;

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
        $actionChain = 'todo';

        $action->setStartedAt(now());

        $this->executeAction($actionChain, $action);

        $action->setFinishedAt(now());

        $this->queuedAction = $this->queuedActionRepository->updateQueuedAction($this->queuedAction->getId(), $action);
        event(new QueuedActionUpdated($this->queuedAction));

        $this->triggerCallbacks($actionChain);
    }

    /**
     * @param QueuedActionChain $actionChain
     * @param Action $action
     */
    private function executeAction(QueuedActionChain $actionChain, Action $action): void
    {
        if ($this->shouldSkipAction($actionChain, $action)) {
            $action->setStatus(ActionStatus::SKIPPED);
            return;
        }

        try {
            $output = $action->execute(...$action->getParameters());
        } catch (PhpException $exception) {
            $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
            return;
        }

        $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
    }

    /**
     * @param QueuedActionChain $actionChain
     * @param $actionInstance
     * @return bool
     */
    private function shouldSkipAction(QueuedActionChain $actionChain, $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip($actionChain);
    }

    /**
     * @param QueuedActionChain $actionChain
     */
    private function triggerCallbacks(QueuedActionChain $actionChain): void
    {
        foreach ($actionChain->getCallbacks() as $callback) {
            $callback($actionChain);
        }
    }

    /**
     * @param PhpException $exception
     */
    public function failed(PhpException $exception)
    {
        $queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);

        $action = $queuedAction
            ->getAction()
            ->setFinishedAt(now())
            ->setStatus(ActionStatus::FAILED)
            ->setOutput($exception->getMessage());

        $queuedAction = $this->queuedActionRepository->updateQueuedAction($this->queuedActionId, $action);
        event(new QueuedActionUpdated($queuedAction));

        $actionInstance = $action->instantiateAction();

        if(method_exists($actionInstance, 'failed')) {
            $actionInstance->failed($exception);
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
