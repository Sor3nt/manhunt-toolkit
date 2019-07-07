<?php
namespace App\Service\Archive;

use App\Service\Archive\Textures\Ps2;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Txd extends Archive {
    public $name = 'Textures (PS2)';

    public static $supported = 'txd';

    private $ps2;

    public function __construct()
    {
        $this->ps2 = new Ps2();
    }


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){
        return false;
    }


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


    private function parseHeader( NBinary &$binary ){

        return [
            'magic'             => $binary->consume(4,  NBinary::STRING),
            'constNumber'       => $binary->consume(4,  NBinary::INT_32),
            'fileSize'          => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset'  => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset2' => $binary->consume(4,  NBinary::INT_32),
            'numIndex'          => $binary->consume(4,  NBinary::INT_32),
            'unknown'           => $binary->consume(8,  NBinary::HEX),
            'numTextures'       => $binary->consume(4,  NBinary::INT_32),
            'firstOffset'       => $binary->consume(4,  NBinary::INT_32),
            'lastTOffset'       => $binary->consume(4,  NBinary::INT_32)
        ];

    }

    private function parseTexture( $startOffset, NBinary $binary ){

        $binary->jumpTo($startOffset);

        $texture = [
            'nextOffset'        => $binary->consume(4,  NBinary::INT_32),
            'prevOffset'        => $binary->consume(4,  NBinary::INT_32),
            'name'              => $binary->consume(64, NBinary::BINARY),

            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'rasterFormat'      => $binary->consume(4,  NBinary::HEX),

            'unknown11'             => $binary->consume(4,  NBinary::INT_32),
            'unknown12'             => $binary->consume(4,  NBinary::INT_32),

            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32)
        ];

        $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

        $binary->jumpTo($texture['paletteOffset']);

        $texture['palette'] = $binary->consume($this->ps2->getPaletteSize($texture['rasterFormat'], $texture['bitPerPixel']), NBinary::BINARY);

        $binary->jumpTo($texture['dataOffset']);
        $texture['data'] = $binary->consume(
            $this->ps2->getRasterSize($texture['rasterFormat'], $texture['width'], $texture['height'], $texture['bitPerPixel']),
            NBinary::BINARY
        );


        return $texture;
    }


    public function convertIndexed8ToRGBA( $indexed4Data, $count, $palette ){
        /**
         *
         *
        const uint32_t* pal = static_cast<const uint32_t*>(palette);

        const uint8_t* src = static_cast<const uint8_t*>(indexed8Data);
        uint32_t* dst = static_cast<uint32_t*>(dest);
        for (int i = 0; i < count; i++)
        {
         *dst++ = pal[*src++];
        }
         */
        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $count; $i++) {
            $src = $binary->consume(1, NBinary::INT_8);

            $result[] = $palette[$src];
        }

        return $result;

    }


    public function convertIndexed4ToRGBA( $indexed4Data, $count, $palette ){

//        $result = [];
//
//        $binary = new NBinary( $indexed4Data );
//
//        for ($i = 0; $i < $count; $i = $i + 2) {
//            $val = $binary->consume(1, NBinary::U_INT_8);
//
//            $result[] = $val;
//        }
//
//        return $result;
        $result = [];

        $binary = new NBinary( $indexed4Data );

        for ($i = 0; $i < $count; $i = $i + 2) {
            $val = $binary->consume(1, NBinary::U_INT_8);

            $result[] = $palette[$val & 0x0F];
            $result[] = $palette[$val >> 4];
        }

        return $result;

    }

    public function decode32ColorsToRGBA( NBinary $colors){

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

    public function unpack(NBinary $binary, $game, $platform){

        $header = $this->parseHeader($binary);
        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);

            $bmpRgba = $this->ps2->convertToRgba($texture);

            $image = $this->ps2->saveImage($bmpRgba, $texture['width'],$texture['height']);

            $textures[$texture['name'] . '.png'] = $image;

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }

        return $textures;
    }

    public function pack( $data, $game, $platform){

        die("Packing it not supported right now.");

    }
}