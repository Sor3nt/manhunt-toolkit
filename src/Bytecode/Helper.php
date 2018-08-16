<?php
namespace App\Bytecode;


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


}