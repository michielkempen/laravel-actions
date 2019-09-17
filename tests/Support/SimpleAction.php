<?php

namespace MichielKempen\LaravelActions\Tests\Support;

use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;

class SimpleAction
{
    use QueueableAction;

    public function execute()
    {
        //
    }
}