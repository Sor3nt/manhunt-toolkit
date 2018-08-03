<?php
namespace App\Service\Compiler\Tokens;

class T_NIL {

    static public function match( $input, $current, $tokens ){

        $chars = strtolower(substr($input, $current - 1, 5));

        if ($chars == " nil " || $chars == " nil)"|| $chars == " nil,"){
            return [
                'type' => 'T_NIL',
                'value' => "nil"
            ];
        }

        return false;
    }

}