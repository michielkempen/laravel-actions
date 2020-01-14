<?php

namespace MichielKempen\LaravelActions;

use MichielKempen\LaravelActions\Resources\ActionChainReport;

trait InteractsWithActionChainReport
{
    protected ActionChainReport $actionChainReport;

    public function setActionChainReport(ActionChainReport $actionChain): void
    {
        $this->actionChainReport = $actionChain;
    }

    public function getActionChainReport(): ActionChainReport
    {
        return $this->actionChainReport;
    }
}