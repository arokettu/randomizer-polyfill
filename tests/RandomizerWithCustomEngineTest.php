<?php

declare(strict_types=1);

namespace Arokettu\Random\Tests;

use Arokettu\Random\Tests\DevEngines\SingleByte;
use Arokettu\Random\Tests\DevEngines\Xorshift32;
use Arokettu\Random\Tests\DevEngines\Zeros;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;

class RandomizerWithCustomEngineTest extends TestCase
{
    public function testGetInt(): void
    {
        $testMatrix = [
            -59034 => [
                14063, 62020, 2777, 64694, -61324, -5558, -27594, 4191, 16313, 64364, -31667, -33181, -44517, -31748,
                17574, 29263, -17059, -59906, -55214, 55435, -23048, 21980, -51981, -20446, 49034, 43681, 11518, 12780,
                26338, 3915, -54374, -34972, 20856, 2915, 16384, -16055, -20821, -3304, -20666, -22131, -35699, 29900,
                -3254, 13813, -58887, 43987, -44090, -23416, -54591, 45215, -26641, 15502, -16533, 224, -58166, -10092,
                -61311, 18624, -6227, -24331, -60070, -41242, 1415, 23267, 12559, -36101, -18699, -15034, 12183, 58980,
                45985, 30005, 14277, 11606, -62980, 42172, 1999, 65159, -14901, -60600, 2195, -8276, 52714, 11451,
                -20089, 56985, -1986, 15450, 3652, 25883, -33473, 22797, 18152, 23610, -33231, -46344, 8745, -27422,
                -27876, 48575,
            ],
            17483 => [
                8687, -59266, 36949, 31714, 58144, 29995, 50802, -27486, -30764, -42539, 43471, 24424, -62082, -37607,
                -21505, 57114, -42924, -16081, -55210, 56637, 31109, -48812, 44123, -45206, -29210, -9672, 33292, -9256,
                -24344, -25664, -17179, 62958, 33425, -8473, 49001, 54384, 56726, 13172, -60621, -51078, 33550, -2352,
                -53500, -25917, 23433, -27023, -5732, 23714, -7591, 46029, -7487, 17342, -10806, -43607, -32481, 3906,
                -13659, 28639, 4851, 20079, -14725, 38075, 49664, -22918, -36839, 41362, -60581, -49038, -40813, 948,
                61195, 38750, 11545, -34534, 38485, 56468, 26430, -18682, 53295, 55145, -25928, -5196, 56466, -58713,
                62627, 20094, -5507, -51651, -50128, 28145, -24923, 21829, 21577, -53844, 44329, 40114, 24909, -52203,
                54738, -6098,
            ],
            -7694 => [
                41554, 5836, 37974, 63301, -31429, 55239, 25581, 28353, -2172, -19402, 60009, 28233, -41499, 58111,
                63182, -50862, -57232, 13285, -40788, -11699, 8602, -10173, -21012, 2461, -60204, 30953, -13897, 24839,
                38742, -52624, -10296, 38434, -23424, 55366, -47565, 463, 14389, -8822, -20461, -22303, 40470, -59711,
                -13691, 28380, -42367, -32817, -31553, -22913, -57577, 41183, 12396, 8317, -51221, -1258, -34274,
                -54563, 40444, 17757, -32451, 7644, -21253, -56686, 23669, -28023, 8701, -10710, -20279, 23650, -6982,
                8459, 64112, 47499, 59245, 62607, -1385, 49001, -20191, 23085, -37059, -36118, 14287, -7724, 53454,
                -14106, 21649, -27244, -15988, 59368, -27558, 61131, 15848, -42682, 12255, -64399, 19520, 41947, -3323,
                24003, 35226, 50589,
            ],
            -63067 => [
                32448, -10625, 40464, -26614, 37400, -13832, -40613, 52456, 1451, -57728, 53304, 13727, -58925, -37237,
                54587, -56671, 15753, 30346, -22081, -28516, -56731, -24570, -39363, -45627, -56716, -32134, -15351,
                7522, -43882, -63957, -56617, -6786, -13132, -54078, -7946, 31230, -53555, -8752, -34536, -14953, 49903,
                59612, 28265, 17391, -52456, 19427, -36341, -6718, 6566, -32211, 46002, -33340, -13188, -19287, -47280,
                6119, 56255, -64818, 52116, 3073, -22465, 37873, 53418, 44936, 59227, 14553, -32309, 43102, 61097,
                18414, -37449, 38343, -23984, -25979, 53979, -39030, 22144, 55065, 45140, 15808, 53441, 62988, 57836,
                -19905, 28728, 64138, 39311, -64690, 52087, -47911, 25481, 24332, -15571, 52419, 51022, -50314, -32251,
                61936, 20086, 38047,
            ],
            43492 => [
                -46022, -24997, 31126, -12970, 20812, 44235, -16728, 52076, 6898, 18889, -63958, 19255, -60089, 51675,
                -64207, 64687, 2417, 13431, 2136, -19484, 45407, -9836, -33758, -35449, -36156, -3763, 36778, -61708,
                -62563, -28564, 17292, 17698, 13215, -23640, 63218, 38749, 20007, -42051, 2983, -25875, 50353, -52717,
                39374, 41187, 45439, -46984, -57793, -22947, 1325, 11508, 18145, 12051, -18739, 52225, -34603, -4187,
                34820, 56809, 21394, -56968, 64482, -20938, -12447, 1880, 14353, 4652, -42653, 50783, 14128, -52673,
                2920, -9219, -39742, 8234, 176, 6641, -38949, -20670, -7127, 250, 46386, 11977, -18126, -5966, 7213,
                -6916, -44101, 63284, -52651, -23675, -34939, 62062, -26539, -17129, -24363, -2268, -50064, 4446,
                -63473, -6382,
            ],
        ];

        foreach ($testMatrix as $seed => $nums) {
            $rnd = new Randomizer(new Xorshift32($seed));

            for ($i = 0; $i < 100; $i++) {
                $num = $rnd->getInt(-65536, 65535);
                self::assertEquals($nums[$i], $num, "Seed: $seed Index: $i");
            }
        }
    }

