<?php

namespace MichielKempen\LaravelActions\Resources;

use MichielKempen\LaravelEnum\Enum;

class ActionStatus extends Enum
{
    const PENDING = 'pending';
    const SKIPPED = 'skipped';
    const SUCCEEDED = 'succeeded';
    const FAILED = 'failed';
}
