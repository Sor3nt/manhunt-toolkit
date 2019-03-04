<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_WHITESPACE {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);

        if ($char == " " || $char == "\n" || $char == "\t"){
            return [
                'type' => Token::T_WHITESPACE,
                'value' => " "
            ];
        }

        return false;
    }

}