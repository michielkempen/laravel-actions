<?php

namespace MichielKempen\LaravelActions\Resources;

use MichielKempen\LaravelActions\QueueableObject;

class ActionOutput implements QueueableObject
{
    public string $actionId;

    public function __construct(string $actionId)
    {
        $this->actionId = $actionId;
    }

    public function getActionId(): string
    {
        return $this->actionId;
    }

    public function serialize(): array
    {
        return [
            'actionId' => $this->actionId,
        ];
    }

    public static function deserialize(array $attributes): self
    {
        return new static($attributes['actionId']);
    }
}