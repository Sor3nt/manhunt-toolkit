<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DIVISION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current - 1, 5));

        if ($char == " div "){
            return [
                'type' => Token::T_DEVISION,
                'value' => "div"
            ];
        }

        return false;
    }

}