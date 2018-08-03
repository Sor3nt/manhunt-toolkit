<?php
namespace App\Service\Compiler\Tokens;

class T_SEPERATOR {

    static public function match( $input, $current, $tokens ){

        $char = substr($input, $current, 1);

        if ($char == ","){
            return [
                'type' => 'T_SEPERATOR',
                'value' => ","
            ];
        }

        return false;
    }

}