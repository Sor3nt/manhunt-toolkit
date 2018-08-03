<?php
namespace App\Service\Compiler\Tokens;

class T_SCRIPT {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 7));

        if ($char == "script "){
            return [
                'type' => 'T_SCRIPT',
                'value' => "script"
            ];
        }

        return false;
    }

}