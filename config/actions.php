<?php

return [

    /**
     * The name of the queue the queued actions should be sent to.
     */
    'default_queue' => 'default',

    /**
     * The number of seconds a queued action can run before timing out.
     */
    'default_timeout' => 60,

    /**
     * The number of times a queued action should be attempted before failure.
     */
    'default_attempts' => 1,

];