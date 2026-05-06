<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

use InvalidArgumentException;

/** Thrown when n >= MAX[length] for the chosen OdoID length. */
class OdoOverflowException extends InvalidArgumentException
{
    public function __construct(
        public readonly int $n,
        public readonly int $length,
    ) {
        $max = Charsets::MAX[$length];
        parent::__construct(
            "n={$n} is out of range for length {$length}. Valid range: 0 <= n < {$max}"
        );
    }
}
