<?php

namespace MichielKempen\LaravelActions\Tests\TestCase;

class DataObject
{
    public $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}