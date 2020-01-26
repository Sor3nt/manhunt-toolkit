<?php
namespace App\Service\Archive;

use App\Service\NBinary;

/**
 * Class Dxt5
 * @package App\Service\Archive
 *
 * Based on the great work from Kevin Chapelier
 * https://github.com/kchapelier/decode-dxt
 */
class Dxt5
{

    public function decode($data, $width, $height, $returnAs = "rgba")
    {

        // for dxt4 set this to true
        $premultiplied = false;

        $rgba = [];

        $height4 = floor($height / 4);
        $width4 = floor($width / 4);

        $binary = new NBinary($data);


        for ($h = 0; $h < $height4; $h++) {
            for ($w = 0; $w < $width4; $w++) {

                $firstVal = $binary->consume(1, NBinary::U_INT_8);
                $secondVal = $binary->consume(1, NBinary::U_INT_8);


                $alphaValues = $this->interpolateAlphaValues($firstVal, $secondVal);


                $alphaIndices = array_reverse([
                   $binary->consume(2, NBinary::LITTLE_U_INT_16),
                   $binary->consume(2, NBinary::LITTLE_U_INT_16),
                   $binary->consume(2, NBinary::LITTLE_U_INT_16),
                ]); // reordered as big endian



                $firstVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);
                $secondVal = $binary->consume(2, NBinary::LITTLE_U_INT_16);

                $colorValues = $this->interpolateColorValues($firstVal, $secondVal, true);

                $colorIndices = $binary->consume(4, NBinary::LITTLE_U_INT_32);

                for ($y = 0; $y < 4; $y++) {
                    for ($x = 0; $x < 4; $x++) {
                        $pixelIndex = (3 - $x) + ($y * 4);
                        $rgbaIndex = ($h * 4 + 3 - $y) * $width * 4 + ($w * 4 + $x) * 4;
                        $colorIndex = ($colorIndices >> (2 * (15 - $pixelIndex))) & 0x03;

                        $alphaIndex = $this->getAlphaIndex($alphaIndices, $pixelIndex);
                        $alphaValue = $alphaValues[$alphaIndex];

                        $multiplier = $premultiplied ? 255 / $alphaValue : 1;


                        if ($returnAs == "rgba"){

                            $rgba[$rgbaIndex] = $this->multiply($colorValues[$colorIndex * 4], $multiplier);
                            $rgba[$rgbaIndex + 1] = $this->multiply($colorValues[$colorIndex * 4 + 1], $multiplier);
                            $rgba[$rgbaIndex + 2] = $this->multiply($colorValues[$colorIndex * 4 + 2], $multiplier);
                            $rgba[$rgbaIndex + 3] = $alphaValue;



                        }else if ($returnAs == "abgr"){

                            $rgba[$rgbaIndex] = $alphaValue;
                            $rgba[$rgbaIndex + 1] = $this->multiply($colorValues[$colorIndex * 4 + 2 ], $multiplier);
                            $rgba[$rgbaIndex + 2] = $this->multiply($colorValues[$colorIndex * 4 + 1], $multiplier);
                            $rgba[$rgbaIndex + 3] = $this->multiply($colorValues[$colorIndex * 4], $multiplier);


                        }else{
                            throw new \Exception('Unknown RGBa Order');
                        }
                    }
                }
            }
        }

        return $rgba;
    }

    private function multiply ($component, $multiplier) {
        if (is_infinite($multiplier) || $multiplier === 0) {
            return 0;
        }

        return round($component * $multiplier);
    }

    private function getAlphaIndex($alphaIndices, $pixelIndex) {
        return $this->extractBitsFromUin16Array($alphaIndices, (3 * (15 - $pixelIndex)), 3);
    }


    private function extractBitsFromUin16Array($array, $shift, $length) {
        // sadly while javascript operates with doubles, it does all its binary operations on 32 bytes integers
        // so we have to get a bit dirty to do the bitshifting on the 48 bytes integer for the alpha values of DXT5

        $height = count($array);
            $heightm1 = $height - 1;
            $width = 16;
            $rowS = (($shift / $width) | 0);
            $rowE = ((($shift + $length - 1) / $width) | 0);

        if ($rowS === $rowE) {
            // all the requested bits are contained in a single uint16
            $shiftS = ($shift % $width);
            $result = ($array[$heightm1 - $rowS] >> $shiftS) & (pow(2, $length) - 1);
        } else {
            // the requested bits are contained in two continuous uint16
            $shiftS = ($shift % $width);
            $shiftE = ($width - $shiftS);
            $result = ($array[$heightm1 - $rowS] >> $shiftS) & (pow(2, $length) - 1);
            $result += ($array[$heightm1 - $rowE] & (pow(2, $length - $shiftE) - 1)) << $shiftE;
        }

        return $result;
    }


    private function interpolateAlphaValues ($firstVal, $secondVal) {
        $alphaValues = [$firstVal, $secondVal];

        if ($firstVal > $secondVal) {
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 1 / 7));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 2 / 7));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 3 / 7));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 4 / 7));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 5 / 7));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 6 / 7));

        } else {

            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 1 / 5));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 2 / 5));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 3 / 5));
            $alphaValues[] = floor($this->lerp($firstVal, $secondVal, 4 / 5));
            $alphaValues[] = 0;
            $alphaValues[] = 255;
        }

        return $alphaValues;
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
            $colorValues[] = $colorValues[] = round(($firstColor[1] + $secondColor[1]) / 2);
            round(($firstColor[2] + $secondColor[2]) / 2);
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