<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\ActionChainContract;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;
use MichielKempen\LaravelActions\Implementations\Sync\ChainableAction;

class SkipAction
{
    use QueueableAction, ChainableAction;

    /**
     * @param ActionChainContract $actionChain
     * @return bool
     */
    public function skip(ActionChainContract $actionChain) : bool
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
