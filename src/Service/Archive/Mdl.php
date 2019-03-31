<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Mdl\Extract;
use App\Service\NBinary;

class Mdl extends Archive {
    public $name = 'Model File';

    public static $supported = 'mdl';

    public $game = MHT::GAME_MANHUNT_2;
    public $platform = MHT::PLATFORM_AUTO;

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

    public function pack( $pathFilename, $game, $platform ){
        return false;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $this->platform = $platform;

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $extractor = new Extract();
        $results = $extractor->get($binary);
var_dump("end");
exit;

    }




}