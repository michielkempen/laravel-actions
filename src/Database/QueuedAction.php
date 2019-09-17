<?php

namespace MichielKempen\LaravelActions\Database;

use MichielKempen\LaravelActions\Action;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedAction extends UuidModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = [
        'action' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'started_at',
        'finished_at',
    ];

    /**
     * @return string
     */
    public function getModelId(): string
    {
        return $this->model_id;
    }

    /**
     * @return string
     */
    public function getModelType(): string
    {
        return $this->model_type;
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        if(is_null($this->action)) {
            return null;
        }

        return Action::createFromSerialization($this->action);
    }
}
