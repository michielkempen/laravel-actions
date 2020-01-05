<?php

namespace MichielKempen\LaravelActions\Implementations\Sync;

use Exception as PhpException;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChainReport;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\ActionChainCallback;

class ChainableActionProxy extends ActionProxy
{
    public function execute(...$arguments): ActionChain
    {
        $actionChain = $this->createActionChain($arguments);

        $this->triggerCallbacks(null, $actionChain);

        $actionChain->getActions()->each(function(Action $action) use ($actionChain) {
            $action->setStartedAt(now());
            $this->executeAction($action, $actionChain);
            $action->setFinishedAt(now());
            $this->triggerCallbacks($action, $actionChain);
        });

        return $actionChain;
    }

    private function createActionChain(array $arguments): ActionChain
    {
        $actionChain = new ActionChain;

        $actionChain->addAction(new Action($this->actionInstance, $arguments));

        $this->chainedActions->each(fn(Action $action) => $actionChain->addAction($action));

        return $actionChain;
    }

    private function executeAction(Action $action, ActionChain $actionChain): void
    {
        $actionInstance = $action->instantiate();

        if($this->shouldSkipAction($actionChain, $actionInstance)) {
            $action->setStatus(ActionStatus::SKIPPED);
            return;
        }

        // get the maximum number of attempts specified by the user in the action class
        // if no number is specified, default to the number specified in the config file
        $maxAttempts = $actionInstance->attempts ?? config('actions.default_attempts');

        for($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // execute the action
                $output = $actionInstance->execute(...array_values($action->getArguments()));
                // if the action succeeds, mark the action as successful
                $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
                //and stop the execution
                return;
            } catch (PhpException $exception) {
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

    private function shouldSkipAction(ActionChain $actionChain, object $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip($actionChain);
    }

    private function triggerCallbacks(?Action $action, ActionChain $actionChain): void
    {
        $actionChainReport = new ActionChainReport($action, $actionChain);

        $this->callbacks->each(fn(ActionChainCallback $callback) => $callback->trigger($actionChainReport));
    }
}