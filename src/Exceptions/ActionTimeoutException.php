<?php

namespace MichielKempen\LaravelActions\Exceptions;

class ActionTimeoutException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Execution of action timed out.");
    }
}