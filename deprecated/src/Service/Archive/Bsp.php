<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;

class Bsp extends Archive {
    public $name = 'Level Model File';

    public static $supported = 'bsp';

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

    public function unpack(NBinary $binary, $game, $platform){

        //it is already unzipped via NBinary
        return $binary->binary;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform ){

        return false;
    }


}