<?php
namespace App\Service\Compiler\Tokens;

class T_VARIABLE {

    static public function match( $input, $current ){

        $line = substr($input, $current);

        $offset = 0;
        $value = "";

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);

            if ($char !== ":" && ($char !== " " && $char !== "," && $char !== ")")){
                $value .= $char;
            }else{

                if ($value !== ""){

                    return [
                        'type' => 'T_VARIABLE',
                        'value' => $value
                    ];
                }else{
                    return false;
                }
            }

            $offset++;
        }


        return false;
    }

}