<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

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
                    $funtions = array_merge(ManhuntDefault::$functions, Manhunt::$functions, Manhunt2::$functions);

                    if (isset($funtions[ strtolower($value) ])) {
                        return [
                            'type' => 'T_FUNCTION',
                            'value' => strtolower($value)
                        ];

                    }else if($value == "''"){
                        return [
                            'type' => 'T_STRING',
                            'value' => "''"
                        ];
                    }else{
                        return [
                            'type' => 'T_VARIABLE',
                            'value' => strtolower($value)
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