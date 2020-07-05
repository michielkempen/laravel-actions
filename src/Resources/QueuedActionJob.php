<?php

namespace MichielKempen\LaravelActions\Resources;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MichielKempen\LaravelActions\Exceptions\ActionTimeoutException;
use MichielKempen\LaravelActions\InteractsWithActionChain;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\Action\QueuedActionRepository;
use Spatie\Async\Pool;
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
        // Since we implement our own retry logic, we do not want Laravel to retry our failed jobs.
        $this->tries = 1;
        // Connection
        $this->connection = $actionInstance->connection ?? config('actions.default_connection');
        // Queue
        $this->queue = $actionInstance->queue ?? config('actions.default_queue');
        // The timeout is specified for a single action so we have to multiply it with the maximum number of executions
        // to calculate the timeout of the entire job.
        $maxAttempts = $actionInstance->attempts ?? config('actions.default_attempts');
        $this->timeout = ($actionInstance->timeout ?? config('actions.default_timeout')) * $maxAttempts;
        // Delay
        $this->delay = $actionInstance->delay ?? 0;
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

        if($this->actionInteractsWithActionChain($actionInstance)) {
            $actionInstance->setActionChain($this->queuedAction->getChain());
        }

        if($this->shouldSkipAction($actionInstance)) {
            $this->queuedAction->setStatus(ActionStatus::SKIPPED);
            return;
        }

        $arguments = $this->resolveArguments();
        $maxAttempts = $actionInstance->attempts ?? config('actions.default_attempts');
        $timeout = $actionInstance->timeout ?? config('actions.default_timeout');
        $success = false;

        for($attempt = 1; $attempt <= $maxAttempts && !$success; $attempt++) {
            $pool = Pool::create()->timeout($timeout);

            $pool
                ->add(new ExecuteActionAsynchronous($actionInstance, $arguments))
                ->then(function ($output) use (&$success) {
                    $this->queuedAction->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
                    $success = true;
                })
                ->catch(function (Throwable $exception) use ($attempt, $maxAttempts) {
                    if($attempt == $maxAttempts) {
                        $this->queuedAction->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
                    }
                    $this->cleanupActionInstance($exception);
                })
                ->timeout(function () use ($attempt, $maxAttempts) {
                    $exception = new ActionTimeoutException;
                    if($attempt == $maxAttempts) {
                        $this->queuedAction->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
                    }
                    $this->cleanupActionInstance($exception);
                });

            $pool->wait();
        }
    }

    private function actionInteractsWithActionChain(object $actionInstance): bool
    {
        return in_array(InteractsWithActionChain::class, class_uses($actionInstance));
    }

    private function shouldSkipAction(object $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip();
    }

    private function resolveArguments(): array
    {
        $arguments = array_values($this->queuedAction->getArguments());

        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ActionOutput) {
                $actionId = $argument->getActionId();
                $actions = $this->queuedAction->getChain()->getActions();
                $action = $actions->first(fn(QueuedAction $queuedAction) => $queuedAction->getId() === $actionId);
                $arguments[$index] = $action !== null ? $action->getOutput() : null;
            }
        }

        return $arguments;
    }

    public function failed(Throwable $exception): void
    {
        $this->queuedAction = $this->queuedActionRepository->getQueuedActionOrFail($this->queuedActionId);
        $this->queuedAction->setFinishedAt(now())->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
        $this->queuedAction->save();

        $this->triggerCallbacks();
        $this->cleanupActionInstance($exception);
    }

    private function cleanupActionInstance(Throwable $exception): void
    {
        $actionInstance = $this->queuedAction->instantiate();

        if (method_exists($actionInstance, 'failed')) {
            $actionInstance->failed($exception, ...$this->resolveArguments());
        }
    }

    private function triggerCallbacks(): void
    {
        $this->queuedAction->refresh();

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
