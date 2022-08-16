<?php

/**
 * @copyright Copyright © 2022 Anton Smirnov
 * @license BSD-3-Clause https://spdx.org/licenses/BSD-3-Clause.html
 *
 * Includes adaptation of C code from the PHP Interpreter
 * @license PHP-3.01 https://spdx.org/licenses/PHP-3.01.html
 * @see https://github.com/php/php-src/blob/eff9aed/ext/random/randomizer.c
 * @see https://github.com/php/php-src/blob/eff9aed/ext/standard/array.c
 * @see https://github.com/php/php-src/blob/eff9aed/ext/standard/string.c
 */

declare(strict_types=1);

namespace Random;

use Arokettu\Random\BigIntExportImport;
use Arokettu\Random\NoDynamicProperties;
use Closure;
use GMP;
use Random\Engine\Mt19937;
use Random\Engine\Secure;
use RuntimeException;
use Serializable;
use ValueError;

use function array_key_exists;
use function array_keys;
use function array_values;
use function count;
use function gmp_init;
use function gmp_intval;
use function serialize;
use function strlen;
use function substr;
use function trigger_error;
use function unserialize;

use const E_USER_WARNING;
use const MT_RAND_PHP;
use const SORT_NUMERIC;

/**
 * @property-read Engine $engine
 */
final class Randomizer implements Serializable
{
    use BigIntExportImport;
    use NoDynamicProperties;

    private const SIZEOF_UINT_64_T = 8;
    private const SIZEOF_UINT_32_T = 4;
    private const PHP_MT_RAND_MAX = 0x7FFFFFFF;
    private const RANDOM_RANGE_ATTEMPTS = 50;

    /** @var Engine */
    private $engine;

    public function __construct(?Engine $engine = null)
    {
        $this->initConst();

        $this->engine = $engine ?? new Engine\Secure();
    }

    private function initConst(): void
    {
        $this->initGmpConst();
    }

    private function generate(): string
    {
        $retval = $this->engine->generate();

        $size = strlen($retval);

        if ($size === 0) {
            throw new RuntimeException('Random number generation failed');
        } elseif ($size > self::SIZEOF_UINT_64_T) {
            $retval = substr($retval, 0, self::SIZEOF_UINT_64_T);
        }

        return $retval;
    }

    public function getInt(int $min, int $max): int
    {
        if ($max < $min) {
            throw new ValueError(
                __METHOD__ . '(): Argument #2 ($max) must be greater than or equal to argument #1 ($min)'
            );
        }

        // engine has range func
        if (
            $this->engine instanceof Secure
        ) {
            /** @psalm-suppress PossiblyInvalidFunctionCall */
            $result = Closure::bind(function (int $min, int $max): ?int {
                /** @psalm-suppress UndefinedMethod */
                return $this->range($min, $max);
            }, $this->engine, $this->engine)($min, $max);

            if ($result === null) {
                throw new RuntimeException('Random number generation failed');
            }

            return $result;
        }

        // handle MT_RAND_PHP
        /** @psalm-suppress PossiblyInvalidFunctionCall */
        if (
            $this->engine instanceof Mt19937 &&
            Closure::bind(function () {
                /** @psalm-suppress UndefinedThisPropertyFetch */
                return $this->mode === MT_RAND_PHP; // read private property
            }, $this->engine, $this->engine)()
        ) {
            return $this->rangeBadscaling($min, $max);
        }

        $umax = gmp_init($max) - gmp_init($min);

        // not (algo->generate_size == 0 || algo->generate_size > sizeof(uint32_t))
        $bit32 =
            $this->engine instanceof Mt19937;

        if (!$bit32 || $umax > self::$UINT32_MASK) {
            $rangeval = $this->range64($umax);
        } else {
            $rangeval = $this->range32($umax);
        }

        return gmp_intval($rangeval + $min);
    }

    private function range32(GMP $umax): GMP
    {
        $result = '';
        do {
            $result .= $this->generate();
        } while (strlen($result) < self::SIZEOF_UINT_32_T);

        $result = $this->importGmp32($result);

        if ($umax == self::$UINT32_MASK) {
            return $result;
        }

        $umax += 1;

        if (($umax & ($umax - 1)) == 0) {
            return $result & ($umax - 1);
        }

        $limit = self::$UINT32_MASK - (self::$UINT32_MASK % $umax) - 1;

        $count = 0;

        while ($result > $limit) {
            if (++$count > self::RANDOM_RANGE_ATTEMPTS) {
                throw new RuntimeException('Random number generation failed');
            }

            $result = '';
            do {
                $result .= $this->generate();
            } while (strlen($result) < self::SIZEOF_UINT_32_T);

            $result = $this->importGmp32($result);
        }

        return $result % $umax;
    }

