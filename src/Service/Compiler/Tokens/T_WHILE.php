<?php
namespace App\Service\Compiler\Tokens;

class T_WHILE {

    static public function match( $input, $current, $tokens ){

        $chars = substr($input, $current, 6);

        if (strtolower(substr($chars, 0, 5)) == "while"){

            $lastChar = substr($chars, 5);

            if ($lastChar == " " || $lastChar == "("){
                return [
                    'type' => 'T_WHILE',
                    'value' => "while"
                ];

            }
        }

        return false;
    }

}