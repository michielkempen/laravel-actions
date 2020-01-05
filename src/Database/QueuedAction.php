<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedAction extends UuidModel
{
    public $timestamps = false;

    protected $casts = [
        'action' => 'array',
        'callbacks' => 'array',
    ];

    public function chain(): BelongsTo
    {
        return $this->belongsTo(QueuedActionChain::class, 'chain_id');
    }

    public function getChainId(): string
    {
        return $this->chain_id;
    }

    public function getChain(): QueuedActionChain
    {
        return $this->chain;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAction(): ?Action
    {
        if(is_null($this->action)) {
            return null;
        }

        return Action::createFromSerialization($this->action);
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }
}
