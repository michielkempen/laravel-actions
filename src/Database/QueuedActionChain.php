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
     * @var array
     */
    protected $casts = [
        'callbacks' => 'array',
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
     * @return array|null
     */
    public function getCallbacks(): ?array
    {
        return $this->callbacks;
    }
}
