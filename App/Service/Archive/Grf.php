<?php
namespace App\Service\Archive;

use App\Service\Archive\Grf\Build;
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

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            $ext = strtolower($file->getExtension());
            if ($ext !== "json") return false;

            if(strpos($file->getContents(), 'nodeName') !== false){
                return true;
            }

        }

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
     * @param $pathFilename
     * @param $game
     * @param $platform
     * @return array
     */
    public function pack( $pathFilename, $game, $platform ){

        return (new Build())->build($pathFilename, $game);

    }
}