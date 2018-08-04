<?php
namespace App\Service\Compiler\Tokens;

class T_CASE {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current, 5));

        if ($chars == "case "){
            return [
                'type' => 'T_CASE',
                'value' => "case"
            ];
        }

        return false;
    }

}