<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Image;
use App\Service\NBinary;

/**
 * Class Tex
 * Todos:
 * - Add MipMap Support (do we need this ? maybe for packing ?)
 * - Add Alpha handling
 */
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
        $ddsDecoded = $ddsHandler->unpack( new NBinary($texture['data']), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

        if($ddsDecoded['format'] == "DXT1") {
            $dxtHandler = new Dxt1();


            //decode the DXT Texture
            $rgba = $dxtHandler->decode(
                $ddsDecoded['data'],
                $ddsDecoded['width'],
                $ddsDecoded['height']
            );


        }else if($ddsDecoded['format'] == "DXT5"){
            $dxtHandler = new Dxt5();


            //decode the DXT Texture
            $rgba = $dxtHandler->decode(
                $ddsDecoded['data'],
                $ddsDecoded['width'],
                $ddsDecoded['height']
            );

        }else if($ddsDecoded['format'] == ""){

            $data = new NBinary($ddsDecoded['data']);
            $rgba = [];
            while($data->remain()){
                $rgba[] = $data->consume(1, NBinary::U_INT_8);
            }

        }else{
            var_dump($ddsDecoded, $texture);
            throw new \Exception('Format not implemented: ' . $ddsDecoded['format']);
        }

        return $rgba;

    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    public function unpack(NBinary $binary, $game, $platform){

        $imageHandler = new Image();
        $header = $this->parseHeader($binary);

        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);


            //            if ($texture['mipMapCount'] > 1){
//                throw new \Exception('MipMap handler missed');
//            }


            if ($texture['width'] <= 2 && $texture['height'] <= 2){
                $currentOffset = $texture['nextOffset'];

                $header['numTextures']--;
                continue;
            }

            $rgba = $this->convertToBmp($texture);

            $textures[$texture['name'] . ".png"] = $imageHandler->rgbaToImage($rgba, $texture['width'], $texture['height']);

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