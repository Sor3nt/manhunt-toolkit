<?php
namespace App\Service\Compiler\Tokens;

class T_IS_GREATER {

    static public function match( $input, $current ){

        $char = substr($input, $current, 1);

        if ($char == ">"){
            return [
                'type' => 'T_IS_GREATER',
                'value' => ">"
            ];
        }

        return false;
    }

}