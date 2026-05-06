<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

/**
 * OdoID character set definitions.
 *
 * These exact strings MUST be reproduced verbatim in every compliant implementation.
 */
final class Charsets
{
    /** Numeric characters — radix 10. */
    public const NUM = '0123456789';

    /** Alpha characters (ambiguous chars I, L, O excluded) — radix 22. */
    public const ALPHA = 'ABCDEFGHJKMNPQRSTVWXYZ';

    /** Full hybrid set — NUM concatenated with ALPHA — radix 32. */
    public const ALL = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Maximum exclusive value for each supported length.
     * Formula: 32 × 22 × 10 × 32^(L-3) = 220 × 32^(L-2)
     *
     * @var array<int, int>
     */
    public const MAX = [
        6 => 230_686_720,
        7 => 7_381_975_040,
        8 => 236_223_201_280,
    ];

    /**
     * Returns the charset string for the given 0-based position index.
     *
     * Index  Charset  Radix
     *   0    ALL      32
     *   1    ALPHA    22
     *   2    NUM      10
     *   3+   ALL      32
     */
    public static function getCharset(int $position): string
    {
        return match ($position) {
            1       => self::ALPHA,
            2       => self::NUM,
            default => self::ALL,
        };
    }
}
