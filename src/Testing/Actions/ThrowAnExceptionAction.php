<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use Exception as PhpException;
use MichielKempen\LaravelActions\QueueableAction;

class ThrowAnExceptionAction
{
    use QueueableAction;

    public function execute(): void
    {
        throw new PhpException("Let's break all the things!", 500);
    }
}
