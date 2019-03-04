<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_FOR {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current , 4));

        if ($chars == "for "){
            return [
                'type' => Token::T_FOR,
                'value' => "for"
            ];
        }

        return false;
    }

}