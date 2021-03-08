<?php

namespace MichielKempen\LaravelActions\Support;

use Closure;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class ModelFactory
{
    protected Faker $faker;
    protected array $attributes = [];

    public function __construct()
    {
        $this->faker = resolve(Faker::class);
    }

    public static function new(): self
    {
        return new static;
    }

    protected function setAttribute($key, $value): self
    {
        $clone = clone $this;
        Arr::set($clone->attributes, $key, $value);
        return $clone;
    }

    public abstract function attributes(): array;

    protected function resolveAttributes(): array
    {
        $attributes = $this->mergeArrays($this->attributes(), $this->attributes);

        return array_map(function($value) {
            return $value instanceof Closure ? $value() : $value;
        }, $attributes);
    }

    private function mergeArrays(array $array1, array $array2): array
    {
        foreach ($array1 as $key => $value) {
            if(array_key_exists($key, $array2)) {
                $array1[$key] = is_array($value) && is_array($array2[$key])
                    ? $this->mergeArrays($array1[$key], $array2[$key])
                    : $array2[$key];
                unset($array2[$key]);
            }
        }

        foreach ($array2 as $key => $value) {
            $array1[$key] = $value;
        }

        return $array1;
    }

    public abstract function create();

    public function createNumber(int $times): Collection
    {
        return collect()->times($times)->map(fn() => $this->create());
    }
}
