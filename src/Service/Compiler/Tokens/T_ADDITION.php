<?php
namespace App\Service\Compiler\Tokens;

class T_ADDITION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 1));

        if ($char == "+"){
            return [
                'type' => 'T_ADDITION',
                'value' => "+"
            ];
        }

        return false;
    }

}