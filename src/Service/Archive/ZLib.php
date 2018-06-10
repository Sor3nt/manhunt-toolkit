<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;

abstract class ZLib extends Helper {

    public $header = null;


    /**
     * @param $content
     * @return string
     */
    public function compress($content){
        $size = $this->toSize(bin2hex($content));

        $packed =
            $this->header['start'] .
            $size .
            $this->header['separator'] .
            $this->zLibCompress($content)
        ;

        return hex2bin($packed);
    }

    /**
     * @param $content
     * @return string
     */
    public function uncompress($content){

        $content = substr( bin2hex($content),
            strlen($this->header['start']) +
            strlen('00000000') +
            strlen($this->header['separator'])
        );

        return zlib_decode(hex2bin($content));
    }

    /**
     * @param $string
     * @return string
     * @throws \Exception
     */
    private function zLibCompress($string){

        $hex = bin2hex(zlib_encode($string, ZLIB_ENCODING_DEFLATE));

        if (substr($hex, 0, 4) !== '789c'){
            throw new \Exception('Unknown header start');
        }

        $hex = substr($hex, 4);

        return $hex;
    }


}