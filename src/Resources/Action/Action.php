<?php

namespace MichielKempen\LaravelActions\Resources\Action;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MichielKempen\LaravelActions\Resources\ActionStatus;

class Action implements ActionContract
{
    private string $class;
    private array $arguments;
    private string $name;
    private ?string $uuid;
    private string $status;
    private $output;
    private ?Carbon $startedAt;
    private ?Carbon $finishedAt;

    public function __construct(object $action, array $arguments, ?string $name, ?string $uuid = null)
    {
        $this->class = get_class($action);
        $this->arguments = $arguments;
        $this->name = $name === null ? $this->parseName($action) : $name;
        $this->uuid = $uuid;
        $this->status = ActionStatus::PENDING;
        $this->output = null;
        $this->startedAt = null;
        $this->finishedAt = null;
    }

    private function parseName(object $action): string
    {
        if(property_exists($action, 'name')) {
            return  $action->name;
        }

        $name = class_basename($action);
        $name = Str::replaceLast('Action', '', $name);
        $name = Str::snake($name, ' ');

        return $name;
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

    public function getId(): ?string
    {
        return $this->uuid;
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
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getStartedAt(): ?Carbon
    {
        return $this->startedAt;
    }

    public function setFinishedAt(?Carbon $finishedAt): ActionContract
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getFinishedAt(): ?Carbon
    {
        return $this->finishedAt;
    }

    public function getDuration(): ?string
    {
        if(is_null($this->startedAt) || is_null($this->finishedAt)) {
            return null;
        }

        return $this->finishedAt->longAbsoluteDiffForHumans($this->startedAt);
    }
}
