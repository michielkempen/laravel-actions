<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Resources\QueueableActionProxy;

trait QueueableAction
{
    public function queue(): QueueableActionProxy
    {
        return resolve(QueueableActionProxy::class, [
            'actionInstance' => $this,
        ]);
    }
}
