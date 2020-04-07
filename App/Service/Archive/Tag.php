<?php
namespace App\Service\Archive;

use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Tag extends Archive {

    public $name = 'Level Audio Names';

    public static $supported = 'tag';

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



    private function parseName(NBinary $binary){
        $size = $binary->consume(4, NBinary::INT_32);
        if ($size == 0) return false;
        return $binary->getString();
//        return $binary->consume($size + 1, NBinary::STRING);
    }


    private function isFutureValueNewBlock(NBinary $binary){

        if ($binary->remain() == 0) return true;

        $hash = $binary->consume(4, NBinary::INT_32);
        $testFour = $binary->consume(4, NBinary::INT_32);
        $testZero = $binary->consume(4, NBinary::INT_32);

        $binary->current -= 12;

        if ($testFour == 4 && $testZero == 0) return false;

        return true;

    }

    private function getUnknownDataBlock(NBinary $binary){



        while($this->isFutureValueNewBlock($binary) == false){
            $unknown = $binary->consume(12, NBinary::HEX);
        }
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $count = $binary->consume(4, NBinary::INT_32);
        $this->getUnknownDataBlock($binary);
        $entries = [];


        while($binary->remain()){

            $hash = $binary->consume(4, NBinary::HEX);
            $unknown = $binary->consume(4, NBinary::INT_32);
            $hasData = $binary->consume(4, NBinary::INT_32);


            if (!$hasData) continue;

            $name = $this->parseName($binary);
            $entries[] = $name;




            $unknown2 = $binary->consume(4, NBinary::INT_32);
            $unknown3 = $binary->consume(4, NBinary::INT_32);

            if ($unknown3 == 2698){

                $secondName = $this->parseName($binary);
                $entries[] = $secondName;
                $unknown = $binary->consume(8, NBinary::HEX);
//                var_dump($binary->current);exit;

            }

            $this->getUnknownDataBlock($binary);

//            if ($i == 43){
//                echo $name . " ";
//                var_dump($binary->current);
//                exit;
//
//            }

        }

        return $entries;

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){
    }




}
