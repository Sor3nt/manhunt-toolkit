<?php
namespace App\Service\Compiler\Tokens;

class T_MULTIPLY {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current - 1, 3));

        if ($char == " * "){
            return [
                'type' => 'T_MULTIPLY',
                'value' => "*"
            ];
        }

        return false;
    }

}