<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_ELSE {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 5));

        if ($chars == "else "){
            return [
                'type' => Token::T_ELSE,
                'value' => "else"
            ];
        }

        return false;
    }

}