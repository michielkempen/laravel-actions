<?php

namespace MichielKempen\LaravelActions\Testing\Callbacks;

use MichielKempen\LaravelActions\Resources\ActionChainReport;

class ReturnStatusCallback
{
    public function execute(ActionChainReport $actionChainReport): string
    {
        if(! $actionChainReport->hasAction()) {
            return null;
        }

        return $actionChainReport->getAction()->getStatus();
    }
}
