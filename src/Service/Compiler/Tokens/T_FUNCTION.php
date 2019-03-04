<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Emitter\T_VARIABLE;
use App\Service\Compiler\Token;

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
                    $val = strtolower(trim($value));

                    if ($val == "nil"){
                        return [
                            'type' => Token::T_NIL,
                            'value' => 'nil'
                        ];

                    }

                    return [
                        'type' => Token::T_FUNCTION,
                        'value' => $val
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