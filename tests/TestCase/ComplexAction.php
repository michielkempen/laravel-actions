<?php

namespace MichielKempen\LaravelActions\Tests\TestCase;

use Exception as PhpException;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;

class ComplexAction
{
    use QueueableAction;

    public $queue = 'default';

    protected $dependencyObject;

    public $name = 'custom action name';

    public function __construct()
    {
        $this->dependencyObject = app(DependencyObject::class);
    }

    public function execute(DataObject $dataObject)
    {
        file_put_contents(
            TestCase::LOG_PATH,
            $dataObject->foo.' '.$this->dependencyObject->bar
        );
    }

    public function failed(PhpException $exception)
    {
        file_put_contents(
            TestCase::LOG_PATH,
            'whoops, action failed'
        );
    }
}