<?php
namespace App\Service\Compiler\Emitter\Types;

class T_HEADER_LEVEL_VAR_STATE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        if (!isset($node['target'])){

            $mapped = $data['combinedVariables'][$node['value']];


            return [
                $getLine('1b000000'),
                $getLine($mapped['offset']),
                $getLine('04000000'),
                $getLine('01000000')
            ];
        }else{
            $variableType = $data['types'][$node['target']];

            $mapped = $variableType[ strtolower($node['value']) ];

        }



        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset'])
        ];
    }

}