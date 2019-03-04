<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_NOT {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "not " || $char == "not("){
            return [
                'type' => Token::T_NOT,
                'value' => "NOT"
            ];
        }

        return false;
    }

}