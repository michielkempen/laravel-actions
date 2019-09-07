<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Support;

use Exception as PhpException;
use MichielKempen\LaravelQueueableActions\QueueableAction;
use MichielKempen\LaravelQueueableActions\Tests\TestCase;

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