<?php
namespace App\Service\Compiler\Tokens;

class T_PROCEDURE_NAME {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current - 10, 10));

        if ($char == "procedure "){

            $value = "";
            while($current < strlen($input)) {
                $char = substr($input, $current, 1);

                if ($char === ";" || $char === "("){
                    return [
                        'type' => 'T_PROCEDURE_NAME',
                        'value' => $value
                    ];
                }else{
                    $value .= $char;
                }

                $current++;
            }

            throw new \Exception('T_PROCEDURE_NAME: Invalid Code');

        }

        return false;
    }

}