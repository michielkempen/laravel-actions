<?php

namespace MichielKempen\LaravelActions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Action implements Arrayable
{
    /**
     * @var string
     */
    private $actionClass;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string|null
     */
    private $output;

    /**
     * @var Carbon|null
     */
    private $startedAt;

    /**
     * @var Carbon|null
     */
    private $finishedAt;

    /**
     * TaskReport constructor.
     *
     * @param string $actionClass
     * @param array $parameters
     * @param string $name
     * @param string $status
     * @param array|string|null $output
     * @param Carbon|null $startedAt
     * @param Carbon|null $finishedAt
     */
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

    /**
     * @param array $serialization
     * @return Action
     */
    public static function createFromSerialization(array $serialization): self
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

    /**
     * @param $action
     * @param array $parameters
     * @return Action
     */
    public static function createFromAction($action, array $parameters = []) : Action
    {
        $actionClass = get_class($action);
        $name = self::parseName($action);
        $status = ActionStatus::PENDING;

        return new static($actionClass, $parameters, $name, $status);
    }

    /**
     * @param $action
     * @return string
     */
    public static function parseName($action): string
    {
        if(property_exists($action, 'name')) {
            return  $action->name;
        }

        $name = class_basename($action);
        $name = Str::replaceLast('Action', '', $name);
        $name = Str::snake($name, ' ');

        return $name;
    }

    /**
     * @return string
     */
    public function getActionClass(): string
    {
        return $this->actionClass;
    }

    /**
     * @return mixed
     */
    public function instantiateAction()
    {
        return app($this->actionClass);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $status
     * @return Action
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param mixed $output
     * @return Action
     */
    public function setOutput($output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param Carbon|null $startedAt
     * @return Action
     */
    public function setStartedAt(?Carbon $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getStartedAt(): ?Carbon
    {
        return $this->startedAt;
    }

    /**
     * @param Carbon|null $finishedAt
     * @return Action
     */
    public function setFinishedAt(?Carbon $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @return Carbon|null
     */
    public function getFinishedAt(): ?Carbon
    {
        return $this->finishedAt;
    }

    /**
     * @return string|null
     */
    public function getDuration(): ?string
    {
        if(is_null($this->startedAt) || is_null($this->finishedAt)) {
            return null;
        }

        return $this->finishedAt->longAbsoluteDiffForHumans($this->startedAt);
    }

    /**
     * @return array
     */
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
