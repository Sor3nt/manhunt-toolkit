<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE_TYPE {

    static public function match( $input, $current ){




        if ($current <= 2){
            return false;
        }

        $beforeChar = trim(substr($input, $current - 2, 2));
        if ($beforeChar == ":"){

            $value = "";
            while($current < strlen($input)) {
                $char = substr($input, $current, 1);

                if ($char === ";"){
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

        return false;
    }

}