    public function testNextInt(): void
    {
        $testMatrix = [
            -8234 => [
                39015338, 753332377, 743241107, 757924220, 760244301, 727761985, 251087637, 355132207, 548267656,
                333577846, 701113425, 88104129, 366288413, 885004230, 824395658, 596735763, 588461290, 876655501,
                605114405, 212402895,
            ],
            -47519 => [
                951019900, 631018556, 343603585, 571689140, 733903862, 1020631894, 945081785, 496911432, 939431603,
                3567831, 196902897, 202110912, 747383193, 509392278, 354573777, 227815725, 1031871061, 129831712,
                519776332, 94634773,
            ],
            34864 => [
                418073449, 21632762, 826122544, 644862989, 19080490, 357210841, 69506033, 487433052, 94423567, 12954511,
                54291429, 106644597, 879329535, 179976772, 866462493, 236359614, 600394142, 363397267, 47588238,
                308018029,
            ],
            14567 => [
                843307812, 768522583, 644661381, 838043867, 502660431, 702382145, 593508827, 791684406, 424210269,
                281734300, 645282864, 536196552, 14191790, 954582296, 144470603, 477439233, 578171931, 979343937,
                736126874, 91727475,
            ],
            -44941 => [
                367736309, 434509759, 959364295, 947726348, 854221026, 540269737, 796577322, 558836693, 42250303,
                82445758, 266679680, 66262122, 246865525, 980872681, 514858908, 892577437, 1038142413, 934693297,
                780974273, 184948091,
            ],
        ];

        foreach ($testMatrix as $seed => $nums) {
            $rnd = new Randomizer(new Xorshift32($seed));

            for ($i = 0; $i < 20; $i++) {
                $num = $rnd->nextInt();
                self::assertEquals($nums[$i], $num, "Seed: $seed Index: $i");
            }
        }
    }

