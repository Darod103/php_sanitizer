<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Sanitizer;
use App\Types\PhoneRuType;
use App\Exceptions\ValidationException;

final class PhoneRuTypeTest extends TestCase
{
    private Sanitizer $sanitizer;
    private PhoneRuType $type;

    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
        $this->type = new PhoneRuType();
    }

    public function testNormalizePhoneStartingWith7(): void
    {
        $result = $this->type->sanitize('79991234567', 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testNormalizePhoneStartingWith8(): void
    {
        $result = $this->type->sanitize('89991234567', 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testNormalize10DigitPhone(): void
    {
        $result = $this->type->sanitize('9991234567', 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testHandleFormattedPhone(): void
    {
        $result = $this->type->sanitize('8 (999) 123-45-67', 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testHandlePhoneWithPlus(): void
    {
        $result = $this->type->sanitize('+7-999-123-45-67', 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testHandlePhoneAsInteger(): void
    {
        $result = $this->type->sanitize(79991234567, 'phone', $this->sanitizer);

        $this->assertSame('79991234567', $result);
        $this->assertFalse($this->sanitizer->hasErrors());
    }

    public function testRejectShortPhone(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize('123456', 'phone', $this->sanitizer);
    }

    public function testRejectLongPhone(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize('123456789012', 'phone', $this->sanitizer);
    }

    public function testRejectForeignPhone(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize('+39-991-234-567', 'phone', $this->sanitizer);
    }

    public function testRejectNonNumericPhone(): void
    {
        $this->expectException(ValidationException::class);

        $this->type->sanitize('not-a-phone', 'phone', $this->sanitizer);
    }


}