<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_OF {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 4));

        if ($chars == " of "){
            return [
                'type' => Token::T_OF,
                'value' => "of"
            ];
        }

        return false;
    }

}