    public function testGetBytes(): void
    {
        $testMatrix = [
            33664 => [
                'b8', '2364', '748348', '8704e752', '3c4a887cbb', '18d09059f1cb', 'b818ee1ac4bcdf', '87b8a1393fa49a58',
                '1104b50fca42d149ce', '152a004034be176ddc58', '6a68755616af7a149ab491', 'fb05584488b60f58079d0a75',
                '12a4e773a013d96bb6d90b7c28', '351812445fcc83653b690c1dee24', 'a55c0d4bc951a073a5edb04ac7a288',
                'cf36da598ef0787f7a07494c09546878', 'dde2c02f1039f040d945913dcc866730f3',
                '2190763598f0220900bb0a31958e3b2bc17f', 'f930182eba9be72832388e29b6a1813fcde95b',
                '7c762b0cce267c3e1c881a6e90e53b5c544fb731',
            ],
            -43196 => [
                '2f', 'fbaa', '59a4bc', '8247c011', '5a68313fa7', 'c293161dd57a', '2048b3109beb59', '4ee37c3744eeb349',
                '59fb153ba6f5a50bee', '9ab64843b756e0662c95', 'ad282230f6f4c1537949bf', '91a89f5d37063e39ab83e346',
                'e0e44964cad96c426019e373e7', '8f0b294dc36943673e931a458048', '035c0c09e5950b53fe825317785e82',
                '1e63e2649e5cb7388c14b237bd6d5651', '6dbad90047351445b9672234b3017a6235',
                '17ef1e5749477d55e3691f4fb2064066d9ca', '58777c0df3a4797100261b794d35be26860de3',
                'cf492a6906816422a45ccf7a2955213a0bd00b00',
            ],
            63589 => [
                '83', '7811', 'd97547', 'c74d7146', '43defa169a', '732c4f397320', '42708d7c003926', 'e36bc5655fd89c5f',
                '94587456c732b871d7', '54ece172c13702223c92', 'c0ded91640c0380dd89129', '71bb4023a6adfe31e3af016d',
                '3d9041475e59875d08c33c6366', '91d67660a393396e256df6558ce0', 'a1b7730d22e6720bd9326721a719ba',
                '60c45712add0a531d5dbbe7d577e497e', '5664f561c9df8f62329da8379529d72507',
                '9f1dfa105b53733ab7711b53016b9c181f7e', '44144613c328196823a62c6d37a0f744af51da',
                'd8f81f799a809b461ea0936716539d61c96b0934',
            ],
            51298 => [
                '64', '2c30', '2e6a37', 'f7e1221c', 'd91e9843f8', '17d7e2537741', '83ad3a44866dd2', 'd7fd3141d270ef2f',
                '2815577ff161b123f6', '8abe51418a7c9826ce29', 'c407a82fec9d4245f3c41d', '721af0418d1e71157d1eb462',
                'c0560a4e89c1981e7db9955320', '8d077359ccd2d5094a2a05426c64', '697c102bc78a3437d147e36bfdad35',
                '38c0ce64dc15f22578697d0ef1621d49', '507a2d4d03278e2eb434a027cf21e20762',
                '97485b41d32a7e21e160ab60fa915f1a8a5c', 'fc44481277fb2e40378e4277d5d30b764dc85f',
                '26e99c113ac0bd1b07ade854c3d94568dd9fcf3c',
            ],
            -59300 => [
                'e6', '9b69', '343eba', '4ab8ef01', '19a43e2aa7', '1849bf1c1609', '4e9b2961ae3f4e', '13ffc16ac2055571',
                'b4cb406ccf54f8539f', 'afed8d53d36c3f29a1fe', '2bd04b350c669c266297e3', '4502f4727bf82f250b18361e',
                '9084dd7bd787bb221790681a62', '36033f7e5a4f6055ff02ef4dc770', '9cf9616315a1185608217e054735bf',
                'ccbaed0ed6e4300f638def66823e3f7f', '552c0105101ef831ade67a753a1d451cab',
                '4403301be8534970b2a0bb1c6448177d498f', '8c1dd937b8607a19ce0bc4781015034e406b81',
                '3457c52885125946a1903965d7fd9e32e543a149',
            ],
        ];

        foreach ($testMatrix as $seed => $strings) {
            $rnd = new Randomizer(new Xorshift32($seed));

            for ($i = 0; $i < 20; $i++) {
                $num = \bin2hex($rnd->getBytes($i + 1));
                self::assertEquals($strings[$i], $num, "Seed: $seed Index: $i");
            }
        }
    }

