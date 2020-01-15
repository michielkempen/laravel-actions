<?php

namespace MichielKempen\LaravelActions\Exceptions;

class EmptyActionChainException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Cannot execute empty action chain.", 500);
    }
}