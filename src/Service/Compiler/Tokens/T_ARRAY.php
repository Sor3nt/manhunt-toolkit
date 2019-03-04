<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_ARRAY {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 6));

        if ($chars == " array"){
            return [
                'type' => Token::T_ARRAY,
                'value' => "array"
            ];
        }

        return false;
    }

}