<?php
namespace App\Service\Compiler\Tokens;

class T_IS_NOT_EQUAL {

    static public function match( $input, $current ){

        $char = substr($input, $current, 2);

        if ($char == "<>"){
            return [
                'type' => 'T_IS_NOT_EQUAL',
                'value' => "<>"
            ];
        }

        return false;
    }

}