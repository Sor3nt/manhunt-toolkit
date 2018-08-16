<?php
namespace App\Service\Compiler\Tokens;

class T_FOR {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current , 4));

        if ($chars == "for "){
            return [
                'type' => 'T_FOR',
                'value' => "for"
            ];
        }

        return false;
    }

}