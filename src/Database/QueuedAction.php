<?php

namespace MichielKempen\LaravelActions\Database;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MichielKempen\LaravelActions\ActionContract;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedAction extends UuidModel implements ActionContract
{
    public $timestamps = false;

    protected $dates = [
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'output' => 'json',
        'arguments' => 'array',
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

    public function getClass(): string
    {
        return $this->class;
    }

    public function instantiate(): object
    {
        return app($this->class);
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setStatus(string $status): ActionContract
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setOutput($output): ActionContract
    {
        $this->output = $output;
        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setStartedAt(?Carbon $startedAt): ActionContract
    {
        $this->started_at = $startedAt;
        return $this;
    }

    public function getStartedAt(): ?Carbon
    {
        return $this->started_at;
    }

    public function setFinishedAt(?Carbon $finishedAt): ActionContract
    {
        $this->finished_at = $finishedAt;
        return $this;
    }

    public function getFinishedAt(): ?Carbon
    {
        return $this->finished_at;
    }

    public function getDuration(): ?string
    {
        if(is_null($this->started_at) || is_null($this->finished_at)) {
            return null;
        }

        return $this->finished_at->longAbsoluteDiffForHumans($this->started_at);
    }
}
