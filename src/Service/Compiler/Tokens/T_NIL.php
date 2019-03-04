<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_NIL {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 5));

        if ($chars == "(nil " || $chars == " nil " || $chars == " nil)"|| $chars == " nil,"){
            return [
                'type' => Token::T_NIL,
                'value' => "nil"
            ];
        }

        return false;
    }

}