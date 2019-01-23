<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE_SECTION_ARG {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "arg "){
            return [
                'type' => 'T_DEFINE_SECTION_ARG',
                'value' => "arg"
            ];
        }



        return false;
    }

}