<?php

namespace MichielKempen\LaravelActions\Tests\TestCase;

use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;

class SimpleAction
{
    use QueueableAction;

    public function execute()
    {
        //
    }
}