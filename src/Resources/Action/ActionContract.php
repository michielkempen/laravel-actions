<?php

namespace MichielKempen\LaravelActions\Resources\Action;

use Illuminate\Support\Carbon;

interface ActionContract
{
    public function getClass(): string;
    public function instantiate(): object;
    public function getArguments(): array;
    public function getName(): string;
    public function setStatus(string $status): ActionContract;
    public function getStatus(): string;
    public function setOutput($output): ActionContract;
    public function getOutput();
    public function setStartedAt(?Carbon $startedAt): ActionContract;
    public function getStartedAt(): ?Carbon;
    public function setFinishedAt(?Carbon $finishedAt): ActionContract;
    public function getFinishedAt(): ?Carbon;
    public function getDuration(): ?string;
}