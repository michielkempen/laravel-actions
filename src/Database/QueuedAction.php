<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedAction extends UuidModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = [
        'action' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function chain(): BelongsTo
    {
        return $this->belongsTo(QueuedActionChain::class, 'chain_id');
    }

    /**
     * @return string|null
     */
    public function getChainId(): ?string
    {
        return $this->chain_id;
    }

    /**
     * @return bool
     */
    public function hasChain(): bool
    {
        return ! is_null($this->chain_id);
    }

    /**
     * @return QueuedActionChain|null
     */
    public function getChain(): ?QueuedActionChain
    {
        return $this->chain;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getModelId(): string
    {
        return $this->model_id;
    }

    /**
     * @return string
     */
    public function getModelType(): string
    {
        return $this->model_type;
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        if(is_null($this->action)) {
            return null;
        }

        return Action::createFromSerialization($this->action);
    }
}
