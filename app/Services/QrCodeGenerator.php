<?php

namespace App\Services;

/**
 * Minimal QR code generator — produces an SVG string.
 * Supports alphanumeric/byte mode, error correction level L.
 * No external packages required.
 */
class QrCodeGenerator
{
    /**
     * Generate a QR code SVG string for the given data.
     */
    public static function svg(string $data, int $size = 200, int $margin = 4): string
    {
        $modules = self::encode($data);
        $count = count($modules);
        $totalModules = $count + $margin * 2;
        $moduleSize = $size / $totalModules;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalModules . ' ' . $totalModules . '" width="' . $size . '" height="' . $size . '">';
        $svg .= '<rect width="' . $totalModules . '" height="' . $totalModules . '" fill="white"/>';

        for ($y = 0; $y < $count; $y++) {
            for ($x = 0; $x < $count; $x++) {
                if ($modules[$y][$x]) {
                    $svg .= '<rect x="' . ($x + $margin) . '" y="' . ($y + $margin) . '" width="1" height="1" fill="black"/>';
                }
            }
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Encode data into QR code modules (boolean grid).
     * Uses byte mode, ECC level L, auto-selects smallest version that fits.
     */
    private static function encode(string $data): array
    {
        // Version capacities for byte mode, ECC level L
        $capacities = [
            1 => 17, 2 => 32, 3 => 53, 4 => 78, 5 => 106, 6 => 134,
            7 => 154, 8 => 192, 9 => 230, 10 => 271, 11 => 321, 12 => 367,
            13 => 425, 14 => 458, 15 => 520, 16 => 586, 17 => 644, 18 => 718,
            19 => 792, 20 => 858,
        ];

        $dataLen = strlen($data);
        $version = 1;
        foreach ($capacities as $v => $cap) {
            if ($dataLen <= $cap) {
                $version = $v;
                break;
            }
        }

        $size = 17 + $version * 4;
        $modules = array_fill(0, $size, array_fill(0, $size, null));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        // Place finder patterns
        self::placeFinderPattern($modules, $isFunction, 0, 0);
        self::placeFinderPattern($modules, $isFunction, $size - 7, 0);
        self::placeFinderPattern($modules, $isFunction, 0, $size - 7);

        // Place alignment patterns
        $alignPositions = self::getAlignmentPositions($version);
        foreach ($alignPositions as $ay) {
            foreach ($alignPositions as $ax) {
                if (($ay < 9 && $ax < 9) || ($ay < 9 && $ax > $size - 9) || ($ay > $size - 9 && $ax < 9)) {
                    continue;
                }
                self::placeAlignmentPattern($modules, $isFunction, $ay, $ax);
            }
        }

        // Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $val = ($i % 2 === 0);
            if (!$isFunction[$i][6]) {
                $modules[$i][6] = $val;
                $isFunction[$i][6] = true;
            }
            if (!$isFunction[6][$i]) {
                $modules[6][$i] = $val;
                $isFunction[6][$i] = true;
            }
        }

        // Dark module
        $modules[$size - 8][8] = true;
        $isFunction[$size - 8][8] = true;

        // Reserve format/version areas
        self::reserveFormatArea($isFunction, $size);
        if ($version >= 7) {
            self::reserveVersionArea($isFunction, $size);
        }

        // Encode data
        $bits = self::encodeData($data, $version);

        // Place data bits
        self::placeDataBits($modules, $isFunction, $bits, $size);

        // Apply best mask
        $bestMask = 0;
        $bestScore = PHP_INT_MAX;
        $bestModules = $modules;

        for ($mask = 0; $mask < 8; $mask++) {
            $trial = $modules;
            self::applyMask($trial, $isFunction, $mask, $size);
            self::placeFormatBits($trial, $mask, $size);
            if ($version >= 7) {
                self::placeVersionBits($trial, $version, $size);
            }
            $score = self::penaltyScore($trial, $size);
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMask = $mask;
                $bestModules = $trial;
            }
        }

        // Convert null to false
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($bestModules[$y][$x] === null) {
                    $bestModules[$y][$x] = false;
                }
            }
        }

        return $bestModules;
    }

    private static function placeFinderPattern(array &$modules, array &$isFunction, int $row, int $col): void
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $y = $row + $dy;
                $x = $col + $dx;
                if ($y < 0 || $x < 0 || $y >= count($modules) || $x >= count($modules)) {
                    continue;
                }
                $val = (0 <= $dy && $dy <= 6 && ($dx === 0 || $dx === 6))
                    || (0 <= $dx && $dx <= 6 && ($dy === 0 || $dy === 6))
                    || (2 <= $dy && $dy <= 4 && 2 <= $dx && $dx <= 4);
                $modules[$y][$x] = $val;
                $isFunction[$y][$x] = true;
            }
        }
    }

    private static function placeAlignmentPattern(array &$modules, array &$isFunction, int $cy, int $cx): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $val = abs($dy) === 2 || abs($dx) === 2 || ($dy === 0 && $dx === 0);
                $modules[$cy + $dy][$cx + $dx] = $val;
                $isFunction[$cy + $dy][$cx + $dx] = true;
            }
        }
    }

    private static function getAlignmentPositions(int $version): array
    {
        if ($version === 1) return [];
        $positions = [6];
        $last = 17 + $version * 4 - 7;
        $numAlign = (int) floor($version / 7) + 1;
        $step = ($numAlign === 1) ? 0 : (int) ceil(($last - 6) / $numAlign);
        if ($step % 2 !== 0) $step++;
        for ($pos = $last; $pos > 6; $pos -= $step) {
            array_splice($positions, 1, 0, [$pos]);
        }
        return $positions;
    }

    private static function reserveFormatArea(array &$isFunction, int $size): void
    {
        for ($i = 0; $i < 8; $i++) {
            $isFunction[8][$i] = true;
            $isFunction[$i][8] = true;
            $isFunction[8][$size - 1 - $i] = true;
            $isFunction[$size - 1 - $i][8] = true;
        }
        $isFunction[8][8] = true;
    }

    private static function reserveVersionArea(array &$isFunction, int $size): void
    {
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $isFunction[$i][$size - 11 + $j] = true;
                $isFunction[$size - 11 + $j][$i] = true;
            }
        }
    }

    private static function encodeData(string $data, int $version): array
    {
        // ECC level L parameters per version
        $eccParams = [
            1  => [7, 1, 19],    2  => [10, 1, 34],   3  => [15, 1, 55],
            4  => [20, 1, 80],   5  => [26, 1, 108],   6  => [18, 2, 68],
            7  => [20, 2, 78],   8  => [24, 2, 97],    9  => [30, 2, 116],
            10 => [18, 2, 68+2*136/2], // simplified — use RS for real
        ];

        // Use a simpler approach: pack bits and use Reed-Solomon
        $bits = [];

        // Mode indicator: byte mode = 0100
        $bits = array_merge($bits, [0, 1, 0, 0]);

        // Character count (8 bits for version 1-9, 16 for 10+)
        $ccBits = $version <= 9 ? 8 : 16;
        $len = strlen($data);
        for ($i = $ccBits - 1; $i >= 0; $i--) {
            $bits[] = ($len >> $i) & 1;
        }

        // Data bytes
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($data[$i]);
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($byte >> $b) & 1;
            }
        }

        // Get total data codewords for this version/ECC
        $totalCodewords = self::getTotalDataCodewords($version);
        $totalBits = $totalCodewords * 8;

        // Terminator (up to 4 zeros)
        $terminatorLen = min(4, $totalBits - count($bits));
        for ($i = 0; $i < $terminatorLen; $i++) {
            $bits[] = 0;
        }

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        // Pad bytes (0xEC, 0x11 alternating)
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bits) < $totalBits) {
            $byte = $padBytes[$padIdx % 2];
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($byte >> $b) & 1;
            }
            $padIdx++;
        }

        // Convert to codewords
        $dataCodewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $val = 0;
            for ($b = 0; $b < 8; $b++) {
                $val = ($val << 1) | ($bits[$i + $b] ?? 0);
            }
            $dataCodewords[] = $val;
        }

        // Add ECC
        $eccInfo = self::getEccInfo($version);
        $allCodewords = self::addEcc($dataCodewords, $eccInfo);

        // Convert back to bits
        $result = [];
        foreach ($allCodewords as $cw) {
            for ($b = 7; $b >= 0; $b--) {
                $result[] = ($cw >> $b) & 1;
            }
        }

        return $result;
    }

    private static function getTotalDataCodewords(int $version): int
    {
        // Total data codewords for ECC level L
        $table = [
            1 => 19, 2 => 34, 3 => 55, 4 => 80, 5 => 108,
            6 => 136, 7 => 156, 8 => 194, 9 => 232, 10 => 274,
            11 => 324, 12 => 370, 13 => 428, 14 => 461, 15 => 523,
            16 => 589, 17 => 647, 18 => 721, 19 => 795, 20 => 861,
        ];
        return $table[$version] ?? 19;
    }

    private static function getEccInfo(int $version): array
    {
        // [eccPerBlock, numBlocks1, dataPerBlock1, numBlocks2, dataPerBlock2]
        $table = [
            1  => [7, 1, 19, 0, 0],
            2  => [10, 1, 34, 0, 0],
            3  => [15, 1, 55, 0, 0],
            4  => [20, 1, 80, 0, 0],
            5  => [26, 1, 108, 0, 0],
            6  => [18, 2, 68, 0, 0],
            7  => [20, 2, 78, 0, 0],
            8  => [24, 2, 97, 0, 0],
            9  => [30, 2, 116, 0, 0],
            10 => [18, 2, 68, 2, 69],
            11 => [20, 4, 81, 0, 0],
            12 => [24, 2, 92, 2, 93],
            13 => [26, 4, 107, 0, 0],
            14 => [30, 3, 115, 1, 116],
            15 => [22, 5, 87, 1, 88],
            16 => [24, 5, 98, 1, 99],
            17 => [28, 1, 107, 5, 108],
            18 => [30, 5, 120, 1, 121],
            19 => [28, 3, 113, 4, 114],
            20 => [28, 3, 107, 5, 108],
        ];
        return $table[$version] ?? $table[1];
    }

    private static function addEcc(array $dataCodewords, array $eccInfo): array
    {
        [$eccPerBlock, $numBlocks1, $dataPerBlock1, $numBlocks2, $dataPerBlock2] = $eccInfo;

        $blocks = [];
        $eccBlocks = [];
        $offset = 0;

        // Group 1
        for ($i = 0; $i < $numBlocks1; $i++) {
            $block = array_slice($dataCodewords, $offset, $dataPerBlock1);
            $blocks[] = $block;
            $eccBlocks[] = self::reedSolomon($block, $eccPerBlock);
            $offset += $dataPerBlock1;
        }

        // Group 2
        for ($i = 0; $i < $numBlocks2; $i++) {
            $block = array_slice($dataCodewords, $offset, $dataPerBlock2);
            $blocks[] = $block;
            $eccBlocks[] = self::reedSolomon($block, $eccPerBlock);
            $offset += $dataPerBlock2;
        }

        // Interleave data blocks
        $result = [];
        $maxDataLen = max($dataPerBlock1, $dataPerBlock2);
        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($blocks as $block) {
                if ($i < count($block)) {
                    $result[] = $block[$i];
                }
            }
        }

        // Interleave ECC blocks
        for ($i = 0; $i < $eccPerBlock; $i++) {
            foreach ($eccBlocks as $ecc) {
                if ($i < count($ecc)) {
                    $result[] = $ecc[$i];
                }
            }
        }

        return $result;
    }

    /**
     * Reed-Solomon error correction.
     */
    private static function reedSolomon(array $data, int $eccCount): array
    {
        $gen = self::rsGeneratorPoly($eccCount);
        $result = array_merge($data, array_fill(0, $eccCount, 0));

        for ($i = 0; $i < count($data); $i++) {
            $factor = $result[$i];
            if ($factor === 0) continue;
            for ($j = 0; $j < count($gen); $j++) {
                $result[$i + $j] ^= self::gfMul($gen[$j], $factor);
            }
        }

        return array_slice($result, count($data));
    }

    private static function rsGeneratorPoly(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $newPoly = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $newPoly[$j] ^= $poly[$j];
                $newPoly[$j + 1] ^= self::gfMul($poly[$j], self::gfPow(2, $i));
            }
            $poly = $newPoly;
        }
        return $poly;
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) return 0;
        return self::gfExp()[(self::gfLog()[$a] + self::gfLog()[$b]) % 255];
    }

    private static function gfPow(int $base, int $exp): int
    {
        $result = 1;
        for ($i = 0; $i < $exp; $i++) {
            $result = self::gfMul($result, $base);
        }
        return $result;
    }

    private static function &gfExp(): array
    {
        static $table = null;
        if ($table === null) {
            $table = array_fill(0, 512, 0);
            $val = 1;
            for ($i = 0; $i < 255; $i++) {
                $table[$i] = $val;
                $val <<= 1;
                if ($val >= 256) {
                    $val ^= 0x11D;
                }
            }
            for ($i = 255; $i < 512; $i++) {
                $table[$i] = $table[$i - 255];
            }
        }
        return $table;
    }

    private static function &gfLog(): array
    {
        static $table = null;
        if ($table === null) {
            $table = array_fill(0, 256, 0);
            $exp = self::gfExp();
            for ($i = 0; $i < 255; $i++) {
                $table[$exp[$i]] = $i;
            }
        }
        return $table;
    }

    private static function placeDataBits(array &$modules, array &$isFunction, array &$bits, int $size): void
    {
        $bitIdx = 0;
        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) $right = 5; // Skip timing column

            for ($vert = 0; $vert < $size; $vert++) {
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    $upward = (($right + 1) & 2) === 0;
                    $y = $upward ? ($size - 1 - $vert) : $vert;

                    if ($y < 0 || $y >= $size || $x < 0 || $x >= $size) continue;
                    if ($isFunction[$y][$x]) continue;

                    $modules[$y][$x] = ($bitIdx < count($bits)) ? (bool)$bits[$bitIdx] : false;
                    $bitIdx++;
                }
            }
        }
    }

    private static function applyMask(array &$modules, array &$isFunction, int $mask, int $size): void
    {
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($isFunction[$y][$x]) continue;

                $invert = match ($mask) {
                    0 => ($y + $x) % 2 === 0,
                    1 => $y % 2 === 0,
                    2 => $x % 3 === 0,
                    3 => ($y + $x) % 3 === 0,
                    4 => ((int)($y / 2) + (int)($x / 3)) % 2 === 0,
                    5 => ($y * $x) % 2 + ($y * $x) % 3 === 0,
                    6 => (($y * $x) % 2 + ($y * $x) % 3) % 2 === 0,
                    7 => (($y + $x) % 2 + ($y * $x) % 3) % 2 === 0,
                    default => false,
                };

                if ($invert) {
                    $modules[$y][$x] = !$modules[$y][$x];
                }
            }
        }
    }

    private static function placeFormatBits(array &$modules, int $mask, int $size): void
    {
        // ECC level L = 01, mask pattern
        $data = (0b01 << 3) | $mask;

        // BCH(15,5) encoding
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ ((($rem >> 9) & 1) * 0x537);
        }
        $bits = (($data << 10) | $rem) ^ 0x5412;

        // Place format bits
        $formatPositions1 = [[0, 8], [1, 8], [2, 8], [3, 8], [4, 8], [5, 8], [7, 8], [8, 8], [8, 7], [8, 5], [8, 4], [8, 3], [8, 2], [8, 1], [8, 0]];
        for ($i = 0; $i < 15; $i++) {
            [$y, $x] = $formatPositions1[$i];
            $modules[$y][$x] = (bool)(($bits >> $i) & 1);
        }

        $formatPositions2 = [
            [8, $size - 1], [8, $size - 2], [8, $size - 3], [8, $size - 4],
            [8, $size - 5], [8, $size - 6], [8, $size - 7], [8, $size - 8],
            [$size - 7, 8], [$size - 6, 8], [$size - 5, 8], [$size - 4, 8],
            [$size - 3, 8], [$size - 2, 8], [$size - 1, 8],
        ];
        for ($i = 0; $i < 15; $i++) {
            [$y, $x] = $formatPositions2[$i];
            $modules[$y][$x] = (bool)(($bits >> $i) & 1);
        }
    }

    private static function placeVersionBits(array &$modules, int $version, int $size): void
    {
        if ($version < 7) return;

        $rem = $version;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ ((($rem >> 11) & 1) * 0x1F25);
        }
        $bits = ($version << 12) | $rem;

        for ($i = 0; $i < 18; $i++) {
            $bit = (bool)(($bits >> $i) & 1);
            $a = (int)($i / 3);
            $b = $size - 11 + $i % 3;
            $modules[$a][$b] = $bit;
            $modules[$b][$a] = $bit;
        }
    }

    private static function penaltyScore(array &$modules, int $size): int
    {
        $score = 0;

        // Rule 1: Consecutive same-color modules
        for ($y = 0; $y < $size; $y++) {
            $run = 1;
            for ($x = 1; $x < $size; $x++) {
                if ($modules[$y][$x] === $modules[$y][$x - 1]) {
                    $run++;
                } else {
                    if ($run >= 5) $score += $run - 2;
                    $run = 1;
                }
            }
            if ($run >= 5) $score += $run - 2;
        }

        for ($x = 0; $x < $size; $x++) {
            $run = 1;
            for ($y = 1; $y < $size; $y++) {
                if ($modules[$y][$x] === $modules[$y - 1][$x]) {
                    $run++;
                } else {
                    if ($run >= 5) $score += $run - 2;
                    $run = 1;
                }
            }
            if ($run >= 5) $score += $run - 2;
        }

        // Rule 2: 2x2 blocks
        for ($y = 0; $y < $size - 1; $y++) {
            for ($x = 0; $x < $size - 1; $x++) {
                $c = $modules[$y][$x];
                if ($c === $modules[$y][$x + 1] && $c === $modules[$y + 1][$x] && $c === $modules[$y + 1][$x + 1]) {
                    $score += 3;
                }
            }
        }

        return $score;
    }
}
