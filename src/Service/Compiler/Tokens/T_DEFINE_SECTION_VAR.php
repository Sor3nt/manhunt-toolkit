<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE_SECTION_VAR {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 4));

        if ($char == "var "){
            return [
                'type' => 'T_DEFINE_SECTION_VAR',
                'value' => "var"
            ];
        }



        return false;
    }

}