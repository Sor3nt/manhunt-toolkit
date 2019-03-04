<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE_SECTION_CONST {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 6));

        if ($char == "const "){
            return [
                'type' => Token::T_DEFINE_SECTION_CONST,
                'value' => "const"
            ];
        }



        return false;
    }

}