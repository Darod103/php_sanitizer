<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Тип для валидации и нормализации чисел с плавающей точкой.
 */
final class FloatType implements TypeInterface
{
    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): float
    {
        if (is_float($value) || is_int($value)) {
            $float = (float) $value;
            $this->validateFinite($float, $path, $value);

            return $float;
        }

        if (is_string($value)) {
            $normalized = trim($value);

            if ('' === $normalized) {
                throw new ValidationException(
                    message: 'Значение должно быть числом с плавающей точкой',
                    path: $path,
                    value: $value
                );
            }

            $normalized = str_replace(',', '.', $normalized);

            $filtered = filter_var($normalized, FILTER_VALIDATE_FLOAT);
            if (false !== $filtered) {
                $this->validateFinite($filtered, $path, $value);

                return $filtered;
            }
        }

        throw new ValidationException(
            message: 'Значение должно быть числом с плавающей точкой',
            path: $path,
            value: $value
        );
    }

    /**
     * Проверка на NaN и Infinity.
     */
    private function validateFinite(float $float, string $path, mixed $originalValue): void
    {
        if (is_nan($float) || is_infinite($float)) {
            throw new ValidationException(
                message: 'Некорректное значение float (NaN/INF запрещены)',
                path: $path,
                value: $originalValue
            );
        }
    }
}
