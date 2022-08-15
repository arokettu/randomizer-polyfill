<?php

declare(strict_types=1);

namespace Arokettu\Random\Tests;

use PHPUnit\Framework\TestCase;
use Random\Engine\Secure;
use Random\Randomizer;
use RuntimeException;

class EngineSecureTest extends TestCase
{
    public function testWorks(): void
    {
        $engine = new Secure();
        self::assertNotEmpty($engine->generate());
        $rnd = new Randomizer($engine);
        $int = $rnd->getInt(1, 1000);
        self::assertGreaterThanOrEqual(1, $int);
        self::assertLessThanOrEqual(1000, $int);
    }

    public function testNonSerializable(): void
    {
        try {
            serialize(new Secure());
        } catch (\Throwable $e) {
            self::assertEquals(\Exception::class, get_class($e));
            self::assertEquals("Serialization of 'Random\Engine\Secure' is not allowed", $e->getMessage());
            self::assertNull($e->getPrevious());

            return;
        }

        throw new RuntimeException('Throwable expected'); // do not use expectException to test getPrevious()
    }
}
