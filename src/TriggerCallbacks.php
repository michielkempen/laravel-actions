<?php

namespace MichielKempen\LaravelActions;

class TriggerCallbacks
{
    /**
     * @param array $callbacks
     * @param ActionCallback $actionCallback
     */
    public static function execute(array $callbacks, ActionCallback $actionCallback): void
    {
        if(empty($callbacks)) {
            return;
        }

        foreach ($callbacks as $callback) {
            $callbackInstance = app()->makeWith($callback['class'], $callback['arguments']);
            $callbackInstance->execute($actionCallback);
        }
    }
}