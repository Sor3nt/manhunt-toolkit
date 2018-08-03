<?php
namespace App\Service\Compiler\Tokens;

class T_IF {

    static public function match( $input, $current, $tokens ){

        $chars = substr($input, $current, 3);

        if (strtolower(substr($chars, 0, 2)) == "if"){

            $lastChar = substr($chars, 2);

            if ($lastChar == " " || $lastChar == "("){
                return [
                    'type' => 'T_IF',
                    'value' => "if"
                ];

            }
        }

        return false;
    }

}