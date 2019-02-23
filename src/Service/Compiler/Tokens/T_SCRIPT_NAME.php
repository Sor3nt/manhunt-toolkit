<?php
namespace App\Service\Compiler\Tokens;

class T_SCRIPT_NAME {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current - 7, 7));
        $prevChar = strtolower(substr($input, $current - 1, 1));
        $notChar = strtolower(substr($input, $current - 10, 10));

        //todo: this is just a hack because of the whitespace -.-
        if (
            $notChar == "runscript " ||
            (
                $prevChar == " " &&
                strtolower(substr($input, $current - 11, 10)) == "runscript "
            )
        ){

            return false;
        }



        //todo: this is just a hack because of the whitespace -.-
        $notChar = strtolower(substr($input, $current - 10, 11));
        if (
            $notChar == "callscript " ||
            (
                $prevChar == " " &&
                strtolower(substr($input, $current - 11, 11)) == "callscript "
            )
        ){

            return false;
        }



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