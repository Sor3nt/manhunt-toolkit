<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

abstract class Archive {

    public $name = 'unknown';

    public static $supported = null;
    public static $validationMap = null;
    public static $inValidationMap = null;

    abstract public function pack( $data, $game, $platform );
    abstract public function unpack( NBinary $binary, $game, $platform );

    /**
     * @param $fileNameOrFolder
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canHandle($fileNameOrFolder, $input, $game, $platform){

        // a finder can only mean packing
        if ($input instanceof Finder){
            return static::canPack($fileNameOrFolder, $input, $game, $platform);
        }

        $pathInfo = pathinfo($fileNameOrFolder);

        // every json file is from MHT and mean we want to pack it
        if ($pathInfo['extension'] == "json" || strpos($pathInfo['basename'], '#RIB.wav') !== false){
            return static::canPack($fileNameOrFolder, $input, $game, $platform);
        }

        return static::canUnpack($fileNameOrFolder, $input, $game, $platform);

    }

    /**
     * @param $pathFilename
     * @param $binary
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $binary, $game, $platform ){
        return false;
    }


    public static function canUnpack( $input, NBinary $binary, $game, $platform ){


        if (static::$supported != null){
            $pathInfo = pathinfo($input);

            if (is_string(static::$supported)){
                //Extension match
                if (strtolower($pathInfo['extension']) == static::$supported) return true;

            }else if (is_array(static::$supported) && count(static::$supported) > 0){

                //filename match
                if (in_array(strtolower($pathInfo['basename']), static::$supported)) return true;

                //Extension match
                if (in_array(strtolower($pathInfo['extension']), static::$supported)) return true;
            }
        }

        if (is_array(static::$validationMap)){
            $valid = 0;
            foreach (static::$validationMap as $map) {
                list($offset, $bytes, $type, $matchTo) = $map;

                $binary->jumpTo($offset);
                $result = $binary->consume($bytes, $type);

                if (in_array($result, $matchTo) !== false) $valid++;
            }

            $binary->jumpTo(0);

            return $valid == count(static::$validationMap);
        }


        return false;
    }

}