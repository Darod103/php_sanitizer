<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Sanitizer;
use App\Types\FloatType;
use PHPUnit\Framework\TestCase;

final class FloatTypeTest extends TestCase
{

    private Sanitizer $sanitizer;
    private FloatType $type;

    protected function setUp(): void
    {
        $this->type = new FloatType();
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeWithFloat(): void
    {
        $result = $this->type->sanitize(1.23, 'age', $this->sanitizer);

        $this->assertSame(1.23, $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeWithStringFloat(): void
    {
        $result = $this->type->sanitize('4.56', 'age', $this->sanitizer);

        $this->assertSame(4.56, $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }
    public function testSanitizeWithNegativeFloat(): void
    {
        $result = $this->type->sanitize('-7.89', 'temp', $this->sanitizer);

        $this->assertSame(-7.89, $result);
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
        $this->expectExceptionMessage('плавающей');

        $this->type->sanitize('123abc', 'age', $this->sanitizer);
    }

    public function testSanitizeWithInteger(): void
    {
        $result = $this->type->sanitize(100, 'price', $this->sanitizer);

        $this->assertSame(100.0, $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

}