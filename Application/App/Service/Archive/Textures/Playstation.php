<?php
namespace App\Service\Archive\Textures;

use App\MHT;
use App\Service\NBinary;

class Playstation extends Image {


    private $alphaEncodingTable = [
        0,   1,   1,   2,   2,   3,   3,   4,   4,   5,   5,   6,   6,   7,   7,   8,
        8,   9,   9,   10,  10,  11,  11,  12,  12,  13,  13,  14,  14,  15,  15,  16,
        16,  17,  17,  18,  18,  19,  19,  20,  20,  21,  21,  22,  22,  23,  23,  24,
        24,  25,  25,  26,  26,  27,  27,  28,  28,  29,  29,  30,  30,  31,  31,  32,
        32,  33,  33,  34,  34,  35,  35,  36,  36,  37,  37,  38,  38,  39,  39,  40,
        40,  41,  41,  42,  42,  43,  43,  44,  44,  45,  45,  46,  46,  47,  47,  48,
        48,  49,  49,  50,  50,  51,  51,  52,  52,	 53,  53,  54,  54,  55,  55,  56,
        56,  57,  57,  58,  58,  59,  59,  60,  60,  61,  61,  62,  62,  63,  63,  64,
        64,  65,  65,  66,  66,  67,  67,  68,  68,  69,  69,  70,  70,  71,  71,  72,
        72,  73,  73,  74,  74,  75,  75,  76,  76,  77,  77,  78,  78,  79,  79,  80,
        80,  81,  81,  82,  82,  83,  83,  84,  84,  85,  85,  86,  86,  87,  87,  88,
        88,  89,  89,  90,  90,  91,  91,  92,  92,  93,  93,  94,  94,  95,  95,  96,
        96,  97,  97,  98,  98,  99,  99,  100, 100, 101, 101, 102, 102, 103, 103, 104,
        104, 105, 105, 106, 106, 107, 107, 108, 108, 109, 109, 110, 110, 111, 111, 112,
        112, 113, 113, 114, 114, 115, 115, 116, 116, 117, 117, 118, 118, 119, 119, 120,
        120, 121, 121, 122, 122, 123, 123, 124, 124, 125, 125, 126, 126, 127, 127, 128
    ];

    private $alphaDecodingTable = [

        0,   2,   4,   6,   8,   10,  12,  14,  16,  18,  20,  22,  24,  26,  28,  30,
        32,  34,  36,  38,  40,  42,  44,  46,  48,  50,  52,  54,  56,  58,  60,  62,
        64,  66,  68,  70,  72,  74,  76,  78,  80,  82,  84,  86,  88,  90,  92,  94,
        96,  98,  100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126,
        128, 129, 131, 133, 135, 137, 139, 141, 143, 145, 147, 149, 151, 153, 155, 157,
        159, 161, 163, 165, 167, 169, 171, 173, 175, 177, 179, 181, 183, 185, 187, 189,
        191, 193, 195, 197, 199, 201, 203, 205, 207, 209, 211, 213, 215, 217, 219, 221,
        223, 225, 227, 229, 231, 233, 235, 237, 239, 241, 243, 245, 247, 249, 251, 253,
        255

    ];



    private function unswizzlePsp($texture, $bmpRgba, $as4Bit = false ){

        if ($texture['width'] <= 16) return $bmpRgba;

        $BlockWidth = $as4Bit ? 32 : 16;
        $BlockHeight = 8;

        if ($texture['width'] == 16){
            $BlockWidth = 16;
        }

        $BlockSize = $BlockHeight * $BlockWidth;

        $start = 0;
        $end = count($bmpRgba);

        $unswizzled = [];
        foreach ($bmpRgba as $item) {
            $unswizzled[] = [0,0,0,0];
        }
        $swizzled = $bmpRgba;

        $size = $end - $start;
        $blockCount = $size / $BlockSize;
        $blocksPerRow = $texture['width'] / $BlockWidth;


        for ($block = 0; $block < $blockCount; ++$block)
        {
            $by = (int) ($block / $blocksPerRow) * $BlockHeight;
            $bx = (int) ($block % $blocksPerRow) * $BlockWidth;

            for ($y = 0; $y < $BlockHeight; $y++)
            {

                for ($x = 0; $x < $BlockWidth; $x++)
                {
                    $unswizzled[$start + ($by + $y) * $texture['width'] + $bx + $x] =
                        $swizzled[$start + $block * $BlockSize + $y * $BlockWidth + $x];
                }
            }
        }

        return $unswizzled;
    }

    private function unswizzlePs2($texture, $bmpRgba ){

//        if ($texture['height'] <= 32) return $bmpRgba;

        $result = [];

        for ($y = 0; $y < $texture['height']; $y++){

            for ($x = 0; $x < $texture['width']; $x++) {
                $block_loc = ($y&(~0x0F))*$texture['width'] + ($x&(~0x0F))*2;
                $swap_sel = ((($y+2)>>2)&0x01)*4;
                $ypos = ((($y&(~3))>>1) + ($y&1))&0x07;
                $column_loc = $ypos*$texture['width']*2 + (($x+$swap_sel)&0x07)*4;
                $byte_sum = (($y>>1)&1) + (($x>>2)&2);
                $swizzled = $block_loc + $column_loc + $byte_sum;

                $result[$y*$texture['width']+$x] = $bmpRgba[$swizzled];
            }

        }

        return $result;
    }

