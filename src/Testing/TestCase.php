<?php

namespace MichielKempen\LaravelActions\Testing;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use WithFaker;

    const LOG_PATH = __DIR__.'/temp/queue.log';

    public function setUp(): void
    {
        parent::setUp();

        config()->set('actions.default_connection', 'sync');
        config()->set('actions.default_queue', 'default');
        config()->set('actions.default_timeout', 60);
        config()->set('actions.default_attempts', 1);

        $this->setUpDatabase($this->app);
        $this->clearLog();
    }

    protected function setUpDatabase(Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        include_once __DIR__.'/../../database/migrations/create_queued_actions_table.php.stub';

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
