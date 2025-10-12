<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends \Exception
{
    private string $path;
    private mixed $value;

    public function __construct(string $message, string $path, mixed $value)
    {
        parent::__construct($message);
        $this->path = $path;
        $this->value = $value;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}