<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Emitter\T_VARIABLE;

class T_FUNCTION {

    static public function match( $input, $current, $tokens ){

        $line = substr($input, $current);

        $offset = 0;

        $value = "";

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);

            if( preg_match("/[^a-zA-Z0-9_]+/", $char) == false){
                $value .= $char;
            }else{

                if ($char == ";" || $char == "("){
                    return [
                        'type' => 'T_FUNCTION',
                        'value' => trim($value)
                    ];
                }else{
                    return false;
                }
            }

            $offset++;
        }


        return false;
    }

}