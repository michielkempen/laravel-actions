<?php

namespace MichielKempen\LaravelActions;

class TriggerCallbacks
{
    /**
     * @param array $callbacks
     * @param Action|null $action
     * @param ActionChain|null $actionChain
     */
    public static function execute(array $callbacks, ?Action $action, ?ActionChain $actionChain): void
    {
        if(empty($callbacks)) {
            return;
        }

        $actionCallback = new ActionCallback($action, $actionChain);

        foreach ($callbacks as $callback) {
            $class = $callback['class'];
            $arguments = $callback['arguments'];

            $callbackAction = empty($arguments) ? new $class() : new $class(...$arguments);

            $callbackAction->execute($actionCallback);
        }
    }
}