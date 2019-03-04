<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_SCRIPT {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 7));

        if ($char == "script "){
            return [
                'type' => Token::T_SCRIPT,
                'value' => "script"
            ];
        }

        return false;
    }

}