<?php
namespace App\Service\Archive;

use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;
use App\Service\NBinary;

/**
 * Class Dxt
 * @package App\Service\Archive
 *
 * Based on the great work from Kevin Chapelier
 * https://github.com/kchapelier/decode-dxt
 */
class Dxt1
{

    public function decode($data, $width, $height)
    {

        $rgba = [];

        $height4 = floor($height / 4);
        $width4 = floor($width / 4);

        $binary = new NBinary($data);

        if ($height4 == 0){
            $h = 0;
            for ($w = 0; $w < $width4; $w++) {

                $firstVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);
                $secondVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);

                $colorValues = $this->interpolateColorValues($firstVal, $secondVal, true);

                $colorIndices = $binary->consume(4, NBinary::LITTLE_U_INT_32);

                for ($y = 0; $y < 4; $y++) {
                    for ($x = 0; $x < 4; $x++) {
                        $pixelIndex = (3 - $x) + ($y * 4);
                        $rgbaIndex = ($h * 4 + 3 - $y) * $width * 4 + ($w * 4 + $x) * 4;
                        $colorIndex = ($colorIndices >> (2 * (15 - $pixelIndex))) & 0x03;

                        $rgba[$rgbaIndex] = $colorValues[$colorIndex * 4];
                        $rgba[$rgbaIndex + 1] = $colorValues[$colorIndex * 4 + 1];
                        $rgba[$rgbaIndex + 2] = $colorValues[$colorIndex * 4 + 2];
                        $rgba[$rgbaIndex + 3] = $colorValues[$colorIndex * 4 + 3];

                    }
                }

            }
        }

        for ($h = 0; $h < $height4; $h++) {
            for ($w = 0; $w < $width4; $w++) {

                $firstVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);
                $secondVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);

                $colorValues = $this->interpolateColorValues($firstVal, $secondVal, true);

                $colorIndices = $binary->consume(4, NBinary::LITTLE_U_INT_32);

                for ($y = 0; $y < 4; $y++) {
                    for ($x = 0; $x < 4; $x++) {
                        $pixelIndex = (3 - $x) + ($y * 4);
                        $rgbaIndex = ($h * 4 + 3 - $y) * $width * 4 + ($w * 4 + $x) * 4;
                        $colorIndex = ($colorIndices >> (2 * (15 - $pixelIndex))) & 0x03;

                        $rgba[$rgbaIndex] = $colorValues[$colorIndex * 4];
                        $rgba[$rgbaIndex + 1] = $colorValues[$colorIndex * 4 + 1];
                        $rgba[$rgbaIndex + 2] = $colorValues[$colorIndex * 4 + 2];
                        $rgba[$rgbaIndex + 3] = $colorValues[$colorIndex * 4 + 3];
                    }
                }
            }
        }

        return $rgba;
    }

    public function decodeWii($data, $width, $height)
    {

        $rgba = [];

        $height4 = floor($height / 4);
        $width4 = floor($width / 4);

        $binary = new NBinary($data);

        for ($h = 0; $h < $height4; $h++) {
            for ($w = 0; $w < $width4; $w++) {

                $firstVal = $binary->consume(2, NBinary::BIG_U_INT_16);
                $secondVal = $binary->consume(2, NBinary::BIG_U_INT_16);

                $colorValues = $this->interpolateColorValues($firstVal, $secondVal, true);

                $colorIndices = $binary->consume(4, NBinary::LITTLE_U_INT_32);

                for ($y = 0; $y < 4; $y++) {
                    for ($x = 0; $x < 4; $x++) {
                        $pixelIndex = (3 - $x) + ($y * 4);
                        $rgbaIndex = ($h * 4 + 3 - $y) * $width * 4 + ($w * 4 + $x) * 4;
                        $colorIndex = ($colorIndices >> (2 * (15 - $pixelIndex))) & 0x03;

                        $rgba[$rgbaIndex] = $colorValues[$colorIndex * 4];
                        $rgba[$rgbaIndex + 1] = $colorValues[$colorIndex * 4 + 1];
                        $rgba[$rgbaIndex + 2] = $colorValues[$colorIndex * 4 + 2];
                        $rgba[$rgbaIndex + 3] = $colorValues[$colorIndex * 4 + 3];
                    }
                }
            }
        }

        /**
         * Flip 4x4 blocks
         */
        $rgbaNew = [];
        $rgbaBlocks = [];

        ksort($rgba);

        $pixels = array_chunk($rgba, 4);
        $current = 0;
        while ($current < count($pixels)){

            $rgbaBlocks[] = $pixels[$current + 3];
            $rgbaBlocks[] = $pixels[$current + 2];
            $rgbaBlocks[] = $pixels[$current + 1];

            $rgbaBlocks[] = $pixels[$current];

            $current += 4;
        }

        foreach ($rgbaBlocks as $rgbaBlock) {

            foreach ($rgbaBlock as $item) {
                $rgbaNew[] = $item;

            }
        }

        return $rgbaNew;
    }


    private function unsignedRightShift($a, $b)
    {
        if ($b >= 32 || $b < -32) {
            $m = (int)($b / 32);
            $b = $b - ($m * 32);
        }

        if ($b < 0) {
            $b = 32 + $b;
        }

        if ($b == 0) {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        if ($a < 0) {
            $a = ($a >> 1);
            $a &= 0x7fffffff;
            $a |= 0x40000000;
            $a = ($a >> ($b - 1));
        } else {
            $a = ($a >> $b);
        }

        return $a;
    }


    private function convert565ByteToRgb($byte)
    {
        return [
            round(($this->unsignedRightShift($byte, 11) & 31) * (255 / 31)),
            round(($this->unsignedRightShift($byte, 5) & 63) * (255 / 63)),
            round(($byte & 31) * (255 / 31))
        ];
    }


    private function lerp($v1, $v2, $r)
    {
        return $v1 * (1 - $r) + $v2 * $r;
    }

    private function interpolateColorValues($firstVal, $secondVal, $isDxt1)
    {
        $firstColor = $this->convert565ByteToRgb($firstVal);

        $secondColor = $this->convert565ByteToRgb($secondVal);

        $colorValues = $firstColor;
        $colorValues[] = 255;
        foreach ($secondColor as $color) {
            $colorValues[] = $color;
        }
        $colorValues[] = 255;

        if ($isDxt1 && $firstVal <= $secondVal) {
            $colorValues[] = round(($firstColor[0] + $secondColor[0]) / 2);
            $colorValues[] = round(($firstColor[1] + $secondColor[1]) / 2);
            $colorValues[] = round(($firstColor[2] + $secondColor[2]) / 2);
            $colorValues[] = 255;

            $colorValues[] = 0;
            $colorValues[] = 0;
            $colorValues[] = 0;
            $colorValues[] = 0;
        } else {
            $colorValues[] = round($this->lerp($firstColor[0], $secondColor[0], 1 / 3));
            $colorValues[] = round($this->lerp($firstColor[1], $secondColor[1], 1 / 3));
            $colorValues[] = round($this->lerp($firstColor[2], $secondColor[2], 1 / 3));
            $colorValues[] = 255;

            $colorValues[] = round($this->lerp($firstColor[0], $secondColor[0], 2 / 3));
            $colorValues[] = round($this->lerp($firstColor[1], $secondColor[1], 2 / 3));
            $colorValues[] = round($this->lerp($firstColor[2], $secondColor[2], 2 / 3));
            $colorValues[] = 255;

        }

        return $colorValues;
    }


}