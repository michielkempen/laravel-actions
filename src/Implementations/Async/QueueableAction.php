<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

trait QueueableAction
{
    public function queue(): QueueableActionProxy
    {
        return resolve(QueueableActionProxy::class, [
            'actionInstance' => $this,
        ]);
    }
}
