<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use MichielKempen\LaravelActions\QueueableObject;

class DataObject implements QueueableObject
{
    public string $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public function serialize(): array
    {
        return [
            'foo' => $this->foo,
        ];
    }

    public static function deserialize(array $attributes): QueueableObject
    {
        return new static($attributes['foo']);
    }
}
