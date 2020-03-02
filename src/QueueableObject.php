<?php

namespace MichielKempen\LaravelActions;

interface QueueableObject
{
    public function serialize(): array;
    public static function deserialize(array $attributes): self;
}