    public function testShuffleArray(): void
    {
        $array = \range(1, 100);

        $testMatrix = [
            -4850 => [
                49, 34, 93, 47, 63, 7, 6, 97, 41, 27, 46, 74, 69, 35, 55, 71, 32, 52, 51, 15, 48, 90, 99, 45, 16, 83,
                66, 5, 86, 28, 30, 68, 79, 80, 64, 44, 91, 11, 88, 89, 100, 87, 82, 37, 72, 31, 77, 9, 62, 33, 50, 22,
                54, 76, 12, 85, 24, 18, 8, 98, 73, 60, 70, 43, 95, 75, 78, 4, 25, 96, 29, 81, 57, 36, 23, 59, 84, 40,
                14, 53, 56, 1, 61, 42, 65, 38, 26, 20, 21, 10, 3, 67, 17, 58, 13, 19, 94, 39, 92, 2,
            ],
            -35518 => [
                28, 59, 21, 15, 27, 61, 18, 38, 71, 78, 5, 65, 23, 31, 10, 50, 42, 53, 17, 25, 49, 81, 96, 84, 91, 57,
                89, 60, 13, 79, 30, 6, 66, 35, 39, 14, 19, 86, 93, 43, 68, 83, 26, 9, 85, 72, 3, 12, 82, 33, 97, 20, 48,
                22, 40, 11, 58, 36, 64, 1, 100, 99, 76, 56, 80, 16, 52, 7, 92, 69, 51, 41, 94, 24, 70, 88, 74, 87, 37,
                63, 4, 8, 90, 34, 29, 62, 55, 46, 73, 32, 44, 95, 2, 98, 45, 47, 67, 75, 77, 54,
            ],
            41380 => [
                38, 37, 91, 51, 20, 11, 33, 82, 93, 47, 55, 18, 27, 52, 57, 30, 9, 81, 29, 24, 21, 50, 84, 15, 13, 10,
                75, 98, 99, 86, 45, 41, 53, 88, 25, 87, 48, 58, 36, 26, 66, 6, 72, 17, 65, 56, 64, 97, 62, 77, 74, 49,
                80, 3, 83, 5, 54, 28, 23, 92, 1, 90, 69, 43, 32, 100, 61, 22, 71, 59, 44, 34, 14, 19, 76, 2, 96, 4, 35,
                42, 8, 73, 7, 95, 40, 79, 78, 46, 60, 67, 85, 89, 70, 63, 94, 16, 12, 39, 68, 31,
            ],
            -6629 => [
                97, 24, 14, 96, 52, 41, 71, 73, 77, 74, 54, 98, 53, 58, 33, 75, 40, 16, 55, 29, 5, 60, 66, 22, 92, 27,
                2, 26, 86, 57, 1, 88, 91, 93, 85, 63, 64, 45, 4, 56, 10, 67, 18, 70, 79, 30, 35, 19, 90, 28, 62, 94, 68,
                7, 42, 95, 11, 100, 17, 78, 47, 48, 82, 83, 15, 13, 61, 6, 31, 76, 25, 87, 50, 3, 80, 99, 37, 12, 72,
                21, 89, 46, 65, 8, 9, 43, 23, 84, 51, 32, 20, 81, 39, 59, 34, 36, 44, 49, 69, 38,
            ],
            61842 => [
                16, 70, 80, 3, 66, 72, 20, 1, 73, 56, 87, 91, 24, 42, 15, 21, 65, 2, 34, 44, 43, 7, 50, 14, 92, 90, 27,
                62, 4, 67, 13, 22, 12, 28, 94, 46, 23, 29, 58, 57, 68, 40, 82, 59, 35, 38, 51, 84, 9, 78, 52, 96, 19,
                98, 33, 85, 55, 86, 63, 69, 17, 77, 83, 10, 8, 53, 60, 99, 93, 6, 79, 5, 95, 89, 61, 97, 64, 18, 47, 74,
                49, 36, 75, 71, 48, 39, 76, 45, 41, 25, 37, 31, 26, 100, 54, 32, 81, 11, 30, 88,
            ],
        ];

        foreach ($testMatrix as $seed => $shuffledExpected) {
            $rnd = new Randomizer(new Xorshift32($seed));

            $shuffled = $rnd->shuffleArray($array);
            self::assertEquals($shuffledExpected, $shuffled, "Seed: $seed");
        }
    }

