<?php
namespace App\Service\Compiler\Tokens;

class T_NULL {

    static public function match( $input, $current ){

        $char = substr($input, $current, 3);

        $charBefore = substr($input, $current - 1, 1);
        $charAfter = substr($input, $current + 3, 1);

        if ($char == "nil" && ( $charBefore == "(" || $charBefore == " "  ) && ( $charAfter == ")" || $charAfter == " "  ) ){
            return [
                'type' => 'T_NULL',
                'value' => "NIL"
            ];
        }

        return false;
    }

}