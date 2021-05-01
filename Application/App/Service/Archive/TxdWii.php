<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Image;
use App\Service\Archive\Textures\Playstation;
use App\Service\Archive\Textures\Ps2;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

/**
 * Class TxdWii
 * @package App\Service\Archive
 *
 * based on https://github.com/Zheneq/Noesis-Plugins/blob/master/lib_zq_nintendo_tex.py
 * thx for the great work
 */
class TxdWii extends Archive {
    public $name = 'Textures (Wii)';

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['54444354']]
    ];

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

    private function parseHeader( NBinary &$binary, $game ){

        $header =  [
            'magic'             => $binary->consume(4,  NBinary::STRING),
            'constNumber'       => $binary->consume(4,  NBinary::INT_32),
            'fileSize'          => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset'  => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset2' => $binary->consume(4,  NBinary::INT_32),
            'numIndex'          => $binary->consume(4,  NBinary::INT_32),
            'unknown'           => $binary->consume(8,  NBinary::HEX)
        ];

        $binary->numericBigEndian = false;
        $header['numTextures'] = $binary->consume(4,  NBinary::INT_32);
        $binary->numericBigEndian = true;

        if ($header['numTextures'] > 10000){
            $binary->current -= 4;
            $header['numTextures'] = $binary->consume(4,  NBinary::INT_32);
        }

        if ($game == MHT::GAME_BULLY){
            $header['unknown_bully'] = $binary->consume(4,  NBinary::INT_32);
            $header['lastTOffset'] = $binary->consume(4,  NBinary::INT_32);
            $header['firstOffset'] = $binary->consume(4,  NBinary::INT_32);

        }else{
            $header['firstOffset'] = $binary->consume(4,  NBinary::INT_32);
            $header['lastTOffset'] = $binary->consume(4,  NBinary::INT_32);

        }

        return $header;
    }

    private function parseTexture( $startOffset, NBinary $binary, $game ){
        $binary->jumpTo($startOffset);

        $texture = [];
        $binary->numericBigEndian = true;

        if ($game == MHT::GAME_BULLY){
            $texture['prevOffset'] = $binary->consume(4,  NBinary::INT_32);
            $texture['nextOffset'] = $binary->consume(4,  NBinary::INT_32);
        }else{
            $texture['nextOffset'] = $binary->consume(4,  NBinary::INT_32);
            $texture['prevOffset'] = $binary->consume(4,  NBinary::INT_32);

        }

        $binary->numericBigEndian = false;


        $texture = array_merge($texture,[
            'name'              => $binary->consume(96, NBinary::BINARY),

            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'rasterFormat'      => $binary->consume(4,  NBinary::HEX),

            'unknown'             => $binary->consume(4,  NBinary::INT_32),
            'unknown2'             => $binary->consume(4,  NBinary::INT_32),
        ]);
        $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

        if ($texture['name'] == ""){
            $texture['name'] = uniqid();
        }

        $binary->numericBigEndian = true;
        $texture['dataOffset'] = $binary->consume(4,  NBinary::INT_32);
        $binary->numericBigEndian = false;

        $texture['unknown3'] = $binary->consume(4,  NBinary::INT_32);
        $texture['dataSize'] = $binary->consume(4,  NBinary::INT_32);
        $texture['empty'] = $binary->consume(20,  NBinary::HEX);
//        var_dump($texture);
//        exit;

        $binary->jumpTo($texture['dataOffset']) ;

        $binary->numericBigEndian = true;

        $ident = $binary->consume(4,  NBinary::INT_32);
        $numTexHeaders = $binary->consume(4,  NBinary::INT_32);

        $offsetTexHeader = $binary->consume(4,  NBinary::INT_32);
        $offsetToHeader = $binary->consume(4,  NBinary::INT_32);

        $unknown1 = $binary->consume(4,  NBinary::INT_32);

        if ($numTexHeaders == 2){
            $unknown1a = $binary->consume(4,  NBinary::INT_32);
            $unknown1b = $binary->consume(4,  NBinary::INT_32);

            //TODO ALPHA CHANNELS
        }

        $height = $binary->consume(2,  NBinary::INT_16);
        $width = $binary->consume(2,  NBinary::INT_16);

        $texture['width'] = $width;
        $texture['height'] = $height;

        $binary->jumpTo($texture['dataOffset'] + (64 * $numTexHeaders) ) ;

        $texture['data'] = $binary->consume(
            $texture['dataSize']  ,
            NBinary::BINARY
        );

        return $texture;
    }



    public function unpack(NBinary $binary, $game, $platform){

        $binary->numericBigEndian = true;

        $header = $this->parseHeader($binary, $game);
        $currentOffset = $header['firstOffset'];
        $imageHandler = new Image();

        $textures = [];
        while($header['numTextures'] > 0 ) {
            $texture = $this->parseTexture($currentOffset, $binary, $game);
            if ($texture == false) continue;

            $texture['data'] = $this->unswizzleWii($texture, $texture['data']);

            $dxtHandler = new Dxt1();
            $bmpRgba = $dxtHandler->decodeWii(
                $texture['data'],
                $texture['width'],
                $texture['height']
            );

            $image = $imageHandler->rgbaToImage($bmpRgba, $texture['width'],$texture['height']);

            $textures[$texture['name'] . '.png'] = $image;

            $currentOffset = $texture['nextOffset'];
            if ($game === MHT::GAME_BULLY && $currentOffset === 40) return $textures;

            $header['numTextures']--;
        }

        return $textures;
    }

    private function unswizzleWii($texture, $input ){
        $bmpRgba = str_split(bin2hex($input), 2);
        $result = [];
        foreach ($bmpRgba as $item) {
            $result[] = '';
        }

        $BlocksPerW = $texture['width'] / 8;
        $BlocksPerH = $texture['height'] / 8;

        for ($h = 0; $h < $BlocksPerH; $h++){
            for ($w = 0; $w < $BlocksPerW; $w++) {
                for ($BlocksPerRow = 0; $BlocksPerRow < 2; $BlocksPerRow++) {
                    $swizzled = $h * $BlocksPerW * 32 + $w * 32 + $BlocksPerRow * 16;
                    $unswizzled = $h * $BlocksPerW * 32 + $w * 16 + $BlocksPerRow * $BlocksPerW * 16;


                    for ($n = 0; $n < 16; $n++){

                        $result[$unswizzled + $n] = $bmpRgba[$swizzled + $n];
                    }
                }
            }

        }

        return hex2bin(implode("", $result));
    }

    public function pack( $data, $game, $platform){

        die("Packing it not supported.");

    }


}