<?php

namespace App\Interfaces;

use app\Sanitizer;

interface TypeInterface
{
    public function sanitize(mixed $value, string $path, Sanitizer $sanitizer): mixed;
}