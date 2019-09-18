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
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
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
     * @return mixed
     */
    public function getCallbacksAttribute()
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
