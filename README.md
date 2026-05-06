# odoid

Deterministic mixed-radix ID encoding. Maps a non-negative integer to a 6, 7, or 8-character alphanumeric string with a serial-number aesthetic.

```php
use Webictbyleo\OdoID\OdoId;

OdoId::encode(0,            6);  // "0A0000"
OdoId::encode(1234567,      6);  // "0D7NM7"
OdoId::encode(1234567,      7);  // "0A15NM7"
OdoId::encode(236223201279, 8);  // "ZZ9ZZZZZ"
```

## Features

- **Deterministic** — same integer + length always produces the same string, and vice-versa.
- **Human-readable** — ambiguous characters `I`, `L`, `O` are excluded from all positions.
- **Fixed positional structure** — position 1 is always a letter, position 2 is always a digit.
- **Zero dependencies** — pure PHP standard library, PHP 8.1+.

## Install

```sh
composer require webictbyleo/odoid
```

## Usage

### Encode

```php
use Webictbyleo\OdoID\OdoId;

OdoId::encode(0, 6);            // "0A0000"
OdoId::encode(1234567, 6);      // "0D7NM7"
OdoId::encode(1234567, 7);      // "0A15NM7"
OdoId::encode(236223201279, 8); // "ZZ9ZZZZZ"

// Default length is 6
OdoId::encode(0); // "0A0000"
```

### Decode

```php
OdoId::decode("0D7NM7");   // 1234567
OdoId::decode("0d7nm7");   // 1234567 (lowercase accepted)
```

### OdoIDGenerator

```php
use Webictbyleo\OdoID\OdoIDGenerator;

$g = new OdoIDGenerator(namespace: 'orders', length: 7);
$result = $g->next();
// $result['id']        → e.g. "3H5NV2K"
// $result['n']         → the raw integer
// $result['length']    → 7
// $result['namespace'] → "orders"
```

## Lengths and Capacity

| Length | Max integer (exclusive) |
|--------|------------------------|
| 6      | 230,686,720            |
| 7      | 7,381,975,040          |
| 8      | 236,223,201,280        |

## Exceptions

All extend `\InvalidArgumentException`:

| Exception | When |
|-----------|------|
| `OdoOverflowException` | `$n >= MAX[$length]` |
| `UnsupportedLengthException` | length is not 6, 7, or 8 |
| `InvalidCharacterException` | character not in positional charset during decode |

```php
use Webictbyleo\OdoID\OdoOverflowException;
use Webictbyleo\OdoID\UnsupportedLengthException;
use Webictbyleo\OdoID\InvalidCharacterException;

try {
    OdoId::decode("0A000O");
} catch (InvalidCharacterException $e) {
    echo $e->char;     // "O"
    echo $e->position; // 6
}
```

## Run tests

```sh
composer install
vendor/bin/phpunit
```

## Monorepo

This repository is the Packagist-facing split of the PHP package from the [Webictbyleo/odoid](https://github.com/Webictbyleo/odoid) monorepo, which contains implementations in TypeScript, Python, Go, C#, Rust, Lua, and Java.

## Specification

See [SPEC.md](https://github.com/Webictbyleo/odoid/blob/main/SPEC.md) for the full processing instruction document.

## License

MIT
