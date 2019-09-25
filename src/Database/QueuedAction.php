<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelUuidModel\UuidModel;
use Opis\Closure\SerializableClosure;

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
     * @return string
     */
    public function getChainId(): string
    {
        return $this->chain_id;
    }

    /**
     * @return QueuedActionChain
     */
    public function getChain(): QueuedActionChain
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
    public function getStatus(): string
    {
        return $this->status;
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

    /**
     * @param $callbacks
     */
    public function setCallbacksAttribute(array $callbacks)
    {
        $result = array_map(function(SerializableClosure $serializableClosure) {
            return serialize($serializableClosure);
        }, $callbacks);

        $this->attributes['callbacks'] = json_encode($result);
    }

    /**
     * @return array
     */
    public function getCallbacksAttribute(): array
    {
        $result = json_decode($this->attributes['callbacks']);

        $result = array_map(function(string $serializedClosure) {
            return \Opis\Closure\unserialize($serializedClosure);
        }, $result);

        return $result;
    }

    /**
     * @return array|null
     */
    public function getCallbacks(): ?array
    {
        return $this->callbacks;
    }
}
