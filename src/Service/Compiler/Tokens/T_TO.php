<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_TO {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1 , 4));

        if ($chars == " to "){
            return [
                'type' => Token::T_TO,
                'value' => "to"
            ];
        }

        return false;
    }

}