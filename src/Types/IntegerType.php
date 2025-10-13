<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Тип для валидации и нормализации целочисленного числа
 *
 */
final class IntegerType implements TypeInterface
{

    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            $filteredValue = filter_var($value, FILTER_VALIDATE_INT);
            if ($filteredValue !== false) {
                return $filteredValue;
            }
        }
        throw  new ValidationException(
            message:'Значение должно быть целочисленным числом',
            path: $path,
            value: $value
        );
    }
}