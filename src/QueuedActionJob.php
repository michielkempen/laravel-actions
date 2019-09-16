<?php

namespace MichielKempen\LaravelQueueableActions;

use Exception as PhpException;
use MichielKempen\LaravelQueueableActions\Database\QueuedActionRepository;
use MichielKempen\LaravelQueueableActions\Events\QueuedActionUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueuedActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var QueuedActionRepository
     */
    protected $queuedActionRepository;

    /**
     * @var string
     */
    protected $actionClass;

    /**
     * @var string|null
     */
    protected $queuedActionId;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var
     */
    protected $action;

    /**
     * ActionJob constructor.
     *
     * @param $action
     * @param string|null $queuedActionId
     * @param array $parameters
     */
    public function __construct($action, ?string $queuedActionId = null, array $parameters = [])
    {
        $this->queuedActionRepository = app(QueuedActionRepository::class);

        $this->actionClass = get_class($action);
        $this->queuedActionId = $queuedActionId;
        $this->parameters = $parameters;

        $this->resolveQueueableProperties($action);
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        return $this->actionClass;
    }

    /**
     * @return string
     */
    public function getActionClass(): string
    {
        return $this->actionClass;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * ...
     */
    public function handle()
    {
        $this->updateQueuedActionOrSkip(QueuedActionStatus::RUNNING);

        $this->action = app($this->actionClass);

        $this->action->execute(...$this->parameters);

        $this->updateQueuedActionOrSkip(QueuedActionStatus::SUCCEEDED);
    }

    /**
     * @param PhpException $exception
     */
    public function failed(PhpException $exception)
    {
        $this->updateQueuedActionOrSkip(QueuedActionStatus::FAILED, $exception->getMessage());

        if(method_exists($this->action, 'failed')) {
            $this->action->failed($exception);
        }
    }

    /**
     * @param $action
     */
    protected function resolveQueueableProperties($action)
    {
        $queueableProperties = [
            'connection',
            'queue',
            'chainConnection',
            'chainQueue',
            'delay',
            'chained',
            'tries',
            'timeout',
        ];

        foreach ($queueableProperties as $queueableProperty) {
            if(property_exists($action, $queueableProperty)) {
                $this->{$queueableProperty} = $action->{$queueableProperty};
            }
        }
    }

    /**
     * @param string $status
     * @param string|null $output
     * @return void
     */
    private function updateQueuedActionOrSkip(string $status, string $output = null): void
    {
        if(! $this->queuedActionId) {
            return;
        }

        $queuedAction = $this->queuedActionRepository->updateQueuedAction(
            $this->queuedActionId, $status, $output
        );

        event(new QueuedActionUpdated($queuedAction));
    }
}
