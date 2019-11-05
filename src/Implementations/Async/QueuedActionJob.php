<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionCallback;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use MichielKempen\LaravelActions\TriggerCallbacks;
use Throwable;

class QueuedActionJob implements ShouldQueue
{
    use Dispatchable, Queueable;

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
     * @param object $action
     * @param string $queuedActionId
     */
    public function __construct(object $action, string $queuedActionId)
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionId = $queuedActionId;
        $this->resolveQueueableProperties($action);
    }

    /**
     * @param object $action
     */
    private function resolveQueueableProperties(object $action): void
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

        $action->setStartedAt(now()->subSeconds($this->delay ?? 0));

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

        if($this->shouldSkipAction($actionInstance)) {
            $action->setStatus(ActionStatus::SKIPPED);
            return;
        }

        try {
            $output = $actionInstance->execute(...$action->getParameters());
        } catch (Throwable $exception) {
            $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
            return;
        }

        $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
    }

    /**
     * @param object $actionInstance
     * @return bool
     */
    private function shouldSkipAction(object $actionInstance): bool
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
        $action = $this->queuedAction->getAction();
        $queuedActionChain = $this->queuedAction->getChain();
        $actionChain = ActionChain::createFromQueuedActionChain($queuedActionChain);

        $actionCallback = new ActionCallback($action, $actionChain, $queuedActionChain);

        TriggerCallbacks::execute($this->queuedAction->getCallbacks(), $actionCallback);
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        if(is_null($this->queuedAction)) {
            $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);
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
