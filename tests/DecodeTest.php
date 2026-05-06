<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webictbyleo\OdoID\InvalidCharacterException;
use Webictbyleo\OdoID\OdoId;
use Webictbyleo\OdoID\UnsupportedLengthException;

final class DecodeTest extends TestCase
{
    #[DataProvider('roundTripCases')]
    public function testRoundTrip(int $n, int $length): void
    {
        $this->assertSame($n, OdoId::decode(OdoId::encode($n, $length)));
    }

    public static function roundTripCases(): array
    {
        return [
            [0, 6], [1, 6], [255, 6], [65535, 6], [1234567, 6],
            [0, 7], [1234567, 7],
            [0, 8], [1234567, 8],
        ];
    }

    public function testAcceptsLowercase(): void
    {
        $this->assertSame(0, OdoId::decode('0a0000'));
    }

    public function testLowercaseMatchesUppercase(): void
    {
        $this->assertSame(OdoId::decode('0D7NM7'), OdoId::decode('0d7nm7'));
    }

    #[DataProvider('excludedChars')]
    public function testExcludedCharsThrowInvalidCharacterException(string $ch): void
    {
        $this->expectException(InvalidCharacterException::class);
        OdoId::decode('0A0' . $ch . '00');
    }

    public static function excludedChars(): array
    {
        return [['I'], ['L'], ['O']];
    }

    public function testInvalidCharReportsCorrectPosition(): void
    {
        try {
            OdoId::decode('0A000O');
            $this->fail('Expected InvalidCharacterException');
        } catch (InvalidCharacterException $e) {
            $this->assertSame(6, $e->position);
            $this->assertSame('O', $e->char);
        }
    }

    public function testInvalidCharExceptionExtendsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OdoId::decode('0A000O');
    }

    public function testSpecialCharThrowsInvalidCharacterException(): void
    {
        $this->expectException(InvalidCharacterException::class);
        OdoId::decode('0A00-0');
    }

    #[DataProvider('unsupportedLengthIds')]
    public function testUnsupportedLengthThrowsException(string $id): void
    {
        $this->expectException(UnsupportedLengthException::class);
        OdoId::decode($id);
    }

    public static function unsupportedLengthIds(): array
    {
        return [['0A000'], ['0A000000000']];
    }

    public function testEmptyStringThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OdoId::decode('');
    }
}
