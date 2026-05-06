<?php

namespace App\Support\Parents;

abstract class ParentData
{
    public static function from(array $data = []): static
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();

        if (! $constructor) {
            return new static();
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();

            $args[$name] = array_key_exists($name, $data)
                ? $data[$name]
                : $param->getDefaultValue();
        }

        return new static(...$args);
    }
}
