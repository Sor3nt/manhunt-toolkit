<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_ASSIGN {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 2);

        if ($char == ":="){
            return [
                'type' => Token::T_ASSIGN,
                'value' => ":="
            ];
        }

        return false;
    }

}