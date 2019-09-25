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
            $callbackInstance = app()->makeWith($callback['class'], $callback['arguments']);
            $callbackInstance->execute($actionCallback);
        }
    }
}