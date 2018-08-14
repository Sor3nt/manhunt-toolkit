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
                    $funtionsDefault = ManhuntDefault::$functions;
                    $funtions = Manhunt2::$functions;
                    if (GAME == "mh1") $funtions = Manhunt::$functions;

                    if (isset($funtionsDefault[ strtolower($value) ])) {
                        return [
                            'type' => 'T_FUNCTION',
                            'value' => $value
                        ];

                    }else if (isset($funtions[ strtolower($value) ])) {
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