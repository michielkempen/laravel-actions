<?php

namespace MichielKempen\LaravelQueueableActions\Database;

use Illuminate\Support\Carbon;
use MichielKempen\LaravelUuidModel\UuidModel;

class QueuedAction extends UuidModel
{
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * @return Carbon
     */
    public function getLastUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }
}
