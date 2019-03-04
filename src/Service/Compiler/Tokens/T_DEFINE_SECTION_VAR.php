<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE_SECTION_VAR {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "var "){
            return [
                'type' => Token::T_DEFINE_SECTION_VAR,
                'value' => "var"
            ];
        }

        return false;
    }

}