    public function testShuffleString(): void
    {
        $string = \implode(\range('a', 'z')) . \implode(\range('0', 9)) . \implode(\range('A', 'Z'));

        $testMatrix = [
            -35487 => 'bvzajxEifA4VDopFm3yr6Sh7Mg9XOnUqc8G50TLZIYe2BNwtlkHPQCduWJR1sK',
            39925  => 'y0b47gCAOfvLSXEBqkQwRKaM5DcNtpI69zh1JPYi2TZdxnrG3sFmHjUWoVle8u',
            -27577 => 'gZAtUCiSFdnQVwhjycb7XWP2D65ufxYseImrKJBR4qLHpEkT1volNzO3G9aM80',
            -38362 => 'fWza4SI085ir3vDyehpnsm97dUGAt6oREPj2kHBMVCgwuxJbYO1LQXcZlKqNTF',
            -54215 => '1etOCY3Rf74yusBU9k5pS0IKzcqnmMQvWrlFXVjD6i8NdJPZgGhEbxoaw2TALH',
        ];

        foreach ($testMatrix as $seed => $shuffledExpected) {
            $rnd = new Randomizer(new Xorshift32($seed));

            $shuffled = $rnd->shuffleBytes($string);
            self::assertEquals($shuffledExpected, $shuffled, "Seed: $seed");
        }
    }

