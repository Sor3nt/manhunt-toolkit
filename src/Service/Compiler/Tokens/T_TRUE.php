<?php
namespace App\Service\Compiler\Tokens;

class T_TRUE {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "true"){
            return [
                'type' => 'T_TRUE',
                'value' => "TRUE"
            ];
        }

        return false;
    }

}