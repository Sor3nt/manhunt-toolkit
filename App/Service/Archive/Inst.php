<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Inst extends Archive {

    public $name = 'Entity Positions';

    public static $supported = [
        'entity.inst',
        'entity2.inst',
        'entity_pc.inst',
        'entity_wii.inst',
        'entinst.bin'
    ];


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

            $content = $file->getContents();
            return strpos($content, 'record') !== false &&
                strpos($content, 'internalName') !== false &&
                strpos($content, 'entityClass') !== false;
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
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){

        return (new Build())->build( $pathFilename, $game, $platform );
    }



}
