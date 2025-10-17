<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Sanitizer;
use App\Types\IntegerType;
use App\Types\StringType;
use App\Types\StructType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class StructTypeTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
    }

    public function testSanitizeValidStruct(): void
    {
        $type = new StructType([
            'name' => new StringType(),
            'age' => new IntegerType(),
        ]);

        $data = ['name' => 'John', 'age' => '30'];

        $result = $type->sanitize($data, 'user', $this->sanitizer);

        $this->assertSame('John', $result['name']);
        $this->assertSame(30, $result['age']);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testCollectErrorForMissingField(): void
    {
        $type = new StructType([
            'name' => new StringType(),
            'age' => new IntegerType(),
        ]);

        $data = ['name' => 'John'];

        $result = $type->sanitize($data, 'user', $this->sanitizer);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('user.age', $errors[0]['path']);
        $this->assertStringContainsString('отсутствует', $errors[0]['message']);
    }

    public function testCollectErrorForInvalidField(): void
    {
        $type = new StructType([
            'name' => new StringType(),
            'age' => new IntegerType(),
        ]);

        $data = ['name' => 'John', 'age' => 'invalid'];

        $result = $type->sanitize($data, 'user', $this->sanitizer);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('user.age', $errors[0]['path']);
        $this->assertSame('invalid', $errors[0]['value']);
    }

    public function testRejectUnknownFieldsByDefault(): void
    {
        $type = new StructType([
            'name' => new StringType(),
        ]);

        $data = ['name' => 'John', 'extra' => 'field'];

        $result = $type->sanitize($data, 'user', $this->sanitizer);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('user.extra', $errors[0]['path']);
        $this->assertStringContainsString('Неизвестное', $errors[0]['message']);
    }

    public function testAllowUnknownFieldsWhenConfigured(): void
    {
        $type = new StructType(
            fields: ['name' => new StringType()],
            allowUnknown: true
        );

        $data = ['name' => 'John', 'extra' => 'field'];

        $result = $type->sanitize($data, 'user', $this->sanitizer);

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('extra', $result);
    }

    public function testRejectNonArray(): void
    {
        $type = new StructType(['name' => new StringType()]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('массивом');

        $type->sanitize('not-an-array', 'user', $this->sanitizer);
    }
}
