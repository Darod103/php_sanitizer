<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Sanitizer;
use App\Types\IntegerType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class IntegerTypeTest extends TestCase
{
    private Sanitizer $sanitizer;
    private IntegerType $type;

    protected function setUp(): void
    {
        $this->type = new IntegerType();
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeWithInteger(): void
    {
        $result = $this->type->sanitize(123, 'age', $this->sanitizer);

        $this->assertSame(123, $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeWithStringInteger(): void
    {
        $result = $this->type->sanitize('456', 'age', $this->sanitizer);

        $this->assertSame(456, $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeWithNegativeInteger(): void
    {
        $result = $this->type->sanitize('-789', 'temp', $this->sanitizer);

        $this->assertSame(-789, $result);
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

    public function testSanitizeWithInvalidString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('целочисленным');

        $this->type->sanitize('123abc', 'age', $this->sanitizer);
    }

    public function testSanitizeWithFloat(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize(12.5, 'age', $this->sanitizer);
    }
}
