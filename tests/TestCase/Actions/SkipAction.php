<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use MichielKempen\LaravelActions\QueueableAction;
use MichielKempen\LaravelActions\Resources\ActionChain\ActionChainContract;

class SkipAction
{
    use QueueableAction;

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
