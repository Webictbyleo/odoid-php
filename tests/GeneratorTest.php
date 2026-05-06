<?php

declare(strict_types=1);

namespace Webictbyleo\OdoID\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webictbyleo\OdoID\Charsets;
use Webictbyleo\OdoID\OdoId;
use Webictbyleo\OdoID\OdoIDGenerator;
use Webictbyleo\OdoID\UnsupportedLengthException;

final class GeneratorTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $g = new OdoIDGenerator();
        $this->assertSame('default', $g->namespace);
        $this->assertSame(6, $g->length);
    }

    public function testCustomNamespaceAndLength(): void
    {
        $g = new OdoIDGenerator(namespace: 'acme', length: 8);
        $this->assertSame('acme', $g->namespace);
        $this->assertSame(8, $g->length);
    }

    #[DataProvider('supportedLengths')]
    public function testCapacityMatchesMax(int $length): void
    {
        $g = new OdoIDGenerator(length: $length);
        $this->assertSame(Charsets::MAX[$length], $g->capacity);
    }

    public function testUnsupportedLengthThrowsException(): void
    {
        $this->expectException(UnsupportedLengthException::class);
        new OdoIDGenerator(length: 5);
    }

    public function testNextResultHasCorrectShape(): void
    {
        $g = new OdoIDGenerator(namespace: 'test', length: 6);
        $r = $g->next();
        $this->assertArrayHasKey('id', $r);
        $this->assertArrayHasKey('n', $r);
        $this->assertArrayHasKey('length', $r);
        $this->assertArrayHasKey('namespace', $r);
        $this->assertNotEmpty($r['id']);
        $this->assertSame(6, $r['length']);
        $this->assertSame('test', $r['namespace']);
    }

    #[DataProvider('supportedLengths')]
    public function testNextIdHasCorrectLength(int $length): void
    {
        $g = new OdoIDGenerator(length: $length);
        $this->assertSame($length, strlen($g->next()['id']));
    }

    public function testNextIdIsUppercaseAlphanumeric(): void
    {
        $g = new OdoIDGenerator(length: 8);
        for ($i = 0; $i < 50; $i++) {
            $id = $g->next()['id'];
            $this->assertSame(strtoupper($id), $id, "ID not uppercase: {$id}");
            $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $id,
                "ID contains non-alphanumeric: {$id}");
        }
    }

    public function testExcludedCharsNeverInOutput(): void
    {
        $g = new OdoIDGenerator(length: 8);
        for ($i = 0; $i < 50; $i++) {
            $id = $g->next()['id'];
            $this->assertStringNotContainsString('I', $id, "{$id} contains I");
            $this->assertStringNotContainsString('L', $id, "{$id} contains L");
            $this->assertStringNotContainsString('O', $id, "{$id} contains O");
        }
    }

    #[DataProvider('supportedLengths')]
    public function testNextNIsInValidRange(int $length): void
    {
        $g   = new OdoIDGenerator(length: $length);
        $max = Charsets::MAX[$length];
        for ($i = 0; $i < 50; $i++) {
            $n = $g->next()['n'];
            $this->assertGreaterThanOrEqual(0, $n);
            $this->assertLessThan($max, $n, "n={$n} out of range for length {$length}");
        }
    }

    public function testNextNMatchesDecodeOfId(): void
    {
        $g = new OdoIDGenerator(namespace: 'verify', length: 6);
        for ($i = 0; $i < 20; $i++) {
            $r = $g->next();
            $this->assertSame($r['n'], OdoId::decode($r['id']));
        }
    }

    public function testMonotonicSequencing_sameTickProducesDistinctIds(): void
    {
        // Use a future epoch (60s ahead) so tick stays at 0 for all calls
        $futureEpoch = (int)(microtime(true) * 1000) + 60_000;
        $g = new OdoIDGenerator(namespace: 'seq-test', epoch: $futureEpoch);

        $seen = [];
        for ($i = 0; $i < 20; $i++) {
            $id = $g->next()['id'];
            $this->assertArrayNotHasKey($id, $seen, "Duplicate ID: {$id}");
            $seen[$id] = true;
        }
    }

    public function testNamespaceIsolation(): void
    {
        $futureEpoch = (int)(microtime(true) * 1000) + 60_000;
        $g1 = new OdoIDGenerator(namespace: 'ns-a', length: 8, epoch: $futureEpoch);
        $g2 = new OdoIDGenerator(namespace: 'ns-b', length: 8, epoch: $futureEpoch);
        $this->assertNotSame($g1->next()['id'], $g2->next()['id']);
    }

    public function testProxyEncodeUsesGeneratorLength(): void
    {
        $g = new OdoIDGenerator(length: 7);
        $this->assertSame(7, strlen($g->encode(0)));
    }

    public function testProxyDecodeReturnsCorrectN(): void
    {
        $g = new OdoIDGenerator(length: 6);
        $r = $g->next();
        $this->assertSame($r['n'], $g->decode($r['id']));
    }

    public static function supportedLengths(): array
    {
        return [[6], [7], [8]];
    }
}
