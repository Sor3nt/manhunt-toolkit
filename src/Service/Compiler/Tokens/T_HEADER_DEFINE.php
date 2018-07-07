<?php
namespace App\Service\Compiler\Tokens;

class T_HEADER_DEFINE {

    static public function match( $input, $current ){


        $line = substr($input, $current);

        $offset = 0;
        $value = "";

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);

            if ($char !== " " ){
                $value .= $char;
            }else{


                if ($value !== "" &&  substr($line, $offset + 1, 1) == ":" && substr($line, $offset + 2, 1) == " "){

                    return [
                        'type' => 'T_HEADER_DEFINE',
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