    private function range64(GMP $umax): GMP
    {
        $result = '';
        do {
            $result .= $this->generate();
        } while (strlen($result) < self::SIZEOF_UINT_64_T);

        $result = $this->importGmp64($result);

        if ($umax == self::$UINT64_MASK) {
            return $result;
        }

        $umax += 1;

        if (($umax & ($umax - 1)) == 0) {
            return $result & ($umax - 1);
        }

        $limit = self::$UINT64_MASK - (self::$UINT64_MASK % $umax) - 1;

        $count = 0;

        while ($result > $limit) {
            if (++$count > self::RANDOM_RANGE_ATTEMPTS) {
                throw new RuntimeException('Random number generation failed');
            }

            $result = '';
            do {
                $result .= $this->generate();
            } while (strlen($result) < self::SIZEOF_UINT_64_T);

            $result = $this->importGmp64($result);
        }

        return $result % $umax;
    }

    private function rangeBadscaling(int $min, int $max): int
    {
        $n = $this->generate();
        $n = $this->importGmp32($n);
        $n = gmp_intval($n >> 1);
        // (__n) = (__min) + (zend_long) ((double) ( (double) (__max) - (__min) + 1.0) * ((__n) / ((__tmax) + 1.0)))
        return $min + (int) (( (float)$max - $min + 1.0) * ($n / (self::PHP_MT_RAND_MAX + 1.0)));
    }

    public function nextInt(): int
    {
        $result = $this->generate();
        $result = $this->importGmp64($result);

        return gmp_intval($result >> 1);
    }

    public function getBytes(int $length): string
    {
        if ($length < 1) {
            throw new ValueError(__METHOD__ . '(): Argument #1 ($length) must be greater than 0');
        }

        $retval = '';

        do {
            $result = $this->engine->generate();
            if ($result === '') {
                throw new RuntimeException('Random number generation failed');
            }
            $retval .= $result;
        } while (strlen($retval) < $length);

        return substr($retval, 0, $length);
    }

    public function shuffleArray(array $array): array
    {
        // handle empty
        if ($array === []) {
            return [];
        }

        $hash = array_values($array);
        $nLeft = count($hash);

        while (--$nLeft) {
            $rndIdx = $this->getInt(0, $nLeft);
            $tmp = $hash[$nLeft];
            $hash[$nLeft] = $hash[$rndIdx];
            $hash[$rndIdx] = $tmp;
        }

        return $hash;
    }

    public function shuffleBytes(string $bytes): string
    {
        if (strlen($bytes) <= 1) {
            return $bytes;
        }

        $nLeft = strlen($bytes);

        while (--$nLeft) {
            $rndIdx = $this->getInt(0, $nLeft);
            $tmp = $bytes[$nLeft];
            $bytes[$nLeft] = $bytes[$rndIdx];
            $bytes[$rndIdx] = $tmp;
        }

        return $bytes;
    }

    public function pickArrayKeys(array $array, int $num): array
    {
        if (!($this->engine instanceof CryptoSafeEngine)) {
            // Crypto-safe engines are not expected to produce reproducible sequences
            trigger_error('pickArrayKeys() may produce results incompatible with native ext-random', E_USER_WARNING);
        }

        if ($array === []) {
            throw new ValueError(__METHOD__ . '(): Argument #1 ($array) cannot be empty');
        }

        $numAvail = count($array);
        $keys = array_keys($array);

        if ($num === 1) {
            return [$keys[$this->getInt(0, $numAvail - 1)]];
        }

        if ($num <= 0 || $num > $numAvail) {
            throw new ValueError(
                __METHOD__ .
                    '(): Argument #2 ($num) must be between 1 and the number of elements in argument #1 ($array)'
            );
        }

        $retval = [];

        $i = $num;

        while ($i--) {
            while (true) {
                $idx = $this->getInt(0, $numAvail - 1);

                if (array_key_exists($idx, $retval) === false) {
                    $retval[$idx] = $keys[$idx];
                    break;
                }
            }
        }

        ksort($retval, SORT_NUMERIC); // sort by indexes

        return array_values($retval); // remove indexes
    }

    public function __serialize(): array
    {
        return [['engine' => $this->engine]];
    }

    public function __unserialize(array $data): void
    {
        $this->initConst();

        [$fields] = $data;
        ['engine' => $this->engine] = $fields;
    }

    public function serialize(): string
    {
        trigger_error('Serialized object will be incompatible with PHP 8.2', E_USER_WARNING);
        return serialize($this->__serialize());
    }

    /**
     * @param string $data
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function unserialize($data): void
    {
        $this->__unserialize(unserialize($data));
    }

    /**
     * @return mixed
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    public function __get(string $name)
    {
        if ($name === 'engine') {
            return $this->engine;
        }

        trigger_error('Undefined property: ' . self::class . '::$' . $name);
        return null;
    }

    public function __isset(string $name): bool
    {
        return $name === 'engine';
    }
}
