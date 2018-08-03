<?php
namespace App\Service\Compiler\Tokens;

class T_OR {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 4));

        if ($chars == " or "){
            return [
                'type' => 'T_OR',
                'value' => "or"
            ];
        }

        return false;
    }

}