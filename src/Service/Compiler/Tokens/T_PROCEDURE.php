<?php
namespace App\Service\Compiler\Tokens;

class T_PROCEDURE {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 10));

        if ($char == "procedure "){
            return [
                'type' => 'T_PROCEDURE',
                'value' => "procedure"
            ];
        }

        return false;
    }

}