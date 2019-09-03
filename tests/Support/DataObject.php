<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Support;

class DataObject
{
    public $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}