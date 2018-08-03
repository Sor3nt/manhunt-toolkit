<?php
namespace App\Service\Compiler\Tokens;

class T_ASSIGN {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 2);

        if ($char == ":="){
            return [
                'type' => 'T_ASSIGN',
                'value' => ":="
            ];
        }

        return false;
    }

}