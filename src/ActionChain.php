<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Support\Collection;

class ActionChain implements ActionChainContract
{
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new Collection;
    }

    public function addAction(ActionContract $action): ActionChain
    {
        $this->actions->add($action);

        return $this;
    }

    public function getNumberOfActions(): int
    {
        return $this->actions->count();
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getNthAction(int $number): ?Action
    {
        return $this->actions->get($number - 1);
    }

    public function getNumberOfActionsForActionClass(string $actionClass): int
    {
        return $this->getActionsForActionClass($actionClass)->count();
    }

    public function getActionsForActionClass(string $actionClass): Collection
    {
        return $this->actions->filter(fn(ActionContract $action) => $action->getClass() == $actionClass);
    }

    public function getNthActionForActionClass(int $number, string $actionClass): ?Action
    {
        return $this->getActionsForActionClass($actionClass)->get($number - 1);
    }

    public function hasUnsuccessfulActionForAnyActionClassOf(array $actionClasses): bool
    {
        return $this->actions
            ->filter(function(ActionContract $action) use ($actionClasses) {
                return in_array($action->getClass(), $actionClasses)
                    && in_array($action->getStatus(), [ActionStatus::FAILED, ActionStatus::SKIPPED]);
            })
            ->isNotEmpty();
    }

    public function isSuccessful(): bool
    {
        return $this->actions
            ->filter(fn(ActionContract $action) => $action->getStatus() != ActionStatus::SUCCEEDED)
            ->isEmpty();
    }

    public function isFinished(): bool
    {
        return $this->actions
            ->filter(fn(ActionContract $action) => $action->getStatus() == ActionStatus::PENDING)
            ->isEmpty();
    }
}
