<?php

namespace MichielKempen\LaravelActions\Resources\ActionChain;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MichielKempen\LaravelActions\Resources\ActionChainCallback;
use MichielKempen\LaravelActions\Support\ModelFactory;

class QueuedActionChainFactory extends ModelFactory
{
    public function name(string $name): self
    {
        return $this->setAttribute('name', $name);
    }

    public function modelId(string $modelId): self
    {
        return $this->setAttribute('model_id', $modelId);
    }

    public function modelType(string $modelType): self
    {
        return $this->setAttribute('model_type', $modelType);
    }

    public function callbacks(Collection $callbacks): self
    {
        return $this->setAttribute(
            'callbacks',
            $callbacks->map(fn(ActionChainCallback $callback) => $callback->serialize())->all(),
        );
    }

    public function createdAt(Carbon $createdAt): self
    {
        return $this->setAttribute('created_at', $createdAt);
    }

    public function attributes(): array
    {
        $numberOfCallbacks = $this->faker->numberBetween(0, 4);
        $callbacks = [];

        for($i = 0; $i < $numberOfCallbacks; $i++) {
            $callbacks[] = ['class' => $this->faker->word, 'arguments' => [$this->faker->uuid]];
        }

        return [
            'name' => $this->faker->word,
            'model_id' => $this->faker->uuid,
            'model_type' => $this->faker->slug(2),
            'callbacks' => $callbacks,
            'created_at' => now()->subMinutes($this->faker->numberBetween(0, 120)),
        ];
    }

    public function make(): QueuedActionChain
    {
        return QueuedActionChain::make($this->resolveAttributes());
    }

    public function create(): QueuedActionChain
    {
        return QueuedActionChain::create($this->resolveAttributes());
    }
}
