<?php

namespace App\Service\Archive;

use App\MHT;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Fxb extends Archive
{

    public $name = 'Effects';

    public static $supported = 'fxb';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {
        return false;
    }



    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform)
    {
        $fourCC = $binary->consume(4, NBinary::BINARY);
        $count = $binary->consume(4, NBinary::INT_32);
        $files = [];
        while($count--){
            $files['entry_' . $count . '.json'] = $this->parseEntry($binary);
        }

        return $files;
    }

    private function parseEntry(NBinary $binary){
        $fourCC = $binary->consume(4, NBinary::BINARY);
        if ($fourCC !== "_ys_") die(__LINE__ . ' invalid');

        $hash = $binary->consume(4, NBinary::HEX);

        $length = $binary->consume(4, NBinary::FLOAT_32);
        $playmode = $binary->consume(1, NBinary::INT_8);
        $cullDist = $binary->consume(2, NBinary::INT_16);

        $unknown = $binary->consume(4, NBinary::INT_32);
        if ($unknown > 0){
            $unknownFloat = $binary->consume(16, NBinary::HEX);
            $unknownFloat2 = $binary->consume(4, NBinary::FLOAT_32);

            if ($unknownFloat2 > 0){ //0057D744

            }
        }

        $numPrims = $binary->consume(1, NBinary::INT_8);

        $results = [];
        while($numPrims--){
            $result = [
                'blocks' => []
            ];

            $result['base'] = $this->parsePrimsBaseData($binary);

            $fourCC = $binary->consume(4, NBinary::BINARY);
            if ($fourCC !== "_mi_") die(__LINE__ . ' invalid');

            $numInfos = $binary->consume(4, NBinary::INT_32);

            while($numInfos--){
                $result['blocks'][] = $this->getBlock($binary);
            }

            $results[] = $result;
        }

        return [
            'hash' => $hash,
            'length' => $length,
            'playmode' => $playmode,
            'cullDist' => $cullDist,
            'prims' => $results
        ];
    }


    private function getBlock(NBinary $binary){
        $unknownShort = $binary->consume(2, NBinary::HEX);


        $fourCC = $binary->consume(4, NBinary::BINARY);
        $zero = $binary->consume(4, NBinary::INT_32);

        $factor = $binary->consume(1, NBinary::INT_8);
        $zeroShort = $binary->consume(2, NBinary::INT_16);
        $unknownShortBlock = [];
        if ($factor > 1){

            $c = $factor;
            while($c-- - 1){
                $unknownShortBlock[] = $binary->consume(2 , NBinary::INT_16) / 32767.0;

            }
        }

        $count = $binary->consume(1, NBinary::INT_8);
        $entries = $count * $factor;

        $data = [];
        while($entries--){

            if ($fourCC === "_fi_"){
                $data[] = (float)$binary->consume(4, NBinary::FLOAT_32);
            }else{
                $data[] = $binary->consume(2, NBinary::INT_16) / 32767.0;
            }
        }

        $data = array_chunk($data, $factor);

        return [
            'fourCC' => $fourCC,
            'unknownShort' => $unknownShort,
            'unknownShortBlock' => $unknownShortBlock,
            'data' => $data
        ];


    }

    private function getName(NBinary $binary){
        $size = $binary->consume(4, NBinary::INT_32);
        return $binary->consume($size, NBinary::STRING);

    }

    private function parsePrimsBaseData(NBinary $binary){
        $fourCC = $binary->consume(4, NBinary::BINARY);
        if ($fourCC !== "_me_") die(__LINE__ . ' invalid ' . $fourCC);

        $unknownData = $binary->consume(2, NBinary::HEX);
        $unknownShort = $binary->consume(3, NBinary::INT_16);

        $srcBlendId = $binary->consume(1, NBinary::INT_8);
        $dstBlendId = $binary->consume(1, NBinary::INT_8);
        $alphaOn = $binary->consume(1, NBinary::INT_8);

        $unknownData2 = $binary->consume(3, NBinary::INT_32);//should be 4 ?!
        $unknownFlag = $binary->consume(4, NBinary::INT_32);

        $unknownData3 = false;
        if ($unknownFlag > 0){
            $unknownData3 = $binary->consume(3 * 8, NBinary::HEX);
        }

        $textureId1 = $binary->consume(4, NBinary::INT_32);
        $textureId2 = $binary->consume(4, NBinary::INT_32);
        $textureId3 = $binary->consume(4, NBinary::INT_32);
        $textureId4 = $binary->consume(4, NBinary::INT_32);

        $textures = [$this->getName($binary)];
        if ($textureId2 > 0) $textures[] = $this->getName($binary);
        if ($textureId3 > 0) $textures[] = $this->getName($binary);
        if ($textureId4 > 0) $textures[] = $this->getName($binary);

        return [
            'unknownData' => $unknownData,
            'unknownData2' => $unknownData2,
            'unknownFlag' => $unknownFlag,
            'unknownData3' => $unknownData3,
            'srcBlendId' => $srcBlendId,
            'dstBlendId' => $dstBlendId,
            'alphaOn' => $alphaOn,
            'textures' => $textures
        ];

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack($pathFilename, $game, $platform)
    {
    }


}
