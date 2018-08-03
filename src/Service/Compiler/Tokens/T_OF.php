<?php
namespace App\Service\Compiler\Tokens;

class T_OF {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 4));

        if ($chars == " of "){
            return [
                'type' => 'T_OF',
                'value' => "of"
            ];
        }

        return false;
    }

}