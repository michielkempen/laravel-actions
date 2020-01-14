<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\QueueableAction;

class ReturnTheFirstParameterAsOutputAction
{
    use QueueableAction;

    /**
     * @param string $parameterA
     * @param string $parameterB
     * @return string
     */
    public function execute(string $parameterA, string $parameterB): string
    {
        return $parameterA;
    }
}
