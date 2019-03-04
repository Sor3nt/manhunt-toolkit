<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_THEN {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 5));

        if ($chars == "then "){
            return [
                'type' => Token::T_THEN,
                'value' => "then"
            ];

        }

        return false;
    }

}