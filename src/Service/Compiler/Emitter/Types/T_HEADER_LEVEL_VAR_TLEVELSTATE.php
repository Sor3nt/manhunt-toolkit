<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_LEVEL_VAR_TLEVELSTATE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $variableType = $data['types'][$node['target']];

        if ($data['calculateLineNumber']){
            $mapped = $variableType[$node['value']];
        }else{
            $mapped = [
                'offset' => '12345678'
            ];
        }

        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset']),
        ];
    }

}