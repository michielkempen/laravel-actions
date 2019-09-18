<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedActionChain extends UuidModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

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
}
