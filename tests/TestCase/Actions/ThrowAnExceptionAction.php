<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use Exception as PhpException;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;
use MichielKempen\LaravelActions\Implementations\Sync\ChainableAction;

class ThrowAnExceptionAction
{
    use QueueableAction, ChainableAction;

    /**
     * @throws PhpException
     */
    public function execute(): void
    {
        throw new PhpException("Let's break all the things!", 500);
    }
}
