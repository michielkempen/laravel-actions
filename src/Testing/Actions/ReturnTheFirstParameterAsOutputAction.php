<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use MichielKempen\LaravelActions\QueueableAction;

class ReturnTheFirstParameterAsOutputAction
{
    use QueueableAction;

    public function execute(string $parameterA, string $parameterB): string
    {
        return $parameterA;
    }
}
