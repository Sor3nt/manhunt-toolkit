<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE_SECTION_CONST {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 6));

        if ($char == "const "){
            return [
                'type' => 'T_DEFINE_SECTION_CONST',
                'value' => "const"
            ];
        }



        return false;
    }

}