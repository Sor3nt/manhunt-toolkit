<?php
namespace App\Service\Compiler\Tokens;

use App\Service\Compiler\Token;

class T_LEVEL_VAR {

    static public function match( $input, $current, $tokens ){

        $char = strtolower(substr($input, $current, 9));

        $internalCurrent = 9;
        $levelVar = "";
        if ($char == "level_var"){

            $char = substr($input, $current + $internalCurrent, 1);

            while($char != ";") {

                $levelVar .= $char;

                $internalCurrent++;
                $char = substr($input, $current + $internalCurrent, 1);
            }

            return [
                'type' => Token::T_LEVEL_VAR,
                'value' => "level_var" . $levelVar
            ];
        }

        return false;
    }

}