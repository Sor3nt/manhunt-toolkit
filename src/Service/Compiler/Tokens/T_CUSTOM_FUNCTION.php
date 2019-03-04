<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_CUSTOM_FUNCTION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 9));

        if ($char == "function "){

            return [
                'type' => Token::T_CUSTOM_FUNCTION,
                'value' => "function"
            ];
        }

        return false;
    }

}