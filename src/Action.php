<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Action implements Arrayable
{
    private string $actionClass;
    private array $parameters;
    private string $name;
    private string $status;
    private ?string $output;
    private ?Carbon $startedAt;
    private ?Carbon $finishedAt;

    public function __construct(
        string $actionClass, array $parameters, string $name, string $status, $output = null, ?Carbon $startedAt = null,
        ?Carbon $finishedAt = null
    )
    {
        $this->actionClass = $actionClass;
        $this->parameters = $parameters;
        $this->name = $name;
        $this->status = $status;
        $this->output = $output;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
    }

    public static function createFromSerialization(array $serialization): Action
    {
        $startedAt = $serialization['started_at'];
        $finishedAt = $serialization['finished_at'];

        return new static(
            $serialization['action_class'],
            $serialization['parameters'],
            $serialization['name'],
            $serialization['status'],
            $serialization['output'],
            is_null($startedAt) ? null : Carbon::parse($startedAt),
            is_null($finishedAt) ? null : Carbon::parse($finishedAt)
        );
    }

    public static function createFromAction(object $action, array $parameters = []): Action
    {
        $actionClass = get_class($action);
        $name = self::parseName($action);
        $status = ActionStatus::PENDING;

        return new static($actionClass, $parameters, $name, $status);
    }

    public static function parseName(object $action): string
    {
        if(property_exists($action, 'name')) {
            return  $action->name;
        }

        $name = class_basename($action);
        $name = Str::replaceLast('Action', '', $name);
        $name = Str::snake($name, ' ');

        return $name;
    }

    public function getActionClass(): string
    {
        return $this->actionClass;
    }

    public function instantiateAction(): object
    {
        return app($this->actionClass);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setStatus(string $status): Action
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setOutput($output): Action
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setStartedAt(?Carbon $startedAt): Action
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getStartedAt(): ?Carbon
    {
        return $this->startedAt;
    }

    public function setFinishedAt(?Carbon $finishedAt): Action
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

    public function toArray(): array
    {
        return [
            'action_class' => $this->actionClass,
            'parameters' => $this->parameters,
            'name' => $this->name,
            'status' => $this->status,
            'output' => $this->output,
            'started_at' => is_null($this->startedAt) ? null : $this->startedAt->toIso8601String(),
            'finished_at' => is_null($this->finishedAt) ? null : $this->finishedAt->toIso8601String(),
            'duration' => $this->getDuration(),
        ];
    }
}
