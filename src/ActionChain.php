<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;

class ActionChain implements Arrayable
{
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new Collection;
    }

    public static function createFromSerialization(array $serialization): ActionChain
    {
        $actionChain = new static;

        foreach ($serialization['actions'] as $action) {
            $actionChain->addAction(Action::createFromSerialization($action));
        }

        return $actionChain;
    }

    public static function createFromQueuedActionChain(QueuedActionChain $queuedActionChain): ActionChain
    {
        $actionChain = new static;

        $queuedActionChain->getActions()->each(function(QueuedAction $queuedAction) use ($actionChain) {
            $actionChain->addAction($queuedAction->getAction());
        });

        return $actionChain;
    }

    public function addAction(Action $action): ActionChain
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
        return $this->actions->filter(function(Action $action) use ($actionClass) {
            return $action->getActionClass() == $actionClass;
        });
    }

    public function getNthActionForActionClass(int $number, string $actionClass): ?Action
    {
        return $this->getActionsForActionClass($actionClass)->get($number - 1);
    }

    public function hasUnsuccessfulActionForAnyActionClassOf(array $actionClasses): bool
    {
        return $this->actions->filter(function(Action $action) use ($actionClasses) {
            return in_array($action->getActionClass(), $actionClasses)
                && in_array($action->getStatus(), [ActionStatus::FAILED, ActionStatus::SKIPPED]);
        })->isNotEmpty();
    }

    public function isSuccessful(): bool
    {
        return $this->actions->filter(function(Action $action) {
            return $action->getStatus() != ActionStatus::SUCCEEDED;
        })->isEmpty();
    }

    public function isFinished(): bool
    {
        return $this->actions->filter(function(Action $action) {
            return $action->getStatus() == ActionStatus::PENDING;
        })->isEmpty();
    }

    public function toArray(): array
    {
        return [
            'success' => $this->isSuccessful(),
            'finished' => $this->isFinished(),
            'actions' => $this->getActions()->toArray(),
        ];
    }
}