    public function testPickKeys(): void
    {
        // try to be accurate at least on packed arrays
        $array = \array_flip(\array_merge(\range('a', 'z'), \range('0', 9), \range('A', 'Z')));

        $testMatrix = [
            63250 => [
                ['r'],
                ['c', '0'],
                ['3', 'M', 'Q'],
                ['p', 'u', '6', 'B'],
                ['e', 'p', 'v', 'B', 'O'],
                ['a', 'f', 'l', 'v', '7', 'U'],
                ['k', 'q', 't', 'I', 'J', 'M', 'U'],
                ['c', 'd', 'o', '1', '8', 'P', 'Q', 'W'],
                ['e', 'i', 'y', '1', '4', 'H', 'O', 'S', 'W'],
                ['h', 'j', 't', 'v', 'w', '0', '2', '8', 'A', 'Q'],
                ['m', 'o', 'q', 't', 'y', '2', '8', 'I', 'Q', 'T', 'U'],
                ['d', 'h', 'l', 'm', 'o', 'x', 'z', '7', 'A', 'C', 'D', 'F'],
                ['a', 'b', 'c', 'g', 'q', 'v', 'z', '6', 'D', 'N', 'U', 'W', 'Z'],
                ['b', 'j', 'l', 'q', 'w', '2', '3', '4', 'B', 'D', 'E', 'J', 'N', 'Y'],
                ['l', 'm', 'x', 'z', '6', '8', 'A', 'E', 'H', 'K', 'M', 'Q', 'T', 'U', 'X'],
                ['f', 'h', 'i', 'k', 'm', 'n', 't', 'v', 'y', '0', '3', 'C', 'N', 'Q', 'S', 'Y'],
                ['f', 'g', 'j', 't', 'u', '0', '5', '6', 'A', 'C', 'H', 'J', 'O', 'S', 'V', 'X', 'Z'],
                ['c', 'g', 'i', 'j', 's', 'w', 'y', 'z', '1', '4', '9', 'K', 'L', 'M', 'O', 'R', 'S', 'Y'],
                ['d', 'f', 'n', 'r', 's', 'v', 'w', 'x', '0', '1', '3', '8', 'A', 'B', 'C', 'G', 'I', 'Q', 'Z'],
                ['d', 'e', 'j', 'l', 'p', 't', 'v', 'x', 'z', '0', '1', '3', '6', '7', '9', 'L', 'P', 'Q', 'T', 'Y'],
            ],
            19413 => [
                ['M'],
                ['7', 'R'],
                ['a', '0', 'U'],
                ['b', 'i', 'k', 'M'],
                ['j', '5', 'P', 'X', 'Z'],
                ['f', 'm', '0', '5', 'A', 'I'],
                ['f', 'q', 'u', '2', 'B', 'G', 'X'],
                ['f', 'i', 'J', 'M', 'N', 'Q', 'V', 'Y'],
                ['h', 'n', '0', '4', '7', 'J', 'S', 'V', 'Y'],
                ['f', 't', 'y', 'z', '2', '6', 'C', 'D', 'R', 'V'],
                ['d', 'f', 'h', 'k', 'o', 'y', '6', 'C', 'L', 'P', 'S'],
                ['m', 'r', 's', 'x', '5', '8', '9', 'B', 'K', 'O', 'W', 'X'],
                ['d', 's', 'v', '6', '7', 'D', 'E', 'F', 'G', 'H', 'K', 'V', 'Y'],
                ['e', 'h', 'v', 'x', 'y', '3', '7', 'B', 'E', 'K', 'M', 'Q', 'V', 'X'],
                ['b', 'h', 'k', 'm', 'n', 't', 'u', 'y', 'z', '3', '6', '7', '8', 'C', 'W'],
                ['a', 'c', 'j', 'p', 'u', 'v', 'z', 'C', 'E', 'G', 'J', 'K', 'L', 'M', 'O', 'S'],
                ['b', 'e', 'i', 'n', 'u', 'y', '5', '7', 'A', 'D', 'G', 'H', 'I', 'J', 'T', 'U', 'V'],
                ['b', 'd', 'e', 'g', 'k', 'n', 'q', '2', '3', 'A', 'E', 'H', 'O', 'Q', 'S', 'U', 'W', 'Y'],
                ['c', 'e', 'l', 'm', 'n', 'o', 'q', 'v', 'w', 'x', '7', 'B', 'D', 'G', 'N', 'S', 'V', 'X', 'Y'],
                ['a', 'j', 'm', 'n', 'o', 'p', 'r', 't', 'u', 'v', 'w', '2', '5', '7', 'E', 'F', 'H', 'K', 'O', 'T'],
            ],
            -3227 => [
                ['M'],
                ['p', 'F'],
                ['5', 'N', 'W'],
                ['d', 'q', 'P', 'V'],
                ['a', '8', 'I', 'J', 'O'],
                ['n', '3', 'C', 'L', 'S', 'Y'],
                ['i', 'k', 'l', 'y', '2', 'A', 'U'],
                ['a', 'o', '2', '7', '8', 'I', 'S', 'X'],
                ['e', 'g', 'k', 'A', 'F', 'G', 'I', 'J', 'O'],
                ['j', 'm', 'x', 'z', '1', '6', '7', 'B', 'H', 'X'],
                ['g', 'j', 'u', '0', '1', '7', 'F', 'I', 'K', 'L', 'M'],
                ['m', 'o', 's', 'v', 'x', '0', '6', '8', '9', 'E', 'O', 'P'],
                ['i', 'k', 'o', 'p', 'A', 'B', 'G', 'J', 'O', 'T', 'W', 'X', 'Y'],
                ['a', 'g', 'h', 'j', 'n', 'o', 'u', 'y', '0', '3', '6', 'G', 'P', 'S'],
                ['a', 'i', 'j', 'k', 'r', 'v', 'w', '0', '2', '3', '5', 'E', 'H', 'K', 'U'],
                ['f', 'j', 'm', 'o', 's', 'u', 'w', '5', '9', 'A', 'C', 'D', 'I', 'L', 'S', 'X'],
                ['k', 'o', 'w', '2', '3', '7', '9', 'A', 'C', 'H', 'L', 'N', 'P', 'R', 'S', 'T', 'X'],
                ['d', 'e', 'j', 'k', 'n', 'q', 'r', 'w', '6', '8', '9', 'B', 'G', 'L', 'S', 'U', 'W', 'Z'],
                ['n', 'q', 'v', 'x', 'y', 'z', '0', '5', '7', 'D', 'F', 'H', 'J', 'K', 'N', 'O', 'S', 'W', 'Z'],
                ['c', 'd', 'g', 'j', 'l', 'm', 'o', 'p', 'q', 'r', 'w', 'y', 'A', 'C', 'D', 'M', 'Q', 'S', 'Y', 'Z'],
            ],
            6752 => [
                ['w'],
                ['t', 'N'],
                ['o', 'P', 'S'],
                ['b', 'd', 'k', 'G'],
                ['f', 'm', 'v', 'G', 'L'],
                ['j', 't', '6', 'A', 'F', 'I'],
                ['e', 'x', 'I', 'M', 'O', 'U', 'V'],
                ['d', 'j', 'n', 'o', 'v', '4', 'R', 'V'],
                ['d', 'k', 'v', 'A', 'C', 'G', 'L', 'Q', 'W'],
                ['b', 'j', 'k', 'n', 'q', 's', '3', '9', 'N', 'Q'],
                ['b', 'e', 'j', 'n', 'o', 'r', 's', 'D', 'K', 'O', 'Z'],
                ['a', 'f', 'l', 'v', 'x', 'y', '2', '4', 'L', 'M', 'V', 'W'],
                ['a', 'o', 'p', 's', 't', 'x', 'z', 'D', 'E', 'K', 'N', 'O', 'Z'],
                ['f', 'k', 'r', '1', '4', '5', '8', '9', 'F', 'G', 'K', 'U', 'W', 'Y'],
                ['j', 'o', 'u', '1', '4', '6', '7', '8', 'A', 'C', 'O', 'P', 'S', 'T', 'Z'],
                ['b', 'g', 'h', 'n', 'p', 't', 'x', 'y', '1', '5', '8', 'D', 'E', 'N', 'S', 'T'],
                ['f', 'g', 'k', 'l', 'n', 'r', 't', 'w', '1', '6', 'A', 'K', 'L', 'M', 'P', 'Q', 'R'],
                ['c', 'e', 'g', 'j', 'k', 'l', 'm', 'n', 'u', 'y', '0', '9', 'A', 'E', 'M', 'N', 'T', 'X'],
                ['a', 'd', 'i', 'p', 's', 't', 'x', '0', '4', '5', '8', 'B', 'E', 'M', 'O', 'R', 'S', 'W', 'Z'],
                ['a', 'i', 'm', 's', 't', 'u', 'x', 'y', '0', '1', '6', '8', '9', 'A', 'B', 'D', 'H', 'J', 'K', 'R'],
            ],
            -41584 => [
                ['q'],
                ['i', 'w'],
                ['q', 'H', 'I'],
                ['u', 'K', 'O', 'Q'],
                ['e', 'o', 'B', 'C', 'P'],
                ['n', 'x', '1', '5', 'K', 'L'],
                ['i', 'x', '0', '3', 'D', 'T', 'V'],
                ['d', 'n', 'q', 'A', 'B', 'R', 'U', 'Y'],
                ['k', 'o', 'y', '3', 'D', 'I', 'Q', 'T', 'W'],
                ['l', 'm', 'r', 'F', 'G', 'K', 'R', 'T', 'U', 'V'],
                ['j', 'o', 'q', 't', 'u', '0', '6', '7', 'A', 'D', 'Q'],
                ['a', 'i', 'k', 't', 'x', '3', '4', '8', '9', 'P', 'R', 'U'],
                ['b', 'd', 'k', 'n', 'o', 'r', 'x', '1', 'I', 'K', 'Q', 'W', 'Y'],
                ['b', 'c', 'f', 'g', 'z', '1', '8', 'A', 'D', 'H', 'P', 'U', 'X', 'Z'],
                ['a', 'l', 'n', 't', 'u', 'z', '4', '7', 'A', 'C', 'D', 'I', 'K', 'O', 'X'],
                ['e', 'h', 'j', 'r', 'u', 'w', '0', '4', '5', 'B', 'C', 'D', 'G', 'I', 'L', 'O'],
                ['a', 'd', 'j', 'q', 'r', 'v', 'x', '0', '3', '4', '8', 'D', 'I', 'P', 'T', 'V', 'W'],
                ['b', 'e', 'f', 't', 'u', 'v', 'z', 'B', 'F', 'G', 'I', 'K', 'L', 'P', 'R', 'S', 'W', 'Z'],
                ['g', 'l', 'n', 'p', 'r', 't', 'u', 'x', 'z', '0', '1', '2', '8', 'D', 'E', 'F', 'G', 'M', 'Y'],
                ['k', 'l', 'r', 's', 't', 'v', 'w', '1', '2', '4', '7', '9', 'C', 'D', 'F', 'H', 'J', 'N', 'O', 'Q'],
            ],
        ];

        foreach ($testMatrix as $seed => $keysExpected) {
            $rnd = new Randomizer(new Xorshift32($seed));

            for ($i = 0; $i < 20; $i++) {
                $keys = @$rnd->pickArrayKeys($array, $i + 1);
                self::assertEquals($keysExpected[$i], $keys, "Seed: $seed Index: $i");
            }
        }
    }

