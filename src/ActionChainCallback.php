<?php

namespace MichielKempen\LaravelActions;

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
        $callbackInstance = resolve($this->class, $this->arguments);
        $callbackInstance->execute($actionChainReport);
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