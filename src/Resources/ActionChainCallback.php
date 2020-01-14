<?php

namespace MichielKempen\LaravelActions\Resources;

use MichielKempen\LaravelActions\InteractsWithActionChainReport;

class ActionChainCallback
{
    private string $class;
    private array $arguments;

    public function __construct(string $class, array $arguments = [])
    {
        $this->class = $class;
        $this->arguments = $arguments;
    }

    public function trigger(ActionChainReport $actionChainReport): void
    {
        $callbackInstance = resolve($this->class);

        if($this->callbackInteractsWithActionChainReport($callbackInstance)) {
            $callbackInstance->setActionChainReport($actionChainReport);
        }

        $callbackInstance->execute(...array_values($this->arguments));
    }

    private function callbackInteractsWithActionChainReport(object $callbackInstance): bool
    {
        return in_array(InteractsWithActionChainReport::class, class_uses($callbackInstance));
    }

    public function serialize(): array
    {
        return [
            'class' => $this->class,
            'arguments' => $this->arguments,
        ];
    }

    public static function deserialize(array $serialization): ActionChainCallback
    {
        return new static($serialization['class'], $serialization['arguments']);
    }
}