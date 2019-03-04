<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);
        $nextChar = substr($input, $current + 1, 1);

        //take sure its not an assignment
        if ($char == ":" && $nextChar != "="){
            return [
                'type' => Token::T_DEFINE,
                'value' => ":"
            ];
        }

        return false;
    }

}