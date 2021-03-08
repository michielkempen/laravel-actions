<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use MichielKempen\LaravelActions\QueueableAction;
use MichielKempen\LaravelActions\Testing\PendingModel;

class DumpTheFirstParameterAction
{
    use QueueableAction;

    public function execute(PendingModel $pendingModel): void
    {
        dd($pendingModel);
    }
}
