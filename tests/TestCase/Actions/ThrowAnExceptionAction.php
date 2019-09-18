<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use Exception as PhpException;

class ThrowAnExceptionAction
{
    /**
     * @throws PhpException
     */
    public function execute(): void
    {
        throw new PhpException("Let's break all the things!", 500);
    }
}
