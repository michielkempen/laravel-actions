<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\QueueableAction;
use MichielKempen\LaravelActions\Tests\TestCase\PendingModel;

class DumpTheFirstParameterAction
{
    use QueueableAction;

    public function execute(PendingModel $pendingModel): void
    {
        dd($pendingModel);
    }
}
