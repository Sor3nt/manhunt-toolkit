<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE {

    static public function match( $input, $current ){

        $char = substr($input, $current, 1);
        $nextChar = substr($input, $current + 1, 1);

        //take sure its not an assignment
        if ($char == ":" && $nextChar != "="){
            return [
                'type' => 'T_DEFINE',
                'value' => ":"
            ];
        }

        return false;
    }

}