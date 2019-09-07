<?php

namespace MichielKempen\LaravelQueueableActions\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    const LOG_PATH = __DIR__.'/Support/temp/queue.log';

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->clearLog();
    }

    /**
     * @param Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/create_queued_actions_table.php.stub';

        (new \CreateQueuedActionsTable)->up();
    }

    protected function clearLog()
    {
        if (! file_exists(self::LOG_PATH)) {
            return;
        }

        unlink(self::LOG_PATH);
    }

    protected function assertLogHas(string $text)
    {
        $log = file_get_contents(self::LOG_PATH);

        $this->assertStringContainsString($text, $log);
    }
}