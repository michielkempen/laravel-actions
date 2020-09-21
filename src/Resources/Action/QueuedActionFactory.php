<?php

namespace MichielKempen\LaravelActions\Resources\Action;

use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChain;
use MichielKempen\LaravelActions\Resources\ActionChain\QueuedActionChainFactory;
use MichielKempen\LaravelActions\Resources\ActionStatus;
use MichielKempen\LaravelActions\Support\ModelFactory;

class QueuedActionFactory extends ModelFactory
{
    public function id(string $id): self
    {
        return $this->setAttribute('id', $id);
    }

    public function chain(QueuedActionChain $chain): self
    {
        return $this->setAttribute('chain_id', $chain->getId());
    }

    public function order(int $order): self
    {
        return $this->setAttribute('order', $order);
    }

    public function class(string $class): self
    {
        return $this->setAttribute('class', $class);
    }

    public function arguments(array $arguments): self
    {
        return $this->setAttribute('arguments', $arguments);
    }

    public function name(string $name): self
    {
        return $this->setAttribute('name', $name);
    }

    public function status(string $status): self
    {
        return $this->setAttribute('status', $status);
    }

    public function output($output): self
    {
        return $this->setAttribute('output', $output);
    }

    public function started_at(?Carbon $startedAt): self
    {
        return $this->setAttribute('started_at', $startedAt);
    }

    public function finished_at(?Carbon $finishedAt): self
    {
        return $this->setAttribute('finished_at', $finishedAt);
    }

    public function attributes(): array
    {
        return [
            'id' => $this->faker->uuid,
            'chain_id' => fn() => QueuedActionChainFactory::new()->create()->getId(),
            'order' => $this->faker->numberBetween(0, 10),
            'class' => $this->faker->word,
            'arguments' => [$this->faker->uuid],
            'name' => $this->faker->word,
            'status' => $this->faker->randomElement(ActionStatus::all()),
            'output' => $this->faker->boolean ? null : ['hello' => 'world'],
            'started_at' => $this->faker->boolean ? null : Carbon::parse($this->faker->dateTime),
            'finished_at' => $this->faker->boolean ? null : Carbon::parse($this->faker->dateTime),
        ];
    }

    public function make(): QueuedAction
    {
        return QueuedAction::make($this->resolveAttributes());
    }

    public function create(): QueuedAction
    {
        return QueuedAction::create($this->resolveAttributes());
    }
}
