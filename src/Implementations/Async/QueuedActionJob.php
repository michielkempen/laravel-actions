<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MichielKempen\LaravelActions\ActionChainReport;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\ActionChainCallback;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionRepository;
use Throwable;

class QueuedActionJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    private QueuedActionRepository $queuedActionRepository;
    private string $queuedActionId;
    private ?QueuedAction $queuedAction = null;

    public function __construct(object $actionInstance, string $queuedActionId)
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);
        $this->queuedActionId = $queuedActionId;
        $this->resolveQueueableProperties($actionInstance);
    }

    private function resolveQueueableProperties(object $actionInstance): void
    {
        // Tries: Since we implement our own retry logic, we do not want Laravel to retry our failed jobs.
        $this->tries = 1;

        // Queue
        $this->queue = $actionInstance->queue ?? config('actions.default_queue');

        // Timeout
        $this->timeout = $actionInstance->timeout ?? config('actions.default_timeout');

        // Connection
        if(property_exists($actionInstance, 'connection')) {
            $this->connection = $actionInstance->connection;
        }

        // Delay
        if(property_exists($actionInstance, 'delay')) {
            $this->delay = $actionInstance->delay;
        }
    }

    public function handle(): void
    {
        $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);

        $this->queuedAction->setStartedAt(now()->subSeconds($this->delay ?? 0));
        $this->queuedAction->save();

        $this->executeAction();

        $this->queuedAction->setFinishedAt(now());
        $this->queuedAction->save();

        $this->triggerCallbacks();
    }

    private function executeAction(): void
    {
        $actionInstance = $this->queuedAction->instantiate();

        if($this->shouldSkipAction($actionInstance)) {
            $this->queuedAction->setStatus(ActionStatus::SKIPPED);
            return;
        }

        // get the maximum number of attempts specified by the user in the action class
        // if no number is specified, default to the number specified in the config file
        $maxAttempts = $actionInstance->attempts ?? config('actions.default_attempts');

        for($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // execute the action
                $output = $actionInstance->execute(...array_values($this->queuedAction->getArguments()));
                // if the action succeeds, mark the action as successful
                $this->queuedAction->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
                //and stop the execution
                return;
            } catch (Throwable $exception) {
                // if the action fails, try again
                if($attempt < $maxAttempts) {
                    continue;
                }
                // if there are no attempts left, mark the action as failed
                $this->queuedAction->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
                // and stop the execution
                return;
            }
        }
    }

    private function shouldSkipAction(object $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip($this->queuedAction->getChain());
    }

    public function failed(Exception $exception): void
    {
        $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);

        $this->queuedAction
            ->setFinishedAt(now())
            ->setStatus(ActionStatus::FAILED)
            ->setOutput($exception->getMessage());

        $this->queuedAction->save();

        $this->triggerCallbacks();

        $actionInstance = $this->queuedAction->instantiate();

        if(method_exists($actionInstance, 'failed')) {
            $actionInstance->failed($exception);
        }
    }

    private function triggerCallbacks(): void
    {
        $queuedActionChain = $this->queuedAction->getChain();

        $actionChainReport = new ActionChainReport($this->queuedAction, $queuedActionChain);

        $queuedActionChain
            ->getCallbacks()
            ->each(fn(ActionChainCallback $callback) => $callback->trigger($actionChainReport));
    }

    public function displayName(): string
    {
        if(is_null($this->queuedAction)) {
            $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);
        }

        return $this->queuedAction->getClass();
    }

    public function getQueuedActionId(): string
    {
        return $this->queuedActionId;
    }
}
