<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use Exception as PhpException;
use MichielKempen\LaravelActions\QueueableAction;

class ThrowAnExceptionAction
{
    use QueueableAction;

    /**
     * @throws PhpException
     */
    public function execute(): void
    {
        throw new PhpException("Let's break all the things!", 500);
    }
}
