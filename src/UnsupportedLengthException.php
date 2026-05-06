<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

use InvalidArgumentException;

/** Thrown when a length other than 6, 7, or 8 is requested. */
class UnsupportedLengthException extends InvalidArgumentException
{
    public function __construct(public readonly int $length)
    {
        parent::__construct(
            "Unsupported OdoID length: {$length}. Must be 6, 7, or 8."
        );
    }
}
