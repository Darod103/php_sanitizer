<?php

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use app\Sanitizer;

/**
 * Тип для валидации и нормализации строки
 *
 */
final class StringType implements TypeInterface
{

    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): mixed
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new ValidationException(
                message: 'Value must be a string',
                path: $path,
                value: $value
            );
        }

        return trim((string)$value);
    }
}