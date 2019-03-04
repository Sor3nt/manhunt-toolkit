<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_RECORD {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 7));

        if ($chars == " record"){
            return [
                'type' => Token::T_RECORD,
                'value' => "record"
            ];
        }

        return false;
    }

}