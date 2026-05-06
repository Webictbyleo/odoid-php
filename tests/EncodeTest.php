<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webictbyleo\OdoID\Charsets;
use Webictbyleo\OdoID\InvalidCharacterException;
use Webictbyleo\OdoID\OdoId;
use Webictbyleo\OdoID\OdoOverflowException;
use Webictbyleo\OdoID\UnsupportedLengthException;

final class EncodeTest extends TestCase
{
    public function testDefaultLengthIs6(): void
    {
        $this->assertSame(6, strlen(OdoId::encode(0)));
        $this->assertSame('0A0000', OdoId::encode(0));
    }

    #[DataProvider('supportedLengths')]
    public function testOutputLengthMatchesRequested(int $length): void
    {
        $this->assertSame($length, strlen(OdoId::encode(0, $length)));
    }

    public static function supportedLengths(): array
    {
        return [[6], [7], [8]];
    }

    #[DataProvider('sampleValues')]
    public function testOutputIsAlwaysUppercase(int $n): void
    {
        $id = OdoId::encode($n, 6);
        $this->assertSame(strtoupper($id), $id);
    }

    public static function sampleValues(): array
    {
        return [[0], [1], [100], [999999], [1234567]];
    }

    #[DataProvider('sampleValues')]
    public function testPosition1IsAlwaysAlphaChar(int $n): void
    {
        $id = OdoId::encode($n, 6);
        $ch = $id[1];
        $this->assertStringContainsString($ch, Charsets::ALPHA,
            "pos 1 = '{$ch}' not in ALPHA");
    }

    #[DataProvider('sampleValues')]
    public function testPosition2IsAlwaysDigit(int $n): void
    {
        $ch = OdoId::encode($n, 6)[2];
        $this->assertMatchesRegularExpression('/\d/', $ch,
            "pos 2 = '{$ch}' not a digit");
    }

    #[DataProvider('largeSampleValues')]
    public function testExcludedCharsNeverAppear(int $n): void
    {
        $id = OdoId::encode($n, 8);
        $this->assertStringNotContainsString('I', $id, "{$id} contains I");
        $this->assertStringNotContainsString('L', $id, "{$id} contains L");
        $this->assertStringNotContainsString('O', $id, "{$id} contains O");
    }

    public static function largeSampleValues(): array
    {
        return [[0], [1], [1000], [1234567], [100_000_000]];
    }

    #[DataProvider('supportedLengths')]
    public function testEncodeZeroIsValid(int $length): void
    {
        $this->assertIsString(OdoId::encode(0, $length));
    }

    #[DataProvider('supportedLengths')]
    public function testEncodeMaxMinus1IsValid(int $length): void
    {
        $this->assertIsString(OdoId::encode(Charsets::MAX[$length] - 1, $length));
    }

    #[DataProvider('supportedLengths')]
    public function testEncodeMaxThrowsOverflow(int $length): void
    {
        $this->expectException(OdoOverflowException::class);
        OdoId::encode(Charsets::MAX[$length], $length);
    }

    public function testOverflowExceptionMessageContainsViolatingValue(): void
    {
        try {
            OdoId::encode(Charsets::MAX[6], 6);
            $this->fail('Expected OdoOverflowException');
        } catch (OdoOverflowException $e) {
            $this->assertStringContainsString((string) Charsets::MAX[6], $e->getMessage());
        }
    }

    public function testOverflowExceptionExtendsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OdoId::encode(Charsets::MAX[6], 6);
    }

    #[DataProvider('unsupportedLengths')]
    public function testUnsupportedLengthThrowsException(int $length): void
    {
        $this->expectException(UnsupportedLengthException::class);
        OdoId::encode(0, $length);
    }

    public static function unsupportedLengths(): array
    {
        return [[0], [1], [5], [9], [100]];
    }

    public function testUnsupportedLengthExceptionExtendsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OdoId::encode(0, 5);
    }
}
