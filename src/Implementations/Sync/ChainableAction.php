<?php

namespace MichielKempen\LaravelActions\Implementations\Sync;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Action;

trait ChainableAction
{
    public function chain(string $class, array $arguments = []): ChainableActionProxy
    {
        $chainedActions = new Collection;
        $chainedActions->add(new Action(resolve($class), $arguments));

        return resolve(ChainableActionProxy::class, [
            'actionInstance' => $this,
            'chainedActions' => $chainedActions,
        ]);
    }
}
