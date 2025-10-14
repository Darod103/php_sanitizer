<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Sanitizer;
use App\Types\ArrayType;
use App\Types\IntegerType;
use App\Types\StringType;
use App\Exceptions\ValidationException;

final class ArrayTypeTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeArrayOfIntegers(): void
    {
        $type = new ArrayType(new IntegerType());
        $data = ['1', '2', '3'];

        $result = $type->sanitize($data, 'items', $this->sanitizer);

        $this->assertSame([1, 2, 3], $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeArrayOfStrings(): void
    {
        $type = new ArrayType(new StringType());
        $data = ['foo', 123, 'bar'];

        $result = $type->sanitize($data, 'items', $this->sanitizer);

        $this->assertSame(['foo', '123', 'bar'], $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testSanitizeEmptyArray(): void
    {
        $type = new ArrayType(new IntegerType());
        $data = [];

        $result = $type->sanitize($data, 'items', $this->sanitizer);

        $this->assertSame([], $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testCollectErrorsForInvalidElements(): void
    {
        $type = new ArrayType(new IntegerType());
        $data = ['1', 'invalid', '3'];

        $result = $type->sanitize($data, 'items', $this->sanitizer);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('items[1]', $errors[0]['path']);
        $this->assertSame('invalid', $errors[0]['value']);
    }

    public function testCollectMultipleErrors(): void
    {
        $type = new ArrayType(new IntegerType());
        $data = ['1', 'abc', '3', 'xyz', '5'];

        $result = $type->sanitize($data, 'numbers', $this->sanitizer);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(2, $errors);
        $this->assertSame('numbers[1]', $errors[0]['path']);
        $this->assertSame('numbers[3]', $errors[1]['path']);
    }

    public function testRejectNonArray(): void
    {
        $type = new ArrayType(new IntegerType());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('массивом');

        $type->sanitize('not-an-array', 'items', $this->sanitizer);
    }


}