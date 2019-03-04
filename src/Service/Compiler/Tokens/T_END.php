<?php
namespace App\Service\Compiler\Tokens;


use App\Service\Compiler\Token;
use App\Service\Helper;

class T_END {

    static public function match( $input, $current, $tokens ){


        $chars = strtolower(substr($input, $current, 3));

        if ($chars == "end"){
            $lastChar = substr($input, $current + 3, 1);

            if ($lastChar == ";"){
                return [
                    'type' => Helper::findOpenContainerByEnd($tokens),
//                    'type' => 'T_END',
                    'value' => "end;"
                ];

            }else if ($lastChar == " "){
                return [
                    'type' => Token::T_END_ELSE,
                    'value' => "end"
                ];
            }else if ($lastChar == "."){

                return [
                    'type' => Token::T_END_CODE,
                    'value' => "end."
                ];
            }
        }

        return false;

    }

}