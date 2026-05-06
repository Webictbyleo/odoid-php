<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Webictbyleo\OdoID\Charsets;
use Webictbyleo\OdoID\InvalidCharacterException;
use Webictbyleo\OdoID\OdoId;
use Webictbyleo\OdoID\OdoOverflowException;
use Webictbyleo\OdoID\UnsupportedLengthException;

/**
 * Compliance test vectors as defined in SPEC.md § 8.
 * Every compliant implementation MUST pass these tests unchanged.
 */
final class ComplianceTest extends TestCase
{
    // ── § 8.1 Encode ──────────────────────────────────────────────────────────

    #[DataProvider('encodeVectors')]
    public function testEncodeSpecVectors(int $n, int $length, string $expected): void
    {
        $this->assertSame($expected, OdoId::encode($n, $length));
    }

    public static function encodeVectors(): array
    {
        return [
            [0,            6, '0A0000'],
            [1234567,      6, '0D7NM7'],
            [1234567,      7, '0A15NM7'],
            [236223201279, 8, 'ZZ9ZZZZZ'],
            [230686719,    6, 'ZZ9ZZZ'],
        ];
    }

    // ── § 8.2 Decode round-trips ──────────────────────────────────────────────

    #[DataProvider('decodeVectors')]
    public function testDecodeSpecVectors(string $id, int $expected): void
    {
        $this->assertSame($expected, OdoId::decode($id));
    }

    public static function decodeVectors(): array
    {
        return [
            ['0A0000',   0],
            ['0D7NM7',   1234567],
            ['0A15NM7',  1234567],
            ['ZZ9ZZZZZ', 236223201279],
            ['ZZ9ZZZ',   230686719],
        ];
    }

    // ── § 8.3 Error cases ─────────────────────────────────────────────────────

    public function testEncodeThrowsOverflow_whenNEqualsMax6(): void
    {
        $this->expectException(OdoOverflowException::class);
        OdoId::encode(Charsets::MAX[6], 6);
    }

    public function testEncodeThrowsUnsupportedLength_whenLength5(): void
    {
        $this->expectException(UnsupportedLengthException::class);
        OdoId::encode(0, 5);
    }

    public function testDecodeThrowsInvalidCharacter_containsO_atPosition6(): void
    {
        try {
            OdoId::decode('0A000O');
            $this->fail('Expected InvalidCharacterException');
        } catch (InvalidCharacterException $e) {
            $this->assertSame(6, $e->position);
            $this->assertSame('O', $e->char);
        }
    }

    public function testDecodeThrowsInvalidCharacter_containsI_atPosition6(): void
    {
        try {
            OdoId::decode('0A000I');
            $this->fail('Expected InvalidCharacterException');
        } catch (InvalidCharacterException $e) {
            $this->assertSame(6, $e->position);
        }
    }

    public function testDecodeThrowsInvalidCharacter_lowercaseLBecomesExcluded(): void
    {
        $this->expectException(InvalidCharacterException::class);
        OdoId::decode('0A000l');
    }

    public function testDecodeThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OdoId::decode('');
    }
}
