<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;
use App\Service\NBinary;

class Inst extends Archive {

    public $name = 'Entity Positions';

    public static $supported = [
        'entity.inst',
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
        if (!$input instanceof NBinary) return false;

        if (
            strpos($input->binary, 'record') !== false &&
            strpos($input->binary, 'internalName') !== false &&
            strpos($input->binary, 'entityClass') !== false
        )
            return true;

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
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform){

        $records = \json_decode($records->binary, true);
        return (new Build())->build( $records, $game, $platform );
    }



}
