<?php
namespace App\Service\Compiler\Tokens;

class T_THEN {

    static public function match( $input, $current ){

        $chars = strtolower(substr($input, $current - 1, 6));

        if ($chars == " then "){
            return [
                'type' => 'T_THEN',
                'value' => "then"
            ];

        }

        return false;
    }

}