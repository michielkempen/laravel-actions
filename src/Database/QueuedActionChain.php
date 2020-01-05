<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedActionChain extends UuidModel
{
    public $timestamps = false;

    protected $dates = [
        'created_at',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(QueuedAction::class, 'chain_id')->orderBy('order');
    }

    public function getActions(): ?Collection
    {
        return $this->actions;
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
}
