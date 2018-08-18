<?php
namespace App\Service\Compiler\Tokens;

class T_IS_GREATER_EQUAL {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 2);

        if ($char == ">="){
            return [
                'type' => 'T_IS_GREATER_EQUAL',
                'value' => ">="
            ];
        }

        return false;
    }

}