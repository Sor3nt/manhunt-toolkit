<?php
namespace App\Service\Compiler\Tokens;

class T_CUSTOM_FUNCTION {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 9));

        if ($char == "function "){

            return [
                'type' => 'T_CUSTOM_FUNCTION',
                'value' => "function"
            ];
        }

        return false;
    }

}