<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedActionChain extends UuidModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * @return HasMany
     */
    public function actions(): HasMany
    {
        return $this->hasMany(QueuedAction::class, 'chain_id')->orderBy('order');
    }

    /**
     * @return Collection|null
     */
    public function getActions(): ?Collection
    {
        return $this->actions;
    }

    /**
     * @return string|null
     */
    public function getModelId(): ?string
    {
        return $this->model_id;
    }

    /**
     * @return string|null
     */
    public function getModelType(): ?string
    {
        return $this->model_type;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }
}
