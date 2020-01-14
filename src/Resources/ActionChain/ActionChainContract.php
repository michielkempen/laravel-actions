<?php

namespace MichielKempen\LaravelActions\Resources\ActionChain;

use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\Action\ActionContract;

interface ActionChainContract
{
    public function getNumberOfActions(): int;
    public function getActions(): Collection;
    public function getNthAction(int $number): ?ActionContract;
    public function getNumberOfActionsForActionClass(string $actionClass): int;
    public function getActionsForActionClass(string $actionClass): Collection;
    public function getNthActionForActionClass(int $number, string $actionClass): ?ActionContract;
    public function hasUnsuccessfulActionForAnyActionClassOf(array $actionClasses): bool;
    public function isSuccessful(): bool;
    public function isFinished(): bool;
}