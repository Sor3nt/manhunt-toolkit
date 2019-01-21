<?php
namespace App\Service\Compiler\Emitter;


use App\Service\Helper;

class T_STRING {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

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

        $result = [
            $getLine('21000000'),
            $getLine('04000000'),
            $getLine('01000000'),

            $getLine($offset),

            $isProcedure || $isCustomFunction ?
                $getLine('10000000') :
                $getLine('12000000'),
            $isProcedure || $isCustomFunction ?
                $getLine('01000000') :
                $getLine('02000000'),
        ];

        if ($isProcedure == false){
            $result[] = $getLine(Helper::fromIntToHex(
                $value == "__empty__" ? 1 : strlen($value) + 1
            ));
        }

        return $result;
    }

}