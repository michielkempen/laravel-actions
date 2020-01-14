<?php

namespace MichielKempen\LaravelActions\Resources;

use JsonSerializable;

class ActionOutput implements JsonSerializable
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

    public function jsonSerialize()
    {
        return [
            'type' => 'action_output',
            'action_id' => $this->actionId,
        ];
    }
}