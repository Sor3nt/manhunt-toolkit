<?php
namespace App\Service\Compiler\Tokens;

class T_SCRIPT_NAME {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current - 7, 7));

        if ($char == "script "){

            $value = "";
            while($current < strlen($input)) {
                $char = substr($input, $current, 1);

                if ($char === ";"){
                    return [
                        'type' => 'T_SCRIPT_NAME',
                        'value' => $value
                    ];
                }else{
                    $value .= $char;
                }

                $current++;
            }

            throw new \Exception('T_SCRIPT_NAME: Invalid Code');

        }

        return false;
    }

}