<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

use InvalidArgumentException;

/**
 * Provides encoding and decoding of OdoID strings.
 *
 * @example
 *   OdoId::encode(0, 6)              // "0A0000"
 *   OdoId::encode(1234567, 6)        // "0D7NM7"
 *   OdoId::encode(1234567, 7)        // "0A15NM7"
 *   OdoId::encode(236223201279, 8)   // "ZZ9ZZZZZ"
 *   OdoId::decode("0D7NM7")          // 1234567
 */
final class OdoId
{
    private function __construct() {}

    /**
     * Validates that $length is a supported OdoID length (6, 7, or 8).
     *
     * @throws UnsupportedLengthException
     */
    public static function assertLength(int $length): void
    {
        if ($length !== 6 && $length !== 7 && $length !== 8) {
            throw new UnsupportedLengthException($length);
        }
    }

    /**
     * Encodes a non-negative integer $n into an OdoID string of the given $length.
     *
     * @param  int $n      Non-negative integer; must satisfy 0 <= n < MAX[$length].
     * @param  int $length Target string length: 6 (default), 7, or 8.
     * @return string      The encoded OdoID string (uppercase).
     *
     * @throws UnsupportedLengthException if $length is not 6, 7, or 8.
     * @throws OdoOverflowException       if $n >= MAX[$length] or $n < 0.
     */
    public static function encode(int $n, int $length = 6): string
    {
        self::assertLength($length);

        if ($n < 0 || $n >= Charsets::MAX[$length]) {
            throw new OdoOverflowException($n, $length);
        }

        $buf = array_fill(0, $length, '');
        for ($i = $length - 1; $i >= 0; $i--) {
            $charset   = Charsets::getCharset($i);
            $base      = strlen($charset);
            $buf[$i]   = $charset[$n % $base];
            $n         = intdiv($n, $base);
        }

        return implode('', $buf);
    }

    /**
     * Decodes an OdoID string back to its originating integer.
     *
     * The input is uppercased before lookup, so lowercase letters that are valid
     * in the charset (e.g. "0a0000") are accepted. The excluded characters
     * I, L, and O remain invalid even after uppercasing.
     *
     * @param  string $id OdoID string (6, 7, or 8 characters).
     * @return int        The decoded non-negative integer.
     *
     * @throws InvalidArgumentException   if $id is empty.
     * @throws UnsupportedLengthException if strlen($id) is not 6, 7, or 8.
     * @throws InvalidCharacterException  if any character is absent from its positional charset.
     */
    public static function decode(string $id): int
    {
        if ($id === '') {
            throw new InvalidArgumentException('OdoID must be a non-empty string.');
        }

        $upper  = strtoupper($id);
        $length = strlen($upper);
        self::assertLength($length);

        $n = 0;
        for ($i = 0; $i < $length; $i++) {
            $charset = Charsets::getCharset($i);
            $base    = strlen($charset);
            $v       = strpos($charset, $upper[$i]);
            if ($v === false) {
                throw new InvalidCharacterException($upper[$i], $i + 1);
            }
            $n = $n * $base + $v;
        }

        return $n;
    }
}
