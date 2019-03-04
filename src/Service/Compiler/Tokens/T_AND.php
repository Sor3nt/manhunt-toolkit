<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_AND {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 5));

        if ($chars == " and "){
            return [
                'type' => Token::T_AND,
                'value' => "and"
            ];
        }

        return false;
    }

}