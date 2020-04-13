<?php
namespace App\Service\Archive;

use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Afs extends Archive {

    public $name = 'Audio File (AFS)';

    public static $supported = 'afs';

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


    private function getBlock(NBinary $binary){


        $offset = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        $current = $binary->current;

        $binary->current = $offset;
        $data = $binary->consume($size, NBinary::BINARY);
        $binary->current = $current;

        return $data;

    }


    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $fourCC = $binary->consume(4, NBinary::STRING);
        $count = $binary->consume(4, NBinary::INT_32);

        $hashNames = explode("\x0D\x0A", $this->getBlock($binary));

        for($i = 1; $i < $count; $i++) {
            $name = $hashNames[$i - 1];
            $name = str_replace('\\', '/', $name);

            $files[$name] = $this->getBlock($binary);
        }

        return $files;


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
