<?php
namespace App\Service\Compiler\Tokens;

class T_SCRIPTMAIN {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 11));

        if ($char == "scriptmain "){
            return [
                'type' => 'T_SCRIPTMAIN',
                'value' => "scriptmain"
            ];
        }

        return false;
    }

}