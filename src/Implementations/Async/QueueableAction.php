<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

trait QueueableAction
{
    /**
     * @return QueueableActionProxy
     */
    public function queue()
    {
        $class = app()->makeWith(QueueableActionProxy::class, [
            'action' => $this,
        ]);

        return $class;
    }
}
