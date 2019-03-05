<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
use App\Service\Helper;

class T_STRING {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = sprintf('[T_STRING] map ');
        // we have quotes around the string, come from the tokenizer
        $value = substr($node['value'], 1, -1);

        //hack for empty strings
        if ($value == ""){
            $value = "__empty__";
        }

        if (!isset($data['combinedStrings'][$value])){
            throw new \Exception('T_STRING value not found: ' . $value);
        }

        $offset = $data['combinedStrings'][$value]['offset'];


        $isProcedure = isset($data['customData']['isProcedure']) && $data['customData']['isProcedure'];
        $isCustomFunction = isset($data['customData']['isCustomFunction']) && $data['customData']['isCustomFunction'];

        $code = [];

        Evaluate::fromFineANameforMeTodo([
            'section' => "header",
            'offset' => $offset
        ], $code, $getLine);

        if ($isProcedure || $isCustomFunction){
            Evaluate::regularReturn($code, $getLine);
        }else{

            if ($data['game'] == MHT::GAME_MANHUNT){
                $val = $value == "__empty__" ? 4 : strlen($value) + (4 - strlen($value) % 4);
            }else{
                $val = $value == "__empty__" ? 1 : strlen($value) + 1;

            }

            Evaluate::readStringPosition($val, $code, $getLine);

        }

        return $code;
    }

}