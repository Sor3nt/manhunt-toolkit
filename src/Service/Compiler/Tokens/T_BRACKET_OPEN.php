<?php
namespace App\Service\Compiler\Tokens;

class T_BRACKET_OPEN {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);

        if ($char == "("){
            return [
                'type' => 'T_BRACKET_OPEN',
                'value' => "("
            ];
        }

        return false;
    }

}