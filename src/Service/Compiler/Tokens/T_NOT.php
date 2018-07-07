<?php
namespace App\Service\Compiler\Tokens;

class T_NOT {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "not " || $char == "not("){
            return [
                'type' => 'T_NOT',
                'value' => "NOT"
            ];
        }

        return false;
    }

}