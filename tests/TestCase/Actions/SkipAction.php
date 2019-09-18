<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\ActionChain;

class SkipAction
{
    /**
     * @param ActionChain $actionChain
     * @return bool
     */
    public function skip(ActionChain $actionChain) : bool
    {
        return $actionChain->hasUnsuccessfulActionForAnyActionClassOf([
            ThrowAnExceptionAction::class,
        ]);
    }

    /**
     * @return string
     */
    public function execute(): string
    {
        return "not skipped at all";
    }
}
