<?php
namespace App\Application\DTOs;

use Illuminate\Http\Request;
use ReflectionClass;

abstract class BaseDTO
{
    public static function fromRequest(Request $request): static
    {
        $reflection = new ReflectionClass(static::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $request->input($property->getName());
        }

        return new static(...array_values($data));
    }
}
