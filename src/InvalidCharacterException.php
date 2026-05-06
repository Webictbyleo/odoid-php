<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

use InvalidArgumentException;

/** Thrown when a character absent from the positional charset is encountered during decoding. */
class InvalidCharacterException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $char,
        public readonly int    $position,
    ) {
        parent::__construct(
            "Invalid OdoID character '{$char}' at position {$position}."
        );
    }
}
