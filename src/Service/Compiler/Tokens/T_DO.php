<?php
namespace App\Service\Compiler\Tokens;

class T_DO {

    static public function match( $input, $current ){

        $chars = strtolower(substr($input, $current - 1, 4));

        if ($chars == " do "){
            $lastChar = substr($chars, 2, 1);
            if ($lastChar == " " || $lastChar == ""){

                return [
                    'type' => 'T_DO',
                    'value' => "do"
                ];
            }


        }

        return false;
    }

}