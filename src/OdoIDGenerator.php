<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID;

/**
 * A distributed monotonic generator that produces OdoID strings driven by a
 * namespace-scoped, time-seeded pseudo-random integer.
 *
 * The generator guarantees that rapid successive calls within the same
 * millisecond tick produce distinct values via a monotonically incrementing
 * sequence counter.
 */
final class OdoIDGenerator
{
    public readonly int $capacity;

    private int $sequence = 0;
    private int $lastTick = -1;

    /**
     * @param  string   $namespace  Logical partition key (default "default").
     * @param  int      $length     OdoID length: 6 (default), 7, or 8.
     * @param  int      $epoch      Millisecond epoch origin (default 0).
     *
     * @throws UnsupportedLengthException
     */
    public function __construct(
        public readonly string $namespace = 'default',
        public readonly int    $length    = 6,
        private readonly int   $epoch     = 0,
    ) {
        OdoId::assertLength($length);
        $this->capacity = Charsets::MAX[$length];
    }

    private function nowMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    /**
     * FNV-1a 32-bit hash.
     * Constants are normative: offset basis = 2166136261, prime = 16777619.
     */
    private function fnv1a32(string $value): int
    {
        $h = 2166136261;
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $h = (($h ^ ord($value[$i])) * 16777619) & 0xFFFFFFFF;
        }
        return $h;
    }

    /**
     * Returns the next raw integer n in [0, capacity).
     * Exposed for testing and low-level use.
     */
    public function nextN(): int
    {
        $tick = $this->nowMs() - $this->epoch;

        if ($tick === $this->lastTick) {
            $this->sequence++;
        } else {
            $this->sequence = 0;
            $this->lastTick = $tick;
        }

        $seed = $this->fnv1a32($this->namespace . '|' . $tick);
        $seed ^= ($seed << 13) & 0xFFFFFFFF;
        $seed ^= ($seed >> 7);
        $seed ^= ($seed << 17) & 0xFFFFFFFF;

        return ($seed + $this->sequence) % $this->capacity;
    }

    /**
     * Generates and returns the next OdoID result.
     *
     * @return array{id: string, n: int, length: int, namespace: string}
     */
    public function next(): array
    {
        $n  = $this->nextN();
        $id = OdoId::encode($n, $this->length);

        return [
            'id'        => $id,
            'n'         => $n,
            'length'    => $this->length,
            'namespace' => $this->namespace,
        ];
    }

    /** Encodes $n using this generator's configured length. */
    public function encode(int $n): string
    {
        return OdoId::encode($n, $this->length);
    }

    /** Decodes an OdoID string to its originating integer. */
    public function decode(string $id): int
    {
        return OdoId::decode($id);
    }
}
