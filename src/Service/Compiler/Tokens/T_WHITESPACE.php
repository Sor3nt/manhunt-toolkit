<?php
namespace App\Service\Compiler\Tokens;

class T_WHITESPACE {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);

        if ($char == " " || $char == "\n" || $char == "\t"){
            return [
                'type' => 'T_WHITESPACE',
                'value' => " "
            ];
        }

        return false;
    }

}