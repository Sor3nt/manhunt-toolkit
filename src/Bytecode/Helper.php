<?php
namespace App\Bytecode;


use App\Bytecode\Mls\Header;
use App\Bytecode\Mls\Scripts;
use App\Bytecode\Mls\Sequence;
use App\Bytecode\Mls\Srce;

class Helper{

    static function toBigEndian( $hex ){
        $split = str_split($hex, 2);
        $split = array_reverse($split);
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

    public function toSize( $dara, $bigEndian = true ){


        $codeLenght = strlen($dara) / 2;
        $padded = $this->pad(dechex($codeLenght),4, true);
        if ($bigEndian) $padded = $this->toBigEndian($padded);
        return $this->pad($padded);

    }

    static function fromIntToHex( $int ){
        $codeLenght = self::toBigEndian(self::pad(dechex($int),4, true));
        return self::pad($codeLenght);

    }
    static function fromHexToInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));

    }

    static function fromFloatToHex( $value ){

        return strrev(self::toBigEndian(unpack('h*', pack('f', $value))[1]));

    }


}