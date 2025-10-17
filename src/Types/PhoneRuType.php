<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Валидация российских телефонных номеров
 * Результат: 79991234567 (11 цифр, начинается с 7).
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

        $digits = str_replace(['+', '-'], '', filter_var((string) $value, FILTER_SANITIZE_NUMBER_INT));

        $len = strlen($digits);
        if (10 !== $len && 11 !== $len) {
            throw new ValidationException(
                message: 'Некорректный формат телефона РФ',
                path: $path,
                value: $value
            );
        }

        $normalized = $this->normalize($digits);

        if (11 !== strlen($normalized) || '7' !== $normalized[0]) {
            throw new ValidationException(
                message: 'Некорректный формат телефона РФ',
                path: $path,
                value: $value
            );
        }

        return $normalized;
    }

    /**
     * Нормализация номера к формату 7XXXXXXXXXX.
     *
     * @param string $digits Строка из цифр (10 или 11 символов)
     *
     * @return string Нормализованный номер
     */
    private function normalize(string $digits): string
    {
        $len = strlen($digits);

        if (11 === $len && '8' === $digits[0]) {
            return '7'.substr($digits, 1);
        }

        if (10 === $len) {
            return '7'.$digits;
        }

        return $digits;
    }
}
