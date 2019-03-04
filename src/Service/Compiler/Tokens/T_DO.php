<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DO {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 4));

        if ($chars == " do "){
            return [
                'type' => Token::T_DO,
                'value' => "do"
            ];
        }

        return false;
    }

}