<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Database\QueuedAction;
use MichielKempen\LaravelActions\Database\QueuedActionChain;

class ActionChain implements Arrayable
{
    /**
     * @var Collection
     */
    private $actions;

    /**
     * ActionChain constructor.
     */
    public function __construct()
    {
        $this->actions = new Collection;
    }

    /**
     * @param array $serialization
     * @return ActionChain
     */
    public static function createFromSerialization(array $serialization): self
    {
        $actionChain = new static;

        foreach ($serialization['actions'] as $action) {
            $actionChain->addAction(Action::createFromSerialization($action));
        }

        return $actionChain;
    }

    /**
     * @param QueuedActionChain $queuedActionChain
     * @return ActionChain
     */
    public static function createFromQueuedActionChain(QueuedActionChain $queuedActionChain): self
    {
        $actionChain = new static;

        $queuedActionChain->getActions()->each(function(QueuedAction $queuedAction) use ($actionChain) {
            $actionChain->addAction($queuedAction->getAction());
        });

        return $actionChain;
    }

    /**
     * @param Action $action
     * @return ActionChain
     */
    public function addAction(Action $action): self
    {
        $this->actions->add($action);

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfActions(): int
    {
        return $this->actions->count();
    }

    /**
     * @return Collection
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @param int $number
     * @return Action|null
     */
    public function getNthAction(int $number): ?Action
    {
        return $this->actions->get($number - 1);
    }

    /**
     * @param string $actionClass
     * @return int
     */
    public function getNumberOfActionsForActionClass(string $actionClass): int
    {
        return $this->getActionsForActionClass($actionClass)->count();
    }

    /**
     * @param string $actionClass
     * @return Collection
     */
    public function getActionsForActionClass(string $actionClass): Collection
    {
        return $this->actions->filter(function(Action $action) use ($actionClass) {
            return $action->getActionClass() == $actionClass;
        });
    }

    /**
     * @param int $number
     * @param string $actionClass
     * @return Action|null
     */
    public function getNthActionForActionClass(int $number, string $actionClass): ?Action
    {
        return $this->getActionsForActionClass($actionClass)->get($number - 1);
    }

    /**
     * @param array $actionClasses
     * @return bool
     */
    public function hasUnsuccessfulActionForAnyActionClassOf(array $actionClasses): bool
    {
        return $this->actions->filter(function(Action $action) use ($actionClasses) {
            return in_array($action->getActionClass(), $actionClasses)
                && in_array($action->getStatus(), [ActionStatus::FAILED, ActionStatus::SKIPPED]);
        })->isNotEmpty();
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->actions->filter(function(Action $action) {
            return $action->getStatus() != ActionStatus::SUCCEEDED;
        })->isEmpty();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccessful(),
            'actions' => $this->getActions()->toArray(),
        ];
    }
}
