<?php

namespace MichielKempen\LaravelQueueableActions;

use MichielKempen\LaravelEnum\Enum;

class QueuedActionStatus extends Enum
{
    const PENDING = 'pending';
    const RUNNING = 'running';
    const SUCCEEDED = 'succeeded';
    const FAILED = 'failed';

    /**
     * @return array
     */
    public static function active(): array
    {
        return [
            self::PENDING,
            self::RUNNING,
        ];
    }
}
