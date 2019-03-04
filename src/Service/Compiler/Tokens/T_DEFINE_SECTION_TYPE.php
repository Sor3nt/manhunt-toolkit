<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE_SECTION_TYPE {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 5));

        if ($char == "type "){
            return [
                'type' => Token::T_DEFINE_SECTION_TYPE,
                'value' => "type"
            ];
        }

        return false;
    }

}