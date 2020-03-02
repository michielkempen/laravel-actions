<?php

namespace MichielKempen\LaravelActions\Resources;

use MichielKempen\LaravelActions\QueueableObject;
use ReflectionClass;

class ArgumentSerializer
{
    public static function serialize(array $arguments): string
    {
        $attribute = array_map(function($attribute) {

            if(is_object($attribute)) {
                return [
                    'type' => 'object',
                    'class' => get_class($attribute),
                    'properties' => method_exists($attribute, 'serialize')
                        ? $attribute->serialize()
                        : get_object_vars($attribute),
                ];
            } else {
                return [
                    'type' => 'primitive',
                    'value' => $attribute,
                ];
            }

        }, $arguments);

        return json_encode($attribute);
    }

    public static function deserialize(string $arguments): array
    {
        $attribute = json_decode($arguments, true);

        return array_map(function($attribute) {

            if($attribute['type'] == 'object') {
                $class = new ReflectionClass($attribute['class']);

                if($class->implementsInterface(QueueableObject::class)) {
                    return call_user_func("{$attribute['class']}::deserialize", $attribute['properties']);
                }

                $object = $class->newInstance();

                foreach ($attribute['properties'] as $key => $value) {
                    $object->$key = $value;
                }

                return $object;
            } else {
                return $attribute['value'];
            }

        }, $attribute);
    }
}