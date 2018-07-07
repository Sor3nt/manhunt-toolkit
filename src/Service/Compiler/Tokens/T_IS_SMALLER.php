<?php
namespace App\Service\Compiler\Tokens;

class T_IS_SMALLER {

    static public function match( $input, $current ){

        $char = substr($input, $current, 1);

        if ($char == "<"){
            return [
                'type' => 'T_IS_SMALLER',
                'value' => "<"
            ];
        }

        return false;
    }

}