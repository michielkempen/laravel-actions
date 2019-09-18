<?php

namespace MichielKempen\LaravelActions\Tests\TestCase\Actions;

use Exception as PhpException;
use MichielKempen\LaravelActions\Implementations\Async\QueueableAction;
use MichielKempen\LaravelActions\Implementations\Sync\ChainableAction;
use MichielKempen\LaravelActions\Tests\TestCase\TestCase;

class ComplexAction
{
    use QueueableAction, ChainableAction;

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