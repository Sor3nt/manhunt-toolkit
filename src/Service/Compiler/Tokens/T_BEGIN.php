<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_BEGIN {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 5));

        $lastChars = substr($input, $current + 5, 1);

        if ($chars == "begin" && $lastChars == " "){

            return [
                'type' => Token::T_BEGIN,
                'value' => "begin"
            ];

        }

        return false;
    }

}