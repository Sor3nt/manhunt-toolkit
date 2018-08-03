<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\FunctionMap\Manhunt2;

class T_VARIABLE {

    static public function match( $input, $current, $tokens ){

        $line = substr($input, $current);

        $offset = 0;
        $value = "";

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);

            if ($char !== ":" && ($char !== " " && $char !== "," && $char !== ")")){
                $value .= $char;
            }else{

                if ($value !== ""){

                    if (isset(Manhunt2::$functions[ strtolower($value) ])) {
                        return [
                            'type' => 'T_FUNCTION',
                            'value' => $value
                        ];

                    }else{
                        return [
                            'type' => 'T_VARIABLE',
                            'value' => $value
                        ];

                    }

                }else{
                    return false;
                }
            }

            $offset++;
        }


        return false;
    }

}