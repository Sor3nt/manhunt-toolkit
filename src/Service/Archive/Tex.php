<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;

class Tex extends Archive {

    public $name = 'Textures';

    public static $supported = 'tex';

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

    private function parseTexture( $startOffset, NBinary &$binary ){

        $binary->jumpTo($startOffset);

        $texture = [
            'nextOffset'        => $binary->consume(4,  NBinary::INT_32),
            'prevOffset'        => $binary->consume(4,  NBinary::INT_32),
            'name'              => $binary->consume(32, NBinary::STRING),
            'alphaFlags'        => $binary->consume(32, NBinary::HEX),
            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'pitchOrLinearSize' => $binary->consume(4,  NBinary::INT_32),
            'flags'             => $binary->consume(4,  NBinary::HEX),
            'mipMapCount'       => $binary->consume(1,  NBinary::INT_8),
            'unknown'           => $binary->consume(3,  NBinary::HEX),
            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32),
            'size'              => $binary->consume(4,  NBinary::INT_32),
            'unknown2'          => $binary->consume(20, NBinary::BINARY)
        ];

        $binary->jumpTo($texture['dataOffset']);

        $texture['data'] = $binary->consume($texture['size'], NBinary::BINARY);

        return $texture;
    }


    public function convertToBmp( $texture ){
        $ddsHandler = new Dds();
        $bmpHandler = new Bmp();

        $ddsDecoded = $ddsHandler->unpack( new NBinary($texture['data']), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

        if($ddsDecoded['format'] == "DXT1") {
            $dxtHandler = new Dxt1();
        }else if($ddsDecoded['format'] == "DXT5"){
            $dxtHandler = new Dxt5();
        }else{
            throw new \Exception('Format not implemented: ' . $ddsDecoded['format']);
        }

        //decode the DXT Texture
        $bmpRgba = $dxtHandler->decode(
            $ddsDecoded['data'],
            $ddsDecoded['width'],
            $ddsDecoded['height'],
            'abgr'
        );

        //Convert the RGBa values into a Bitmap
        $bmpImage = $bmpHandler->encode(
            $bmpRgba,
            $ddsDecoded['width'],
            $ddsDecoded['height']
        );

        return [ $texture['name'] . ".bmp" , $bmpImage];
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    public function unpack(NBinary $binary, $game, $platform){

        $header = $this->parseHeader($binary);

        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);

            if ($texture['mipMapCount'] > 1){
                throw new \Exception('MipMap handler missed');
            }

            list($filename, $bmp) = $this->convertToBmp($texture);
            $textures[$filename] = $bmp;

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }


        return $textures;
    }

    /**
     * @param $data
     * @param $game
     * @param $platform
     */
    public function pack($data, $game, $platform ){

        die("Packing it not supported right now.");

    }
}