<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_LINEEND {

    static public function match( $input, $current, $tokens ){

        $chars = substr($input, $current, 1);

        if ($chars == ";"){

            return [
                'type' => Token::T_LINEEND,
                'value' => ";"
            ];

        }

        return false;

    }

}