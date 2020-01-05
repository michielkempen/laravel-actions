<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Callbacks;

use MichielKempen\LaravelActions\ActionChainReport;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class LogCallback
{
    public function execute(ActionChainReport $actionChainReport): void
    {
        if(! $actionChainReport->hasAction()) {
            return;
        }

        file_put_contents(
            TestCase::LOG_PATH,
            "{$actionChainReport->getAction()->getClass()} - {$actionChainReport->getAction()->getStatus()}",
            FILE_APPEND
        );
    }
}