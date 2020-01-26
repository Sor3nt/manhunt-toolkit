<?php
namespace App\Service\Archive;


use App\Service\Binary;
use App\Service\Helper;

class ZLib {

    /** @var array  */
    static $header = [
        'start' => '5A32484D',      // the mls header
        'separator' => '78DA'       // zlib compress factor (best)
    ];

    /**
     * @param $content
     * @return string
     */
    static function compress($content){
        $size = Helper::toSize(bin2hex($content));

        $packed =
            self::$header['start'] .
            $size .
            self::$header['separator'] .
            self::zLibCompress($content)
        ;

        return hex2bin($packed);
    }

    /**
     * @param $content
     * @return string
     */
    static function uncompress($content){

        $content = substr( bin2hex($content),
            strlen(self::$header['start']) +
            strlen('00000000') +
            strlen(self::$header['separator'])
        );

        return zlib_decode(hex2bin($content));
    }

    /**
     * @param $string
     * @return string
     * @throws \Exception
     */
    static function zLibCompress($string){
        $hex = bin2hex(zlib_encode($string, ZLIB_ENCODING_DEFLATE));
        return substr($hex, 4);
    }


}