    public function getPaletteSize( $format, $bpp ){


        if ($format == "00010000" && $bpp == 8) return 1024;
        if ($format == "10000000" && $bpp == 8) return 1024;
        if ($format == "20000000" && $bpp == 8) return 1024;
        if ($format == "40000000" && $bpp == 8) return 1024;
        if ($format == "80000000" && $bpp == 32) return 1024;
        if ($format == "80000000" && $bpp == 8) return 1024;
        if ($format == "80000000" && $bpp == 4) return 1024;
        if ($format == "00010000" && $bpp == 32) return 1024;


        if ($format == "08000000" && $bpp == 4) return 64;
        if ($format == "10000000" && $bpp == 4) return 64;
        if ($format == "20000000" && $bpp == 4) return 64;
        if ($format == "40000000" && $bpp == 4) return 64;
        if ($format == "00010000" && $bpp == 4) return 64;
        if ($format == "00020000" && $bpp == 8) return 1024;

        throw new \Exception(sprintf("Unknown palette format %s bpp: %s", $format, $bpp));


    }

    public function getRasterSize( $format, $width, $height, $bpp ){

        if ($format == "80000000" && $bpp == 32) return $width * $height;
        if ($format == "80000000" && $bpp == 8) return $width * $height;
        if ($format == "08000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "10000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "80000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "00010000" && $bpp == 8) return $width * $height;
        if ($format == "20000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "00010000" && $bpp == 4) return $width * $height;
        if ($format == "40000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "40000000" && $bpp == 8) return $width * $height;
        if ($format == "20000000" && $bpp == 8) return $width * $height;
        if ($format == "10000000" && $bpp == 8) return $width * $height;
        if ($format == "00010000" && $bpp == 32) return $width * $height;
        if ($format == "00020000" && $bpp == 8) return $width * $height;

        throw new \Exception(sprintf("Unknown raster format %s bpp: %s", $format, $bpp));
    }


    public function convertToRgba($texture, $platform ){

        $palette = false;
        if ($texture['palette']){
            $palette = $this->decode32ColorsToRGBA( new NBinary($texture['palette']));
        }

        $is4Bit = $texture['bitPerPixel'] == 4;

        if ($texture['bitPerPixel'] == 4) {

            if ($palette){
                $bmpRgba = $this->convertIndexed4ToRGBA(
                    $texture['data'],
                    ($texture['width'] * $texture['height']),
                    $palette
                );

            }else{
                die("todo 4bit no palette");
            }

        }else if ($texture['bitPerPixel'] == 8){

            if ($palette) {
                if ($platform == MHT::PLATFORM_PS2) {
                    $palette = $this->paletteUnswizzle($palette);
                }

                $bmpRgba = $this->convertIndexed8ToRGBA(
                    $texture['data'],
                    $palette
                );
            }else{
                die("todo 8bit no palette");

            }

        }else if ($texture['bitPerPixel'] == 32){

            $bmpRgba = $this->decode32ColorsToRGBA( new NBinary($texture['data']));


        }else{
            throw new \Exception(sprintf("Unknown bitPerPixel format %s", $texture['bitPerPixel']));
        }

        if ($platform == MHT::PLATFORM_PS2 && $texture['swizzleMask'] & 0x1 != 0) {
            $bmpRgba = $this->unswizzlePs2($texture, $bmpRgba);
        }else if ($platform == MHT::PLATFORM_PSP || $platform == MHT::PLATFORM_PSP_001){
            $bmpRgba = $this->unswizzlePsp($texture, $bmpRgba, $is4Bit);
        }

        //flat the rgba array
        $bmpRgba_ = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($bmpRgba));
        $bmpRgba = [];
        foreach ($bmpRgba_ as $item) {
            $bmpRgba[] = $item;
        }

        return $bmpRgba;

    }

    private function paletteUnswizzle($palette){

        //Ruleset:
        /*
         * 1. first 8 colors stay
         *
         * 2. next 8 colors are twisted with the followed 8 colors
         * 3. 16 colors stay
         *
         * 4. goto step 2
         */

        $newPalette = [];

        $palChunks = array_chunk($palette, 8);

        $current = 0;
        $swapCount = 2;

        while($current < count($palChunks)){

            $chunk = $palChunks[$current];

            if ($current == 0){
                $newPalette[] = $chunk;
                $current++;
                $swapCount = 2;
                continue;
            }


            if ($swapCount == 2){
                $newPalette[] = $palChunks[$current + 1];;
                $newPalette[] = $palChunks[$current];
                $current++;
                $swapCount = 0;
            }else{
                $newPalette[] = $chunk;
                $swapCount++;
            }

            $current++;
        }

        $finalPalette = [];
        foreach ($newPalette as $chunk) {
            foreach ($chunk as $rgba) {
                $finalPalette[] = $rgba;
            }
        }

        return $finalPalette;

    }


    private function convertIndexed8ToRGBA( $indexed4Data, $palette ){

        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $binary->length(); $i++) {
            $src = $binary->consume(1, NBinary::U_INT_8);

            $result[] = $palette[$src];
        }

        return $result;

    }


    private function convertIndexed4ToRGBA( $indexed4Data, $count, $palette ){
        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $count; $i = $i + 2) {
            $val = $binary->consume(1, NBinary::U_INT_8);

            $result[] = $palette[$val & 0x0F];
            $result[] = $palette[$val >> 4];
        }

        return $result;

    }


    private function decode32ColorsToRGBA( NBinary $colors){

        $result = [];

        while ($colors->remain()) {
            $dst = [];

            $dst[] = $colors->consume(1, NBinary::U_INT_8); //r
            $dst[] = $colors->consume(1, NBinary::U_INT_8); //g
            $dst[] = $colors->consume(1, NBinary::U_INT_8); //b
            $alpha = $colors->consume(1, NBinary::U_INT_8); //a
//
            $dst[] = $alpha > 0x80 ? 255 : $this->alphaDecodingTable[$alpha];

            $result[] = $dst;
        }

        return $result;
    }




}