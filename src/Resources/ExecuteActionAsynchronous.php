<?php

namespace MichielKempen\LaravelActions\Resources;

use Illuminate\Contracts\Console\Kernel;
use Spatie\Async\Task;

class ExecuteActionAsynchronous extends Task
{
    private object $actionInstance;
    private array $arguments;

    public function __construct(object $actionInstance, array $arguments)
    {
        $this->actionInstance = $actionInstance;
        $this->arguments = $arguments;
    }

    public function configure()
    {
        $laravel = __DIR__.'/../../../../../bootstrap/app.php';

        if(file_exists($laravel)) {
            $app = require_once $laravel;
            $app->make(Kernel::class)->bootstrap();
        }
    }

    public function run()
    {
        return $this->actionInstance->execute(...$this->arguments);
    }
}
