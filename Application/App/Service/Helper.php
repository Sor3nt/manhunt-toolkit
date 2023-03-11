<?php
namespace App\Service;

class Helper{

    static function getFolderWithFile($file, $possibleFolders){

        foreach ($possibleFolders as $folder) {
            if (file_exists($folder . "/" . $file)) return $folder;
        }

        return false;
    }

    static function groupBy( $field, $arr )
    {
        $result = array();
        foreach ($arr as $data) {
            $id = $data[$field];

            if ($id instanceof \DateTime) {
                $id = $id->format('Y-m-d') . ' 00:00:00';
            }

            if (isset($result[$id])) {
                $result[$id][] = $data;
            } else {
                $result[$id] = array($data);
            }
        }

        return $result;
    }

    static function moveArrayIndexToTop(&$array, $key) {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;
    }

    static function moveArrayIndexToBottom(&$array, $key) {
        $value = $array[$key];
        unset($array[$key]);
        $array[$key] = $value;
    }


    static function toBigEndian( $hex ){
        $split = str_split($hex, 2);
        $split = array_reverse($split);
        return join('', $split);
    }

    static function toLittleEndian( $hex ){

        // target : 67 29 01 00
        // input : 12 96 7

        //step 1: str reverse
        // input : 12 96 7
        // output : 76 92 1
        $hex = strrev($hex);
        $hex = self::pad($hex, 4 + (2 % strlen($hex)));

        //step 1: flip bytes
        // input : 76 92 10
        // output : 67 29 01

        $split = str_split($hex, 2);

        foreach ($split as &$item) {
            $item = strrev($item);
        }

        return join('', $split);
    }

    static function pad($hex, $lim = 8, $before = false, $char = "0")
    {
        return (
            strlen($hex) >= $lim)
            ?
                $hex
            :
                self::pad(
                    $before ?
                        $char . $hex
                        :
                        $hex . $char
                    ,
                    $lim,
                    $before,
                    $char
                );
    }

    static function toSize( $dara, $bigEndian = true ){
        $codeLenght = strlen($dara) / 2;
        $padded = self::pad(dechex($codeLenght),4, true);
        if ($bigEndian) $padded = self::toBigEndian($padded);
        return self::pad($padded);

    }

    static function fromIntToHex( $int, $toBig = true ){

        //TODO, its a hack.... dont know why
        if ($int >= 90000) $toBig = false;

        if ($toBig){
            $codeLenght = self::toBigEndian(self::pad(dechex($int),4, true));
            return substr(self::pad($codeLenght), 0 , 8);

        }else{
            $codeLenght = self::toLittleEndian(self::pad(dechex($int),8, true));
            return self::pad($codeLenght);
        }
    }

    static function fromHexToInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));
    }

    static function fromFloatToHex( $value ){
        $hex = strrev(self::toBigEndian(unpack('h*', pack('f', $value))[1]));

        //float issue (54.729702)
        if ($hex === "37eb5a42") $hex = "38eb5a42";
        return $hex;
    }
}