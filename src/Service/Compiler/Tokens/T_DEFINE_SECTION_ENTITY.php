<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE_SECTION_ENTITY {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 7));

        if ($char == "entity "){
            return [
                'type' => Token::T_DEFINE_SECTION_ENTITY,
                'value' => "entity"
            ];
        }

        return false;
    }

}