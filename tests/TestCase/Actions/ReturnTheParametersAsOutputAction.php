<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\QueueableAction;

class ReturnTheParametersAsOutputAction
{
    use QueueableAction;

    /**
     * @param string $parameterA
     * @param string $parameterB
     * @return array
     */
    public function execute(string $parameterA, string $parameterB): array
    {
        return [$parameterA, $parameterB];
    }
}
