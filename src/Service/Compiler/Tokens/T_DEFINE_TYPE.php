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

                    if ($char === ";" || $char === ")") {
                        return [
                            'type' => 'T_DEFINE_TYPE',
                            'value' => $value
                        ];
                    }else if ($char === "]"){

                        //Searchables : array [1..9] of Searchable;
                        if (substr($value, 0, 5) == "array"){
                            $remain = trim(substr($value, 5));
                            $remain = explode('[', $remain)[1];

                            list($from, $to) = explode('..', $remain);

                            $remain = substr($input, $current + 1);
                            $remain = trim(explode("of", $remain)[1]);
                            $ofVar = explode(";", $remain)[0];

                            return [
                                'type' => Token::T_ARRAY,
                                'value' => $value . ']' . substr($input, $current + 1),
                                'from' => $from,
                                'to' => $to,
                                'ofVar' => $ofVar

                            ];
                        }else{
                            $value .= $char;
                        }
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