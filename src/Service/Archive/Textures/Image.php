<?php
namespace App\Service\Archive\Textures;

use App\Service\NBinary;

class Image {

    public function saveImage($data, $width, $height){

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);

        $x = 0;
        $y = 0;
        foreach ($data as $rgba) {
//            $color =  imagecolorallocatealpha($img,$rgba[0],$rgba[1],$rgba[2],$rgba[3]);
            $color =  imagecolorallocate($img,$rgba[0],$rgba[1],$rgba[2]);
            imagesetpixel($img,$x,$y,$color);

            $x++;
            if ($x >= $width){
                $x = 0;
                $y++;
            }
        }

        ob_start();
        imagejpeg($img, null, 100);
        return ob_get_clean();
    }

    /**
     * Nintendo Wii Formats
     */

    public function readFormatI4( $rawPixel){
        return [
            $rawPixel * 0x11,
            $rawPixel * 0x11,
            $rawPixel * 0x11,
            0xFF
        ];
    }

    public function readFormatI8( $rawPixel){
        return [
            $rawPixel,
            $rawPixel,
            $rawPixel,
            0xFF
        ];
    }

    public function readFormatIa4( $rawPixel){
        return [
            ($rawPixel & 0xF) * 0x11,
            ($rawPixel & 0xF) * 0x11,
            ($rawPixel & 0xF) * 0x11,
            ($rawPixel >> 4) * 0x11
        ];
    }

    public function readFormatIa8( $rawPixel){
        return [
            $rawPixel & 0xFF,
            $rawPixel & 0xFF,
            $rawPixel & 0xFF,
            ($rawPixel >> 8) & 0xFF
        ];

        //        return [
//            $rawPixel & 0xFF,
//            $rawPixel & 0xFF,
//            $rawPixel & 0xFF,
//            $rawPixel >> 8
//        ];
    }

    public function readFormatRgba32( $rawPixel){
        return [
            ($rawPixel >> 24) & 0xFF,
            ($rawPixel >> 16) & 0xFF,
            ($rawPixel >> 8)  & 0xFF,
            ($rawPixel >> 0)  & 0xFF
        ];
    }

    public function readFormatRgb5a3( $rawPixel){

        if ($rawPixel & 0x8000 != 0){ // r5g5b5
            return [
                ((($rawPixel >> 10) & 0x1F) * 0xFF / 0x1F),
                ((($rawPixel >> 5)  & 0x1F) * 0xFF / 0x1F),
                ((($rawPixel >> 0)  & 0x1F) * 0xFF / 0x1F),
                0xFF
            ];

        }

        //r4g4b4a3
        return [
            ((($rawPixel >> 8)  & 0x0F) * 0xFF / 0x0F),
            ((($rawPixel >> 4)  & 0x0F) * 0xFF / 0x0F),
            ((($rawPixel >> 0)  & 0x0F) * 0xFF / 0x0F),
            ((($rawPixel >> 12) & 0x07) * 0xF / 0x07)

        ];
    }


    public function readFormatRgb565( $rawPixel){
        return [
            ((($rawPixel >> 11) & 0x1F) * 0xFF / 0x1F),
            ((($rawPixel >> 5)  & 0x3F) * 0xFF / 0x3F),
            ((($rawPixel >> 0)  & 0x1F) * 0xFF / 0x1F),
            0xFF
        ];
    }

}