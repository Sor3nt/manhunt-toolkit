<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Playstation;
use App\Service\NBinary;

class TxdPlaystation extends Archive {
    public $name = 'Textures (PS2/PSP)';

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['54434454']]
    ];

    private $playstation;

    public function __construct()
    {
        $this->playstation = new Playstation();
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

            'pixelFormat'             => $binary->consume(4,  NBinary::INT_32),
            'numMipLevels'             => $binary->consume(1,  NBinary::U_INT_8),
            'swizzleMask'             => $binary->consume(1,  NBinary::U_INT_8),
            'padding'             => $binary->consume(2,  NBinary::HEX),


            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32),


        ];

        $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

        $texture['palette'] = false;

        if ($texture['paletteOffset'] > 0){
            $binary->jumpTo($texture['paletteOffset']);

            $texture['palette'] = $binary->consume($this->playstation->getPaletteSize($texture['rasterFormat'], $texture['bitPerPixel']), NBinary::BINARY);
        }

        $binary->jumpTo($texture['dataOffset']);

        if ($texture['width'] == 1) {
            $texture['data'] = false;
            return $texture;
        }

        $texture['data'] = $binary->consume(
            $this->playstation->getRasterSize($texture['rasterFormat'], $texture['width'], $texture['height'], $texture['bitPerPixel']),
            NBinary::BINARY
        );


        return $texture;
    }

    public function unpack(NBinary $binary, $game, $platform){

        if ($game == MHT::GAME_AUTO) $game = MHT::GAME_MANHUNT_2;

        $header = $this->parseHeader($binary);
        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);

            if ($texture['data'] !== false){
                $bmpRgba = $this->playstation->convertToRgba($texture, $platform);
                $image = $this->playstation->rgbaToImage($bmpRgba, $texture['width'],$texture['height']);

                $textures[$texture['name'] . '.png'] = $image;
            }

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }

        return $textures;
    }

    public function pack( $data, $game, $platform){

        die("Packing it not supported.");

    }
}