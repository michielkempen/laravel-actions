<?php

namespace MichielKempen\LaravelActions;

use Exception;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Exceptions\EmptyActionChainException;
use MichielKempen\LaravelActions\Resources\Action\Action;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChain;
use MichielKempen\LaravelActions\Resources\ActionChainCallback;
use MichielKempen\LaravelActions\Resources\ActionChainReport;
use MichielKempen\LaravelActions\Resources\ActionOutput;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Resources\QueueableActionChainProxy;

class QueueableActionChain
{
    private Collection $actions;
    private Collection $callbacks;

    public function __construct(Collection $actions = null)
    {
        $this->actions = $actions ?? new Collection;
        $this->callbacks = new Collection;
    }

    public function queue(): QueueableActionChainProxy
    {
        return new QueueableActionChainProxy($this->actions, $this->callbacks);
    }

    public function addAction(
        string $class, array $arguments = [], ?string $name = null, ?string $id = null
    ): QueueableActionChain
    {
        $this->actions->add(new Action(resolve($class), $arguments, $name, $id));
        return $this;
    }

    public function withCallback(string $class, array $arguments = []): QueueableActionChain
    {
        $this->callbacks->add(new ActionChainCallback($class, $arguments));
        return $this;
    }

    public function execute()
    {
        if($this->actions->isEmpty()) {
            throw new EmptyActionChainException;
        }

        $actionChain = $this->createActionChain();

        $this->triggerCallbacks(null, $actionChain);

        $actionChain->getActions()->each(function(Action $action) use ($actionChain) {
            $action->setStartedAt(now());
            $this->executeAction($action, $actionChain);
            $action->setFinishedAt(now());
            $this->triggerCallbacks($action, $actionChain);
        });

        return $actionChain;
    }

    private function createActionChain(): ActionChain
    {
        $actionChain = new ActionChain;

        $this->actions->each(fn(Action $action) => $actionChain->addAction($action));

        return $actionChain;
    }

    private function executeAction(Action $action, ActionChain $actionChain): void
    {
        $actionInstance = $action->instantiate();

        if($this->actionInteractsWithActionChain($actionInstance)) {
            $actionInstance->setActionChain($actionChain);
        }

        if($this->shouldSkipAction($actionInstance)) {
            $action->setStatus(ActionStatus::SKIPPED);
            return;
        }

        $arguments = $this->resolveArguments($action, $actionChain);

        // get the maximum number of attempts specified by the user in the action class
        // if no number is specified, default to the number specified in the config file
        $maxAttempts = $actionInstance->attempts ?? config('actions.default_attempts');

        for($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // execute the action
                $output = $actionInstance->execute(...$arguments);
                // if the action succeeds, mark the action as successful
                $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
                //and stop the execution
                return;
            } catch (Exception $exception) {
                // if the action fails, try again
                if($attempt < $maxAttempts) {
                    continue;
                }
                // if there are no attempts left, mark the action as failed
                $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
                // and stop the execution
                return;
            }
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

    private function resolveArguments(Action $action, ActionChain $actionChain): array
    {
        $arguments = array_values($action->getArguments());

        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ActionOutput) {
                $actionId = $argument->getActionId();
                $actions = $actionChain->getActions();
                $action = $actions->first(fn(Action $action) => $action->getId() === $actionId);
                $arguments[$index] = $action !== null ? $action->getOutput() : null;
            }
        }

        return $arguments;
    }

    private function triggerCallbacks(?Action $action, ActionChain $actionChain): void
    {
        $actionChainReport = new ActionChainReport($action, $actionChain);

        $this->callbacks->each(fn(ActionChainCallback $callback) => $callback->trigger($actionChainReport));
    }
}