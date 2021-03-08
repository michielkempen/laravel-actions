<?php

namespace MichielKempen\LaravelActions\Testing\Callbacks;

use MichielKempen\LaravelActions\InteractsWithActionChainReport;
use MichielKempen\LaravelActions\Testing\TestCase;

class LogCallback
{
    use InteractsWithActionChainReport;

    public function execute(): void
    {
        if(! $this->actionChainReport->hasAction()) {
            return;
        }

        file_put_contents(
            TestCase::LOG_PATH,
            "{$this->actionChainReport->getAction()->getClass()} - {$this->actionChainReport->getAction()->getStatus()}",
            FILE_APPEND
        );
    }
}
