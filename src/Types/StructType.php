<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Тип для структур
 */
final class StructType implements TypeInterface
{
    /**
     * @param array<string, TypeInterface> $fields       Поля структуры и их типы
     * @param bool                         $allowUnknown Разрешить неизвестные поля в данных
     */
    public function __construct(
        private readonly array $fields,
        private readonly bool $allowUnknown = false
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): array
    {
        if (!is_array($value)) {
            throw new ValidationException(
                message: 'Значение должно быть ассоциативным массивом',
                path: $path,
                value: $value
            );
        }

        $result = [];

        foreach ($this->fields as $fieldName => $fieldType) {
            $fieldPath = $this->buildFieldPath($path, $fieldName);

            if (!array_key_exists($fieldName, $value)) {
                $sanitizer->addError(
                    path: $fieldPath,
                    value: null,
                    message: 'Обязательное поле отсутствует'
                );

                continue;
            }

            try {
                $result[$fieldName] = $fieldType->sanitize($value[$fieldName], $fieldPath, $sanitizer);
            } catch (ValidationException $e) {
                $sanitizer->addError(
                    path: $e->getPath(),
                    value: $e->getValue(),
                    message: $e->getMessage()
                );
            }
        }

        if (!$this->allowUnknown) {
            $this->validateNoUnknownFields($value, $path, $sanitizer);
        }

        return $result;
    }

    /**
     * Построение пути для поля структуры.
     */
    private function buildFieldPath(string $basePath, string $fieldName): string
    {
        return '' === $basePath ? $fieldName : "{$basePath}.{$fieldName}";
    }

    /**
     * Проверка на неизвестные поля.
     *
     * @param array<string, mixed> $value
     */
    private function validateNoUnknownFields(array $value, string $path, Sanitizer $sanitizer): void
    {
        $unknownFields = array_diff_key($value, $this->fields);

        foreach ($unknownFields as $fieldName => $fieldValue) {
            $fieldPath = $this->buildFieldPath($path, (string) $fieldName);
            $sanitizer->addError(
                path: $fieldPath,
                value: $fieldValue,
                message: 'Неизвестное поле'
            );
        }
    }
}
