<?php
namespace App\Service\Archive;

use App\Service\Archive\Fsb\PhpFsbExt;
use App\Service\Archive\Fsb4\Build;
use App\Service\Archive\Fsb4\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Fsb4 extends Archive {
    public $name = 'Audio File (FSB4)';

    public $debug = false;

    public static $validationMap = [
        [0, 4, NBinary::STRING, ['FSB4']]
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
            if(strtolower($file->getFilename()) == "fsb4.json") return true;
        }

        return false;

    }

    public function unpack(NBinary $binary, $game, $platform){
        $fsbExt = new PhpFsbExt();
        $fsbExt->debug = $this->debug;
        return $fsbExt->encode($binary);
    }

    public function pack( $pathFilename, $game, $platform){
        return (new Build())->build( $pathFilename, $platform );
    }

}