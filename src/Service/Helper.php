<?php
namespace App\Service;


use App\Bytecode\Mls\Header;
use App\Bytecode\Mls\Scripts;
use App\Bytecode\Mls\Sequence;
use App\Bytecode\Mls\Srce;
use App\Service\Compiler\Token;

class Helper{

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
        if ($toBig){
            $codeLenght = self::toBigEndian(self::pad(dechex($int),4, true));
            return self::pad($codeLenght);

        }else{
            $codeLenght = self::toLittleEndian(self::pad(dechex($int),8, true));
            return self::pad($codeLenght);
        }
    }

    static function fromHexToInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));

    }

    static function fromFloatToHex( $value ){

        return strrev(self::toBigEndian(unpack('h*', pack('f', $value))[1]));

    }

    static function findOpenContainerByEnd( $tokens ){
        $tokens = array_reverse($tokens);

        $endCount = 1;
        foreach ($tokens as $index => $token) {
            if (
                $token['type'] == Token::T_IF_END ||
                $token['type'] == Token::T_WHILE_END ||
                $token['type'] == Token::T_SWITCH_END ||
                $token['type'] == Token::T_CASE_END ||
                $token['type'] == Token::T_FOR_END ||
                $token['type'] == Token::T_END_ELSE ||
                $token['type'] == Token::T_SCRIPT_END
            ){
                $endCount++;
            }

            if ($token['type'] == Token::T_BEGIN || $token['type'] == Token::T_OF){

                $endCount--;

                if ($endCount == 0){


                    if ($tokens[$index]['type'] == Token::T_OF){
                        return Token::T_SWITCH_END;
                    }

                    switch($tokens[$index + 1]['type']){
                        case Token::T_THEN:
                            return Token::T_IF_END;
                            break;
                        case Token::T_ELSE:
                            return Token::T_IF_END;
                            break;
                        case Token::T_DO:

                            $i = 0;
                            while($index + $i < count($tokens)){
                                if ($tokens[$index + $i]['type'] == Token::T_FOR){
                                    return Token::T_FOR_END;
                                }
                                if ($tokens[$index + $i]['type'] == Token::T_WHILE){
                                    return Token::T_WHILE_END;
                                }
                                $i++;
                            }


                            break;
                        case Token::T_DEFINE:
                            return Token::T_CASE_END;
                            break;
                        default:

                            return Token::T_SCRIPT_END;
                    }
                }
            }
        }

        return false;
    }




    public static function int8($i) {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }

    public static function int16($i) {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }

}