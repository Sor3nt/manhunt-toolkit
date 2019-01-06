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
     * @param NBinary $input
     * @param null $game
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game = null ){

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
     * Manhunt 2 - entity_pc.inst pack/unpack
     */

    public function unpack(NBinary $binary, $game = null){
        $extractor = new Extract();
        return $extractor->get($binary);
    }


    /**
     * @param NBinary $records
     * @param bool $bigEndian
     * @return null|string
     */
    public function pack( $records, $bigEndian = false ){

        $records = \json_decode($records->binary, true);

        return (new Build())->build( $records, $bigEndian );

    }



}
