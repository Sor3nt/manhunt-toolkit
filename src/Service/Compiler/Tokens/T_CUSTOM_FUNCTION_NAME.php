<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_CUSTOM_FUNCTION_NAME {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current - 9, 9));

        if ($char == "function "){

            $value = "";
            while($current < strlen($input)) {
                $char = substr($input, $current, 1);

                if ($char === ";" || $char === "(" || $char === " "){
                    return [
                        'type' => Token::T_CUSTOM_FUNCTION_NAME,
                        'value' => $value
                    ];
                }else{
                    $value .= $char;
                }

                $current++;
            }

            throw new \Exception('T_CUSTOM_FUNCTION_NAME: Invalid Code');

        }

        return false;
    }

}