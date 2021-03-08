<?php

namespace MichielKempen\LaravelActions\Testing\Actions;

use Exception as PhpException;
use MichielKempen\LaravelActions\QueueableAction;
use MichielKempen\LaravelActions\Testing\TestCase;

class ComplexAction
{
    use QueueableAction;

    public string $queue = 'default';
    public string $name = 'custom action name';

    protected DependencyObject $dependencyObject;

    public function __construct()
    {
        $this->dependencyObject = app(DependencyObject::class);
    }

    public function execute(DataObject $dataObject): void
    {
        file_put_contents(
            TestCase::LOG_PATH,
            $dataObject->foo.' '.$this->dependencyObject->bar
        );
    }

    public function failed(PhpException $exception): void
    {
        file_put_contents(
            TestCase::LOG_PATH,
            'whoops, action failed'
        );
    }
}
