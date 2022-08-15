<?php

declare(strict_types=1);

namespace Arokettu\Random\Tests\DevEngines;

use Random\Engine;

/**
 * @see https://github.com/php/php-src/blob/master/ext/random/tests/02_engine/all_serialize_user.phpt
 */
final class User64 implements Engine
{
    /** @var int */
    private $count = 0;

    public function generate(): string
    {
        return \pack('P*', ++$this->count);
    }
}
