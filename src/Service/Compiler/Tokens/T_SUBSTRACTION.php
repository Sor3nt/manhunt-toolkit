<?php
namespace App\Service\Compiler\Tokens;

class T_SUBSTRACTION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current - 1, 3));

        if ($char == " - "){
            return [
                'type' => 'T_SUBSTRACTION',
                'value' => "-"
            ];
        }

        return false;
    }

}