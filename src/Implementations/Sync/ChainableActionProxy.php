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
        $actionChain = $this->createActionChain($parameters);

        $actionChain->getActions()->each(function(Action $action) use ($actionChain) {

            $action->setStartedAt(now());

            $this->executeAction($actionChain, $action);

            $action->setFinishedAt(now());

            $this->triggerCallbacks($actionChain);

        });

        return $actionChain;
    }

    /**
     * @param array $parameters
     * @return ActionChain
     */
    private function createActionChain(array $parameters): ActionChain
    {
        $actionChain = new ActionChain;

        $action = Action::createFromAction(get_class($this->action), $parameters);
        $actionChain->addAction($action);

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction($actionClass, $parameters);
            $actionChain->addAction($action);
        }

        return $actionChain;
    }

    /**
     * @param ActionChain $actionChain
     * @param Action $action
     */
    private function executeAction(ActionChain $actionChain, Action $action): void
    {
        $actionInstance = $action->instantiateAction();

        if($this->shouldSkipAction($actionChain, $actionInstance)) {
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
     * @param ActionChain $report
     * @param $actionInstance
     * @return bool
     */
    private function shouldSkipAction(ActionChain $report, $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip($report);
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