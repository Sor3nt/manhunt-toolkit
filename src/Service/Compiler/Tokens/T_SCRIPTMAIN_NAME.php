<?php
namespace App\Service\Compiler\Tokens;

class T_SCRIPTMAIN_NAME {

    static public function match( $input, $current, $tokens ){

        $beforeChar = strtolower(trim(substr($input, $current - 11, 10)));

        if ($beforeChar == "scriptmain"){

            $value = "";
            while($current < strlen($input)) {
                $char = substr($input, $current, 1);

                if ($char === ";"){
                    return [
                        'type' => 'T_SCRIPTMAIN_NAME',
                        'value' => $value
                    ];
                }else{
                    $value .= $char;
                }

                $current++;
            }

            throw new \Exception('T_SCRIPTMAIN_NAME: Invalid Code');
        }


        return false;
    }

}