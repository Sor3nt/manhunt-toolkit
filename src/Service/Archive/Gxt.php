<?php
namespace App\Service\Archive;

class Gxt {


    public function unpack($data){
        die("todo)");

        $tkey = $this->substr($data, 0, 4, false);

        $size = $this->substr($data, 4, 4, false);

        $size = current(unpack('L', $size)) ;

        $pos = 8;
        for($i = 0; $i < $size / 20; $i++){
            $entryStringOffset = $this->substr($data, $pos, 4, false);
            $pos = $pos + 4;
            $entryName = $this->substr($data, $pos, 12, false);
            $pos = $pos + 12;
            $entryId = $this->substr($data, $pos , 4, false);
            $pos = $pos + 4;

            var_dump($entryName);


        }
exit;


        $remain = $this->substr($data, 9);


        $pos = mb_strpos($remain, "\x00");
        $name = $this->substr($remain, 0, $pos);
        $missedToFinal4Byte = 4 - ((mb_strlen($name) ) % 4) ;
        $remain = mb_substr($remain, $pos + $missedToFinal4Byte );

        $ka = $this->substr($remain, 0, 12);



        // the first index tell us how many records we have in this file
//        $ = current(unpack("L", hex2bin(substr($hex, 8, 8))));

        var_dump($name);
        exit;

    }

    public function pack( $records ){

    }

    private function substr($string, $start = 0, $length = null, $mb = true){

        if ($mb){
            return mb_substr($string, $start, $length);
        }

        return hex2bin(substr(bin2hex($string), $start * 2, is_null($length) ? null : $length * 2));
    }

}