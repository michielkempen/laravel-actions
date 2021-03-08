<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use MichielKempen\LaravelActions\InteractsWithActionChain;
use MichielKempen\LaravelActions\QueueableAction;

class SkipAction
{
    use QueueableAction, InteractsWithActionChain;

    public function skip() : bool
    {
        return $this->actionChain->hasUnsuccessfulActionForAnyActionClassOf([
            ThrowAnExceptionAction::class,
        ]);
    }

    public function execute(): string
    {
        return "not skipped at all";
    }
}
