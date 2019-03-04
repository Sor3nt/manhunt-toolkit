<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_SELF {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 4));

        if ($chars == "this"){
            $lastChar = substr($input, $current + 4, 1);

            if ($lastChar == " " || $lastChar == "," || $lastChar == ")" || $lastChar == ""){

                return [
                    'type' => Token::T_SELF,
                    'value' => "this"
                ];
            }


        }

        return false;
    }

}