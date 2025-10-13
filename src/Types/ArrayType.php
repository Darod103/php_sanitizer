<?php

declare(strict_types=1);

namespace App\Types;

use App\Exceptions\ValidationException;
use App\Interfaces\TypeInterface;
use App\Sanitizer;

/**
 * Тип для массивов однотипных элементов
 */
final class ArrayType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $elementType
    ) {
    }

    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): array
    {
        if (!is_array($value)) {
            throw new ValidationException(
                message: 'Значение должно быть массивом',
                path: $path,
                value: $value
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            $itemPath = $this->buildPath($path, $index);

            try {
                $result[$index] = $this->elementType->sanitize($item, $itemPath, $sanitizer);
            } catch (ValidationException $e) {
                $sanitizer->addError(
                    path: $e->getPath(),
                    value: $e->getValue(),
                    message: $e->getMessage()
                );
            }
        }

        return $result;
    }

    /**
     * Построение пути для элемента массива
     */
    private function buildPath(string $basePath, int|string $index): string
    {
        $indexPart = "[{$index}]";
        return $basePath === '' ? $indexPart : $basePath . $indexPart;
    }
}