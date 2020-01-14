<?php

namespace MichielKempen\LaravelActions\Resources\ActionChain;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\Action\ActionContract;
use MichielKempen\LaravelActions\Resources\Action\QueuedAction;
use MichielKempen\LaravelActions\Resources\ActionChainCallback;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedActionChain extends UuidModel implements ActionChainContract
{
    public $timestamps = false;

    protected $dates = [
        'created_at',
    ];

    protected $casts = [
        'callbacks' => 'array',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(QueuedAction::class, 'chain_id')->orderBy('order');
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getNumberOfActions(): int
    {
        return $this
            ->getActions()
            ->count();
    }

    public function getNthAction(int $number): ?ActionContract
    {
        return $this
            ->getActions()
            ->get($number - 1);
    }

    public function getNumberOfActionsForActionClass(string $actionClass): int
    {
        return $this
            ->getActionsForActionClass($actionClass)
            ->count();
    }

    public function getActionsForActionClass(string $actionClass): Collection
    {
        return $this
            ->getActions()
            ->filter(fn(ActionContract $action) => $action->getClass() == $actionClass);
    }

    public function getNthActionForActionClass(int $number, string $actionClass): ?ActionContract
    {
        return $this
            ->getActionsForActionClass($actionClass)
            ->get($number - 1);
    }

    public function hasUnsuccessfulActionForAnyActionClassOf(array $actionClasses): bool
    {
        return $this
            ->getActions()
            ->filter(function(ActionContract $action) use ($actionClasses) {
                return in_array($action->getClass(), $actionClasses)
                    && in_array($action->getStatus(), [ActionStatus::FAILED, ActionStatus::SKIPPED]);
            })
            ->isNotEmpty();
    }

    public function isSuccessful(): bool
    {
        return $this
            ->getActions()
            ->filter(fn(ActionContract $action) => $action->getStatus() != ActionStatus::SUCCEEDED)
            ->isEmpty();
    }

    public function isFinished(): bool
    {
        return $this
            ->getActions()
            ->filter(fn(ActionContract $action) => $action->getStatus() == ActionStatus::PENDING)
            ->isEmpty();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getModelId(): ?string
    {
        return $this->model_id;
    }

    public function getModelType(): ?string
    {
        return $this->model_type;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getCallbacks(): Collection
    {
        return collect($this->callbacks)
            ->map(fn(array $serialization) => ActionChainCallback::deserialize($serialization));
    }
}
