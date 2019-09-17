<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Exception as PhpException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionStatus;
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
        $queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);
        $action = $queuedAction->getAction();
        $action = app($action->getActionClass());

        $action->setStartedAt(now());

        if($this->shouldSkipAction($actionChain, $action)) {
            $action->setStatus(ActionStatus::SKIPPED);
        } else {
            try {
                $output = $action->execute(...$action->getParameters());
                $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
            } catch (PhpException $exception) {
                $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
            }
        }

        $action->setFinishedAt(now());

        $queuedAction = $this->queuedActionRepository->updateQueuedAction($queuedAction->getId(), $action);
        event(new QueuedActionUpdated($queuedAction));

        $this->triggerCallbacks($actionChain);
    }

    /**
     * @param ActionChain $actionChain
     * @param $action
     * @return bool
     */
    private function shouldSkipAction(ActionChain $actionChain, $action): bool
    {
        if(! method_exists($action, 'skip')) {
            return false;
        }

        return $action->skip($actionChain);
    }

    /**
     * @param ActionChain $actionChain
     */
    private function triggerCallbacks(ActionChain $actionChain): void
    {
        foreach ($this->callbacks as $callback) {
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

        $action = app($action->getActionClass());

        if(method_exists($action, 'failed')) {
            $action->failed($exception);
        }
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        return $this->actionClass;
    }

    /**
     * @return string
     */
    public function getQueuedActionId(): string
    {
        return $this->queuedActionId;
    }
}
