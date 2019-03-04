<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_STRING {

    static public function match( $input, $current, $tokens ){

        $firstChar = substr($input, $current, 1);
        $line = substr($input, $current + 1);

        $offset = 0;

        $value = "";

        if ($firstChar !== "'" && $firstChar !== '"') return false;

        while($offset < strlen($line)) {

            $char = substr($line, $offset, 1);

            if ($char !== "'" && $char !== '"'){
                $value .= $char;
            }else{

                if ($value !== ""){

                    return [
                        'type' => Token::T_STRING,
                        'value' => '"' . $value . '"'
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