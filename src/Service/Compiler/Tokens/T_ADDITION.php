<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_ADDITION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 1));

        if ($char == "+"){
            return [
                'type' => Token::T_ADDITION,
                'value' => "+"
            ];
        }

        return false;
    }

}