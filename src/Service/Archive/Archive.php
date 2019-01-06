<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

abstract class Archive {

    public $name = 'unknown';

    public static $supported = null;
    public static $validationMap = null;

    abstract public function pack( $data, $game = null );
    abstract public function unpack( NBinary $binary, $game = null );

    /**
     * @param $fileNameOrFolder
     * @param NBinary|Finder $input
     * @param null $game
     * @return bool
     */
    public static function canHandle($fileNameOrFolder, $input, $game = null){

        // a finder can only mean packing
        if ($input instanceof Finder){
            return static::canPack($fileNameOrFolder, $input, $game);
        }

        $pathInfo = pathinfo($fileNameOrFolder);

        // every json file is from MHT and mean we want to pack it
        if ($pathInfo['extension'] == "json"){
            return static::canPack($fileNameOrFolder, $input, $game);
        }

        return static::canUnpack($fileNameOrFolder, $input, $game);

    }

    public static function canPack( $pathFilename, $binary, $game = null ){
        return false;
//        throw new \Exception('canUnpack check not implemented');
    }


    public static function canUnpack( $input, NBinary $binary, $game = null ){


        if (static::$supported != null){
            $pathInfo = pathinfo($input);

            if (is_string(static::$supported)){
                //Extension match
                if (strtolower($pathInfo['extension']) == static::$supported) return true;

            }else if (is_array(static::$supported) && count(static::$supported) > 0){

                //filename match
                if (in_array($pathInfo['basename'], static::$supported)) return true;
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