<?php
namespace App\Service\Compiler\Tokens;

class T_FALSE {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 5));

        if ($char == "false"){
            return [
                'type' => 'T_FALSE',
                'value' => "FALSE"
            ];
        }

        return false;
    }

}