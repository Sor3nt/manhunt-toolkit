<?php
namespace App\Service\Compiler\Tokens;

class T_IS_EQUAL {

    static public function match( $input, $current ){


        $char = strtolower(substr($input, $current - 1, 3));

        if ($char == " = "){
            return [
                'type' => 'T_IS_EQUAL',
                'value' => "="
            ];
        }

        return false;
    }

}