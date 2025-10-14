<?php

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Sanitizer;
use App\Types\StringType;
use PHPUnit\Framework\TestCase;

final class StringTypeTest extends TestCase
{
    private Sanitizer $sanitizer;
    private StringType $type;

    protected function setUp(): void
    {
        $this->type = new StringType();
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeWithString(): void
    {
        $result = $this->type->sanitize('hello', 'name', $this->sanitizer);

        $this->assertSame('hello', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeWithNumber(): void
    {
        $result = $this->type->sanitize(123, 'name', $this->sanitizer);

        $this->assertSame('123', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeWithBoolean(): void
    {
        $this->expectException(ValidationException::class);
        $result = $this->type->sanitize(true, 'name', $this->sanitizer);
    }

    public function testSanitizeWithObject(): void
    {
        $this->expectException(ValidationException::class);
        $this->type->sanitize(new \stdClass(), 'obj', $this->sanitizer);
    }

    public function testSanitizeWithArray(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize(['test'], 'name', $this->sanitizer);
    }

    public function testSanitizeWithNull(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize(null, 'name', $this->sanitizer);
    }

}