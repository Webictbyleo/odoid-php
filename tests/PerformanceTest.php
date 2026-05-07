<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID\Tests;

use PHPUnit\Framework\TestCase;
use Webictbyleo\OdoID\Charsets;
use Webictbyleo\OdoID\OdoId;

/**
 * Performance tests — SPEC.md § 9.
 * Each encode / decode call must average ≤ 0.1000 ms.
 */
final class PerformanceTest extends TestCase
{
    private const LIMIT_MS  = 0.1;
    private const WARMUP    = 2_000;
    private const ITERATIONS = 10_000;

    private function assertAvgMs(string $label, callable $fn): void
    {
        for ($i = 0; $i < self::WARMUP; $i++) {
            $fn();
        }

        $start = hrtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $fn();
        }
        $avgMs = (hrtime(true) - $start) / 1_000_000 / self::ITERATIONS;
        fwrite(STDOUT, sprintf("RESULT|php|%s|%.6f\n", $label, $avgMs));

        $this->assertLessThanOrEqual(
            self::LIMIT_MS,
            $avgMs,
            sprintf('%s: average %.4f ms exceeded spec limit of %.4f ms', $label, $avgMs, self::LIMIT_MS)
        );
    }

    public function testEncodeLength6AveragesBelowLimit(): void
    {
        $n = 0;
        $this->assertAvgMs('encode/6', static function () use (&$n) {
            $n = ($n + 1) % Charsets::MAX[6];
            OdoId::encode($n, 6);
        });
    }

    public function testEncodeLength7AveragesBelowLimit(): void
    {
        $n = 0;
        $this->assertAvgMs('encode/7', static function () use (&$n) {
            $n = ($n + 1) % Charsets::MAX[7];
            OdoId::encode($n, 7);
        });
    }

    public function testEncodeLength8AveragesBelowLimit(): void
    {
        $n = 0;
        $this->assertAvgMs('encode/8', static function () use (&$n) {
            $n = ($n + 1) % Charsets::MAX[8];
            OdoId::encode($n, 8);
        });
    }

    public function testDecodeLength6AveragesBelowLimit(): void
    {
        $ids = ['0A0000', '0D7NM7', 'ZZ9ZZZ', '1B3C4D', 'AB0000'];
        $i   = 0;
        $this->assertAvgMs('decode/6', static function () use ($ids, &$i) {
            OdoId::decode($ids[$i++ % count($ids)]);
        });
    }

    public function testDecodeLength7AveragesBelowLimit(): void
    {
        $ids = ['0A00000', '0A15NM7', 'ZZ9ZZZZ', '1B3C4D5', 'AB00000'];
        $i   = 0;
        $this->assertAvgMs('decode/7', static function () use ($ids, &$i) {
            OdoId::decode($ids[$i++ % count($ids)]);
        });
    }

    public function testDecodeLength8AveragesBelowLimit(): void
    {
        $ids = ['0A000000', 'ZZ9ZZZZZ', '1B3C4D5E', 'AB000000'];
        $i   = 0;
        $this->assertAvgMs('decode/8', static function () use ($ids, &$i) {
            OdoId::decode($ids[$i++ % count($ids)]);
        });
    }

    public function testGeneratorNextAveragesBelowLimit(): void
    {
        $gen = new \Webictbyleo\OdoID\OdoIDGenerator('default', 6);
        $this->assertAvgMs('generate/6', static function () use ($gen) {
            $gen->next();
        });
    }
}
