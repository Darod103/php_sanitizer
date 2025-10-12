<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;

/**
 * Санитайзер данных с накоплением ошибок
 */
final class Sanitizer
{
    /** @var array<int, array{path: string, value: mixed, message: string}> */
    private array $errors = [];

    /**
     * Точка входа: санитизировать данные по типу
     */
    public function sanitize(mixed $data, TypeInterface $type, string $path = ''): mixed
    {
        $this->clearErrors();
        return $this->sanitizeValue($data, $type, $path);
    }

    /**
     * Рекурсивная валидация значения
     */
    private function sanitizeValue(mixed $value, TypeInterface $type, string $path): mixed
    {
        try {
            return $type->sanitize($value, $path, $this);
        } catch (ValidationException $e) {
            $this->errors[] = [
                'path' => $e->getPath(),
                'value' => $e->getValue(),
                'message' => $e->getMessage(),
            ];
            return null;
        }
    }

    /**
     * Добавить ошибку валидации (используется типами)
     */
    public function addError(string $path, mixed $value, string $message): void
    {
        $this->errors[] = compact('path', 'value', 'message');
    }

    /**
     * Получить все накопленные ошибки
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверить наличие ошибок
     */
    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Очистить накопленные ошибки
     */
    private function clearErrors(): void
    {
        $this->errors = [];
    }
}