    public function testSerialize(): void
    {
        $rnd1 = new Randomizer(new Xorshift32(\random_int(0, \PHP_INT_MAX)));

        $rnd1->nextInt();
        $rnd1->nextInt();

        $rnd2 = \unserialize(@\serialize($rnd1));

        self::assertEquals($rnd1->nextInt(), $rnd2->nextInt());
    }

    public function testSerializeKnown(): void
    {
        if (\PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Only 7.4+ is compatible');
        }

        // seed = 123456, 3 generates
        $serialized =
            "O:17:\"Random\\Randomizer\":1:{i:0;a:1:{s:6:\"engine\";O:43:\"Arokettu\\Random\\Tests\\DevEngines\\Singl" .
            "eByte\":1:{s:48:\"\0Arokettu\\Random\\Tests\\DevEngines\\SingleByte\0chr\";i:3;}}}";

        $rnd1 = new Randomizer(new SingleByte());
        $rnd1->nextInt();
        $rnd1->nextInt();
        $rnd1->nextInt();

        self::assertEquals($serialized, \serialize($rnd1));

        $rnd2 = \unserialize($serialized);

        self::assertEquals($rnd1->nextInt(), $rnd2->nextInt());
    }

    public function testSerializeWarning(): void
    {
        if (\PHP_VERSION_ID >= 70400) {
            $this->expectNotToPerformAssertions();
        } elseif (\method_exists($this, 'expectWarning')) { // PHPUnit 8/9
            $this->expectWarning();
            $this->expectWarningMessage('Serialized object will be incompatible with PHP 8.2');
        } else {
            $this->markTestSkipped('PHPUnit is too old for this test');
        }

        \serialize(new Randomizer(new Zeros()));
    }
}
