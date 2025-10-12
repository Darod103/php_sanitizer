<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Валидация российских телефонных номеров
 */
final class PhoneRuType implements TypeInterface
{
    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): string
    {
        if (!is_string($value) && !is_int($value)) {
            throw new ValidationException(
                message: 'Телефон должен быть строкой или числом',
                path: $path,
                value: $value
            );
        }

        $digits = str_replace(['+', '-'], '', filter_var((string)$value, FILTER_SANITIZE_NUMBER_INT));

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }

        if (strlen($digits) !== 11 || $digits[0] !== '7') {
            throw new ValidationException(
                message: 'Некорректный формат телефона РФ',
                path: $path,
                value: $value
            );
        }

        return $digits;
    }
}