<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Sanitizer;
use App\Types\ArrayType;
use App\Types\FloatType;
use App\Types\IntegerType;
use App\Types\PhoneRuType;
use App\Types\StringType;
use App\Types\StructType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SanitizerTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
    }

    public function testExample1FromTask(): void
    {
        $json = '{"foo": "123", "bar": "asd", "baz": "8 (950) 288-56-23"}';
        $data = json_decode($json, true);

        $schema = new StructType([
            'foo' => new IntegerType(),
            'bar' => new StringType(),
            'baz' => new PhoneRuType(),
        ]);

        $result = $this->sanitizer->sanitize($data, $schema);

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertSame(123, $result['foo']);
        $this->assertSame('asd', $result['bar']);
        $this->assertSame('79502885623', $result['baz']);
    }

    public function testExample2FromTask(): void
    {
        // Sanitizer ЛОВИТ исключения и добавляет в errors
        $result = $this->sanitizer->sanitize('123абв', new IntegerType());

        $this->assertNull($result);
        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('', $errors[0]['path']);
        $this->assertSame('123абв', $errors[0]['value']);
        $this->assertStringContainsString('целочисленным', $errors[0]['message']);
    }

    public function testExample3FromTask(): void
    {
        // Sanitizer ЛОВИТ исключения и добавляет в errors
        $result = $this->sanitizer->sanitize('260557', new PhoneRuType());

        $this->assertNull($result);
        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('', $errors[0]['path']);
        $this->assertSame('260557', $errors[0]['value']);
        $this->assertStringContainsString('телефон', strtolower($errors[0]['message']));
    }

    public function testNestedStructure(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'age' => '30',
            ],
            'phone' => '89991234567',
        ];

        $schema = new StructType([
            'user' => new StructType([
                'name' => new StringType(),
                'age' => new IntegerType(),
            ]),
            'phone' => new PhoneRuType(),
        ]);

        $result = $this->sanitizer->sanitize($data, $schema);

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertSame('John', $result['user']['name']);
        $this->assertSame(30, $result['user']['age']);
        $this->assertSame('79991234567', $result['phone']);
    }

    public function testArrayOfStructs(): void
    {
        $data = [
            ['name' => 'Alice', 'age' => '25'],
            ['name' => 'Bob', 'age' => '30'],
        ];

        $schema = new ArrayType(
            new StructType([
                'name' => new StringType(),
                'age' => new IntegerType(),
            ])
        );

        $result = $this->sanitizer->sanitize($data, $schema);

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertCount(2, $result);
        $this->assertSame('Alice', $result[0]['name']);
        $this->assertSame(25, $result[0]['age']);
        $this->assertSame('Bob', $result[1]['name']);
        $this->assertSame(30, $result[1]['age']);
    }

    public function testComplexNestedStructure(): void
    {
        $data = [
            'company' => 'Acme Corp',
            'employees' => [
                [
                    'name' => 'Alice',
                    'salary' => '5000.50',
                    'phone' => '89991234567',
                ],
            ],
        ];

        $schema = new StructType([
            'company' => new StringType(),
            'employees' => new ArrayType(
                new StructType([
                    'name' => new StringType(),
                    'salary' => new FloatType(),
                    'phone' => new PhoneRuType(),
                ])
            ),
        ]);

        $result = $this->sanitizer->sanitize($data, $schema);

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertSame('Acme Corp', $result['company']);
        $this->assertSame('Alice', $result['employees'][0]['name']);
        $this->assertEqualsWithDelta(5000.50, $result['employees'][0]['salary'], 0.01);
        $this->assertSame('79991234567', $result['employees'][0]['phone']);
    }

    public function testCollectAllErrorsFromNestedStructure(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'age' => 'invalid',
            ],
            'phone' => 'invalid-phone',
        ];

        $schema = new StructType([
            'user' => new StructType([
                'name' => new StringType(),
                'age' => new IntegerType(),
            ]),
            'phone' => new PhoneRuType(),
        ]);

        $result = $this->sanitizer->sanitize($data, $schema);

        $this->assertTrue($this->sanitizer->hasErrors());

        $errors = $this->sanitizer->getErrors();
        $this->assertCount(2, $errors);

        $paths = array_column($errors, 'path');
        $this->assertContains('user.age', $paths);
        $this->assertContains('phone', $paths);
    }

    public function testClearErrorsBetweenSanitizeCalls(): void
    {
        $this->sanitizer->sanitize('invalid', new IntegerType());
        $this->assertTrue($this->sanitizer->hasErrors());

        $result = $this->sanitizer->sanitize(123, new IntegerType());

        $this->assertFalse($this->sanitizer->hasErrors());
        $this->assertSame(123, $result);
    }
}
