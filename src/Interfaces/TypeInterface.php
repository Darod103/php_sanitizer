<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Sanitizer;

interface TypeInterface
{
    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): mixed;
}
