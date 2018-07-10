<?php
namespace App\Service\Compiler\Tokens;

class T_ELSE {

    static public function match( $input, $current ){

        $chars = strtolower(substr($input, $current, 5));

        if ($chars == "else "){
            return [
                'type' => 'T_ELSE',
                'value' => "else"
            ];
        }

        return false;
    }

}