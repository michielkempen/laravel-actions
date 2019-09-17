<?php

namespace MichielKempen\LaravelActions\Implementations\Sync;

use Exception as PhpException;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\ActionStatus;

class ChainableActionProxy extends ActionProxy
{
    /**
     * @param mixed ...$parameters
     * @return ActionChain
     */
    public function execute(...$parameters)
    {
        $actionChain = new ActionChain;

        $action = $this->createAction($actionChain, $this->action);
        $this->executeAction($actionChain, $action);

        foreach ($this->chainedActions as $actionClass) {
            $action = app($actionClass);
            $action = $this->createAction($actionChain, $action);
            $this->executeAction($actionChain, $action);
        }

        return $actionChain;
    }

    /**
     * @param ActionChain $actionChain
     * @param $action
     * @return Action
     */
    private function createAction(ActionChain $actionChain, $action): Action
    {
        $action = Action::createFromAction($action);

        $actionChain->addAction($action);

        return $action;
    }

    /**
     * @param ActionChain $actionChain
     * @param Action $action
     */
    private function executeAction(ActionChain $actionChain, Action $action): void
    {
        $actionInstance = app($action->getActionClass());

        $action->setStartedAt(now());

        if($this->shouldSkipAction($actionChain, $actionInstance)) {
            $action->setStatus(ActionStatus::SKIPPED);
        } else {
            try {
                $output = $actionInstance->execute(...$action->getParameters());
                $action->setStatus(ActionStatus::SUCCEEDED)->setOutput($output);
            } catch (PhpException $exception) {
                $action->setStatus(ActionStatus::FAILED)->setOutput($exception->getMessage());
            }
        }

        $action->setFinishedAt(now());

        $this->triggerCallbacks($actionChain);
    }

    /**
     * @param ActionChain $report
     * @param $action
     * @return bool
     */
    private function shouldSkipAction(ActionChain $report, $action): bool
    {
        if(! method_exists($action, 'skip')) {
            return false;
        }

        return $action->skip($report);
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
}