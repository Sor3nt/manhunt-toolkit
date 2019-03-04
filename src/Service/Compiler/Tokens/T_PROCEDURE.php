<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_PROCEDURE {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 10));

        if ($char == "procedure "){
            return [
                'type' => Token::T_PROCEDURE,
                'value' => "procedure"
            ];
        }

        return false;
    }

}