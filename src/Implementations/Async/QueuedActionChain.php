<?php

namespace MichielKempen\LaravelActions\Implementations\Async;

use MichielKempen\LaravelActions\ActionChain;

class QueuedActionChain extends ActionChain
{
    /**
     * @var array
     */
    private $callbacks;

    /**
     * @param array $callbacks
     */
    public function __construct(array $callbacks = [])
    {
        parent::__construct();

        $this->callbacks = $callbacks;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }
}