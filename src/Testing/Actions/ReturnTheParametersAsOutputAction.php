<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use MichielKempen\LaravelActions\QueueableAction;

class ReturnTheParametersAsOutputAction
{
    use QueueableAction;

    public function execute(string $parameterA, string $parameterB): array
    {
        return [$parameterA, $parameterB];
    }
}
