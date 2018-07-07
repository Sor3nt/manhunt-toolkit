<?php
namespace App\Service\Compiler\Tokens;

class T_END {

    static public function match( $input, $current ){


        $chars = strtolower(substr($input, $current, 3));

        if ($chars == "end"){
            $lastChar = substr($input, $current + 3, 1);

            if ($lastChar == ";"){
                return [
                    'type' => 'T_END',
                    'value' => "end;"
                ];

            }else if ($lastChar == ""){
                return [
                    'type' => 'T_END_ELSE',
                    'value' => "end"
                ];
            }else if ($lastChar == "."){

                return [
                    'type' => 'T_END_CODE',
                    'value' => "end."
                ];
            }
        }

        return false;

    }

}