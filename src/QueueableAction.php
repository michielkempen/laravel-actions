<?php

namespace MichielKempen\LaravelQueueableActions;

use Illuminate\Database\Eloquent\Model;

trait QueueableAction
{
    /**
     * @return QueuedActionJob
     */
	public static function job(): QueuedActionJob
    {
        return new QueuedActionJob(new static);
    }

    /**
     * @param Model|null $model
     * @return static
     */
    public function onQueue(?Model $model = null)
    {
        /** @var self $class */
        $class = new QueuedActionProxy($this, $model);

        return $class;
    }
}
