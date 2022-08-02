<?php

/**
 * @copyright Copyright © 2022 Anton Smirnov
 * @license BSD-3-Clause https://spdx.org/licenses/BSD-3-Clause.html
 *
 * Includes adaptation of C code from the PHP Interpreter
 * @license PHP-3.01 https://spdx.org/licenses/PHP-3.01.html
 * @see https://github.com/php/php-src/blob/master/ext/random/engine_pcgoneseq128xslrr64.c
 */

declare(strict_types=1);

namespace Random\Engine;

use Exception;
use GMP;
use Random\Engine;
use RuntimeException;
use Serializable;
use TypeError;
use ValueError;

use function array_is_list;
use function bin2hex;
use function count;
use function get_debug_type;
use function gmp_export;
use function gmp_import;
use function gmp_init;
use function is_int;
use function is_string;
use function random_bytes;
use function str_split;
use function strlen;

final class PcgOneseq128XslRr64 implements Engine, Serializable
{
    use Shared\Serialization;

    private const SIZEOF_UINT128_T = 16;
    private const SIZEOF_UINT64_T = 8;
    private const BITS_64 = 64; // obviously

    /** @var GMP|null 128-bit bitmask */
    private static $UINT128_MASK = null;
    /** @var GMP|null 64-bit bitmask */
    private static $UINT64_MASK = null;
    /** @var GMP|null */
    private static $STEP_MUL_CONST = null;
    /** @var GMP|null */
    private static $STEP_ADD_CONST = null;

    /**
     * @var GMP state
     * @psalm-suppress PropertyNotSetInConstructor Psalm doesn't traverse several levels apparently
     */
    private $state;

    /**
     * @param string|int|null $seed
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function __construct($seed = null)
    {
        $this->initConst();

        if (is_int($seed)) {
            $this->seedInt($seed);
            return;
        }

        if ($seed === null) {
            try {
                $seed = random_bytes(self::SIZEOF_UINT128_T);
            } catch (Exception $e) {
                throw new RuntimeException('Failed to generate a random seed');
            }
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType we don't trust user input */
        if (is_string($seed)) {
            if (strlen($seed) !== self::SIZEOF_UINT128_T) {
                throw new ValueError(__METHOD__ . '(): Argument #1 ($seed) state strings must be 16 bytes');
            }

            $this->seedString($seed);
            return;
        }

        throw new TypeError(
            __METHOD__ .
            '(): Argument #1 ($seed) must be of type string|int|null, ' .
            get_debug_type($seed) . ' given'
        );
    }

    /**
     * @psalm-suppress TraitMethodSignatureMismatch abstract private is 8.0+
     */
    private function initConst(): void
    {
        if (self::$UINT128_MASK === null) {
            self::$UINT128_MASK = gmp_init('ffffffffffffffffffffffffffffffff', 16);
        }
        if (self::$UINT64_MASK === null) {
            self::$UINT64_MASK = gmp_init('ffffffffffffffff', 16);
        }
        if (self::$STEP_MUL_CONST === null) {
            self::$STEP_MUL_CONST =
                gmp_init('2549297995355413924', 10) << self::BITS_64 |
                gmp_init('4865540595714422341', 10);
        }
        if (self::$STEP_ADD_CONST === null) {
            self::$STEP_ADD_CONST =
                gmp_init('6364136223846793005', 10) << self::BITS_64 |
                gmp_init('1442695040888963407', 10);
        }
    }

    private function seedInt(int $seed): void
    {
        $this->seed128(gmp_init($seed));
    }

    private function seedString(string $seed): void
    {
        $this->seed128(gmp_import($seed, self::SIZEOF_UINT64_T, GMP_LITTLE_ENDIAN | GMP_MSW_FIRST));
    }

    private function seed128(GMP $seed): void
    {
        $this->state = gmp_init(0);
        $this->step();
        $this->state = ($this->state + $seed) & self::$UINT128_MASK;
        $this->step();
    }

    private function step(): void
    {
        $this->state = ($this->state * self::$STEP_MUL_CONST + self::$STEP_ADD_CONST) & self::$UINT128_MASK;
    }

    public function generate(): string
    {
        $this->step();
        return $this->rotr64($this->state);
    }

    private function rotr64(GMP $state): string
    {
        $hi = $state >> self::BITS_64;
        $lo = $state & self::$UINT64_MASK;

        $v = $hi ^ $lo;
        $s = $hi >> 58;

        $result = ($v >> $s) | ($v << (-$s & 63));
        $result &= self::$UINT64_MASK;

        return gmp_export($result, self::SIZEOF_UINT64_T, GMP_LITTLE_ENDIAN | GMP_LSW_FIRST);
    }

    public function jump(int $advance): void
    {
        /** @var GMP $curMult for psalm */
        $curMult = self::$STEP_MUL_CONST;
        $curPlus = self::$STEP_ADD_CONST;
        $accMult = gmp_init(1);
        $accPlus = gmp_init(0);

        if ($advance < 0) {
            throw new ValueError(__METHOD__ . '(): Argument #1 ($advance) must be greater than or equal to 0');
        }

        while ($advance > 0) {
            if ($advance & 1) {
                $accMult = ($accMult * $curMult) & self::$UINT128_MASK;
                $accPlus = ($accPlus * $curMult + $curPlus) & self::$UINT128_MASK;
            }
            $curPlus = (($curMult + 1) * $curPlus) & self::$UINT128_MASK;
            $curMult = gmp_pow($curMult, 2) & self::$UINT128_MASK;

            $advance >>= 1;
        }

        $this->state = ($accMult * $this->state + $accPlus) & self::$UINT128_MASK;
    }

    /**
     * @psalm-suppress TraitMethodSignatureMismatch abstract private is 8.0+
     */
    private function getStates(): array
    {
        return str_split(bin2hex(
            gmp_export($this->state, self::SIZEOF_UINT64_T, GMP_LITTLE_ENDIAN | GMP_MSW_FIRST)
        ), self::SIZEOF_UINT64_T * 2);
    }

    /**
     * @psalm-suppress TraitMethodSignatureMismatch abstract private is 8.0+
     * @throws Exception
     */
    private function loadStates(array $states): void
    {
        if (!array_is_list($states) || count($states) < 2) {
            throw new Exception("Engine serialize failed");
        }
        [$hi, $lo] = $states;
        if (strlen($hi) !== self::SIZEOF_UINT64_T * 2 || strlen($lo) !== self::SIZEOF_UINT64_T * 2) {
            throw new Exception("Engine serialize failed");
        }
        $this->state = gmp_import(hex2bin($lo . $hi), self::SIZEOF_UINT64_T, GMP_LITTLE_ENDIAN | GMP_LSW_FIRST);
    }
}