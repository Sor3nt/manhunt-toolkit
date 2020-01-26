<?php
namespace App\Service\Archive;

use App\Service\Archive\Grf\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Grf extends Archive {
    public $name = 'AI Map Path';

    public static $supported = 'grf';


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

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        return (new Extract())->get($binary, $game);
    }



    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform ){

        $binary = new NBinary();
        $binary->write($pathFilename->count(), NBinary::INT_32);


        return $binary->binary;
    }
}