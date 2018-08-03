<?php
namespace App\Service\Compiler\Tokens;

class T_FORWARD {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 8));

        if ($char == "forward;"){
            return [
                'type' => 'T_FORWARD',
                'value' => "forward"
            ];
        }

        return false;
    }

}