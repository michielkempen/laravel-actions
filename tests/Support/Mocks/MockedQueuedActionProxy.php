<?php

namespace MichielKempen\LaravelQueueableActions\Tests\Support\Mocks;

use Illuminate\Database\Eloquent\Model;

class MockedQueuedActionProxy
{
    /**
     * @var
     */
    private $action;

    /**
     * @var Model|null
     */
    private $model;

    /**
     * MockedQueuedActionProxy constructor.
     *
     * @param $action
     * @param Model|null $model
     */
    public function __construct($action, ?Model $model = null)
    {
        $this->action = $action;
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }
}