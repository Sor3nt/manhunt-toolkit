<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Pack extends Archive {

    public $name = 'General Data Container (PS2)';

    public static $validationMap = [
        [0, 4, NBinary::STRING, ['PACK']]
    ];
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

        $fourCC = $binary->consume(4, NBinary::INT_32);
        $dictoryOffset = $binary->consume(4, NBinary::INT_32);

        $binary->current = $dictoryOffset;
        $binary->current += 160; // zeros?!

        $files = [];
        while($binary->remain() > 0){
            $fileName = $binary->consume(72, NBinary::STRING);
            $fileOffset = $binary->consume(4, NBinary::INT_32);
            $fileSize = $binary->consume(4, NBinary::INT_32);

            $fileName = str_replace("\\", "/", $fileName);
            $files[] = [$fileName, $fileOffset, $fileSize];
        }

        $result = [];
        foreach ($files as $file) {
            $binary->current = $file[1];
            $result[$file[0]] = $binary->consume($file[2], NBinary::BINARY);

        }

        return $result;
    }


    /**
     * @param $files
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $files, $game, $platform ){
        return false;
    }
}