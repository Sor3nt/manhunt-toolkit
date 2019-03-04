<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_FORWARD {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 8));

        if ($char == "forward;"){
            return [
                'type' => Token::T_FORWARD,
                'value' => "forward"
            ];
        }

        return false;
    }

}