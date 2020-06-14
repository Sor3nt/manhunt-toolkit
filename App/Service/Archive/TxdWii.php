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

//    public static $supported = 'txd';

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['54444354']]
    ];

    private $ps2;



    public function __construct()
    {
//        $this->ps2 = new Ps2();
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

        $header['firstOffset'] = $binary->consume(4,  NBinary::INT_32);
        $header['lastTOffset'] = $binary->consume(4,  NBinary::INT_32);

        return $header;
    }

    private function parseTexture( $startOffset, NBinary $binary ){

        $binary->jumpTo($startOffset);


        $texture = [];
        $binary->numericBigEndian = true;
        $texture['nextOffset'] = $binary->consume(4,  NBinary::INT_32);
        $texture['prevOffset'] = $binary->consume(4,  NBinary::INT_32);

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

//var_dump($texture['dataOffset']);
        $texture['unknown3'] = $binary->consume(4,  NBinary::INT_32);
        $texture['dataSize'] = $binary->consume(4,  NBinary::INT_32);
        $texture['empty'] = $binary->consume(20,  NBinary::HEX);

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

        $header = $this->parseHeader($binary);
        $currentOffset = $header['firstOffset'];
        $imageHandler = new Image();

        $textures = [];
        while($header['numTextures'] > 0 ) {

            $texture = $this->parseTexture($currentOffset, $binary);


            if ($texture == false) continue;

            $dxtHandler = new Dxt1();

            $texture['data'] = $this->unswizzleWii($texture, $texture['data']);

//            if ($texture['name'] == "FE_execramps"){
//
//
//                $bmpRgba = $dxtHandler->decodeWii(
//                    $texture['data'],
//                    $texture['width'],
//                    $texture['height'],
//                    'abgr'
//                );
//
//                $image = $imageHandler->saveRGBAImage($bmpRgba, $texture['width'],$texture['height']);
//                $textures[$texture['name'] . '.png'] = $image;
//
//                return $textures;
//
//                var_dump($bmpRgba);
//                exit;
//                $data = new NBinary($texture['data']);
//
//                $bmpRgba = [];
//                while($data->remain()){
//                    $bmpRgba[] = 0;
//                    $bmpRgba[] = 0;
//                    $bmpRgba[] = 0;
//                    $bmpRgba[] = $data->consume(2, NBinary::U_INT_8);
//                }
//
//
//                $image = $imageHandler->saveRGBAImage($bmpRgba, $texture['width'],$texture['height']);
//
//                $textures[$texture['name'] . '.png'] = $image;
//
//                return $textures;
//            }else{
//                $currentOffset = $texture['nextOffset'];
//                $header['numTextures']--;
//                continue;
//            }

            $bmpRgba = $dxtHandler->decodeWii(
                $texture['data'],
                $texture['width'],
                $texture['height']
            );


            $image = $imageHandler->saveRGBAImage($bmpRgba, $texture['width'],$texture['height']);

            $textures[$texture['name'] . '.png'] = $image;

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }

        return $textures;
    }

    private function unswizzleWii($texture, $input ){
        $result = [];

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

    private function unswizzlePsp($texture, $bmpRgba, $as4Bit = false)
    {


        $out = [];
        $rowblocks = ($texture['width'] / 4);

        for ($j = 0; $j < $texture['height']; ++$j) {
            for ($i = 0; $i < $texture['width']; ++$i) {
                $blockx = $i / 4;
                $blocky = $j / 4;

                $x = ($i - $blockx * 4);
                $y = ($j - $blocky * 4);
                $block_index = $blockx + (($blocky) * $rowblocks);
                $block_address = $block_index * 4 * 4;

                $target = ($block_address + $x + $y) * 4;
                $source = ($i + $j * $texture['width']) * 4;
//                var_dump($target . " " . $source);
                $out[$target] = $bmpRgba[$source];

            }
        }
//        exit;
        return $out;
    }


    private function unswizzlePs2($texture, $bmpRgba ){
        $result = [];

        for ($y = 0; $y < $texture['height']; $y++){

            for ($x = 0; $x < $texture['width']; $x++) {
                $block_loc = ($y&(~15))*$texture['width'] + ($x&(~15))*2;
                $swap_sel = ((($y+2)>>2)&1)*4;
                $ypos = ((($y&(~3))>>1) + ($y&1))&7;
                $column_loc = $ypos*$texture['width']*2 + (($x+$swap_sel)&7)*4;
                $byte_sum = (($y>>1)&1) + (($x>>2)&2);
                $swizzled = $block_loc + $column_loc + $byte_sum;

                $result[$y*$texture['width']+$x] = $bmpRgba[$swizzled];
            }

        }

        return $result;
    }

    public function pack( $data, $game, $platform){

        die("Packing it not supported.");

    }


}