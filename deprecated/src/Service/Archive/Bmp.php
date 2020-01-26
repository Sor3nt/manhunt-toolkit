<?php
namespace App\Service\Archive;

use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;
use App\Service\NBinary;

class Bmp
{

    public function encode($data, $width, $height)
    {

        $extraBytes = $width % 4;
        $rgbSize = $height * (3 * $width + $extraBytes);
        $headerInfoSize = 40;

        $flag = "BM";
        $reserved = 0;
        $offset = 54;
        $fileSize = $rgbSize + $offset;
        $planes = 1;
        $bitPP = 24;
        $compress = 0;
        $hr = 0;
        $vr = 0;
        $colors = 0;
        $importantColors = 0;


        $tempBuffer = new NBinary();
        $tempBuffer->write($flag, NBinary::BINARY);
        $tempBuffer->write($fileSize, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($reserved, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($offset, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($headerInfoSize, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($width, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write(-$height, NBinary::LITTLE_U_INT_32);

        $tempBuffer->write($planes, NBinary::LITTLE_U_INT_16);
        $tempBuffer->write($bitPP, NBinary::LITTLE_U_INT_16);

        $tempBuffer->write($compress, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($rgbSize, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($hr, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($vr, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($colors, NBinary::LITTLE_U_INT_32);
        $tempBuffer->write($importantColors, NBinary::LITTLE_U_INT_32);

//        var_dump($tempBuffer->hex);
//exit;
//
        $i = 0;
        $rowBytes = 3 * $width + $extraBytes;

        $pos = $tempBuffer->length();

        $asArray = $tempBuffer->getAsArray();

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $p = $pos + $y * $rowBytes + $x * 3;
                $i++;// skip Alpha

                //write B
                $asArray[$p] = str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                //            $tempBuffer->hex .= str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                $i++;

                //write G
                $asArray[$p + 1] = str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                //            $tempBuffer->hex .= str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                $i++;

                //write R
                $asArray[$p + 2] = str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                //            $tempBuffer->hex .= str_pad(dechex($data[$i]), 2, '0', STR_PAD_LEFT);
                $i++;
                //


            }

            if ($extraBytes > 0) {
                $fillOffset = $pos + $y * $rowBytes + $width * 3;
                var_dump($fillOffset, "TODO");
                exit;
//            tempBuffer.fill(0,fillOffset,fillOffset+this.extraBytes);
            }
        }

        return hex2bin(implode("", $asArray));
    }


}