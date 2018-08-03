<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_DEFINE_TYPE {

    static public function match( $input, $current, $tokens ){

        if ($current <= 2){
            return false;
        }

        $beforeChar = trim(substr($input, $current - 2, 2));
        if ($beforeChar == ":"){

            if (self::getCurrentContainer($tokens) == Token::T_DEFINE_SECTION_VAR){
                $value = "";
                while($current < strlen($input)) {
                    $char = substr($input, $current, 1);

                    if ($char === ";" || $char === ")"){
                        return [
                            'type' => 'T_DEFINE_TYPE',
                            'value' => $value
                        ];
                    }else{
                        $value .= $char;
                    }

                    $current++;
                }

                throw new \Exception('T_DEFINE_TYPE: Invalid Code');
            }

        }

        return false;
    }

    static public function getCurrentContainer($tokens){
        $tokens = array_reverse($tokens);

        foreach ($tokens as $token) {
            if ($token['type'] == Token::T_OF) return Token::T_OF;
            if ($token['type'] == Token::T_DEFINE_SECTION_VAR) return Token::T_DEFINE_SECTION_VAR;
        }

        return false;
    }

}