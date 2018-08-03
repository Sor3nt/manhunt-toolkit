<?php
namespace App\Service\Compiler\Tokens;

class T_SELF {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 4));

        if ($chars == "this"){
            $lastChar = substr($input, $current + 4, 1);

            if ($lastChar == " " || $lastChar == "," || $lastChar == ")" || $lastChar == ""){

                return [
                    'type' => 'T_SELF',
                    'value' => "this"
                ];
            }


        }

        return false;
    }

}