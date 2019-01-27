<?php
namespace App\Service\Compiler\Tokens;

class T_BOOLEAN {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "true"){
            return [
                'type' => 'T_BOOLEAN',
                'value' => "true"
            ];
        }

        $char = strtolower(substr($input, $current, 5));
        if ($char == "false"){
            return [
                'type' => 'T_BOOLEAN',
                'value' => "false"
            ];
        }

        return false;
    }

}