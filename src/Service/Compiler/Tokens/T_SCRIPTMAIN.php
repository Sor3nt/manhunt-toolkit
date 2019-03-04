<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_SCRIPTMAIN {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 11));

        if ($char == "scriptmain "){
            return [
                'type' => Token::T_SCRIPTMAIN,
                'value' => "scriptmain"
            ];
        }

        return false;
    }

}