<?php

namespace MichielKempen\LaravelActions\Tests\TestCase;

use Webpatser\Uuid\Uuid;

class PendingModel
{
    public string $id;
    public string $applicationId;
    public string $type;
    public string $name;
    public int $replicas = 1;

    public function __construct()
    {
        $this->id = (string) Uuid::generate(4);
    }

    public static function create(): self
    {
        return new static;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function applicationId(string $applicationId): self
    {
        $this->applicationId = $applicationId;
        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function replicas(int $replicas): self
    {
        $this->replicas = $replicas;
        return $this;
    }
}