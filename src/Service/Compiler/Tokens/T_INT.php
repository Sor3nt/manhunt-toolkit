<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_INT {

    static public function match( $input, $current, $tokens ){

        $line = substr($input, $current);
        $offset = 0;

        $value = "";

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);
            if (is_numeric($char) || $char == "-"){
                $value .= $char;
            }else{

                // this is a float not a int
                if ($char == ".") return false;

                if ($value !== ""){

                    return [
                        'type' => Token::T_INT,
                        'value' => $value
                    ];
                }else{
                    return false;
                }
            }

            $offset++;
        }


        return false;
    }

}