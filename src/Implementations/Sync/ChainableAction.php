<?php

namespace MichielKempen\LaravelActions\Implementations\Sync;

trait ChainableAction
{
    /**
     * @param array $actions
     * @return ChainableActionProxy
     */
    public function chain(array $actions)
    {
        $class = app()->makeWith(ChainableActionProxy::class, [
            'action' => $this,
            'chainedActions' => $actions,
        ]);

        return $class;
    }
}
