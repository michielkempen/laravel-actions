<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;
use MichielKempen\LaravelActions\Implementations\Sync\ChainableAction;

class ReturnTheParametersAsOutputAction
{
    use ChainableAction, QueueableAction;

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
