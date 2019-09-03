<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Support;

use MichielKempen\LaravelQueueableActions\QueueableAction;

class SimpleAction
{
    use QueueableAction;

    public function execute()
    {
        //
    }
}