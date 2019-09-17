<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

trait QueueableAction
{
    /**
     * @return QueuedActionProxy
     */
    public function queue()
    {
        $class = app()->makeWith(QueuedActionProxy::class, [
            'action' => $this,
        ]);

        return $class;
    }
}
