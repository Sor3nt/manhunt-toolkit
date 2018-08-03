<?php
namespace App\Service\Compiler\Tokens;

class T_SQUARE_BRACKET_CLOSE {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);

        if ($char == "["){
            return [
                'type' => 'T_SQUARE_BRACKET_CLOSE',
                'value' => "]"
            ];
        }

        return false;
    }

}