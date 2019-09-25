<?php

namespace MichielKempen\LaravelActions\Implementations\Sync;

use Exception as PhpException;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelActions\ActionChain;
use MichielKempen\LaravelActions\ActionProxy;
use MichielKempen\LaravelActions\ActionStatus;
use MichielKempen\LaravelActions\TriggerCallbacks;

class ChainableActionProxy extends ActionProxy
{
    /**
     * @param mixed ...$parameters
     * @return ActionChain
     */
    public function execute(...$parameters)
    {
        $actionChain = $this->createActionChain($parameters);

        $this->triggerCallbacks(null, $actionChain);

        $actionChain->getActions()->each(function(Action $action) use ($actionChain) {

            $action->setStartedAt(now());

            $this->executeAction($actionChain, $action);

            $action->setFinishedAt(now());

            $this->triggerCallbacks($action, $actionChain);

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

        $action = Action::createFromAction($this->action, $parameters);
        $actionChain->addAction($action);

        foreach ($this->chainedActions as $actionClass) {
            $action = Action::createFromAction(app($actionClass), $parameters);
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
    private function shouldSkipAction(ActionChain $report, object $actionInstance): bool
    {
        if(! method_exists($actionInstance, 'skip')) {
            return false;
        }

        return $actionInstance->skip($report);
    }

    /**
     * @param ActionChain $actionChain
     * @param Action|null $action
     */
    private function triggerCallbacks(?Action $action, ActionChain $actionChain): void
    {
        TriggerCallbacks::execute($this->callbacks, $action, $actionChain);
    }
}