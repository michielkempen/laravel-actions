<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Resources\ActionChain\ActionChainContract;

trait InteractsWithActionChain
{
    protected ActionChainContract $actionChain;

    public function setActionChain(ActionChainContract $actionChain): void
    {
        $this->actionChain = $actionChain;
    }

    public function getActionChain(): ActionChainContract
    {
        return $this->actionChain;
    }
}