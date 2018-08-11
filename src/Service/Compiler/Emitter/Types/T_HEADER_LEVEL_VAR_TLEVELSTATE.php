<?php
namespace App\Service\Compiler\Emitter\Types;

class T_HEADER_LEVEL_VAR_TLEVELSTATE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        if (!isset($node['target'])){
            throw new \Exception('T_HEADER_LEVEL_VAR_TLEVELSTATE: Target is not found');
        }

        $variableType = $data['types'][$node['target']];

        $mapped = $variableType[ strtolower($node['value']) ];


        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset'])
        ];
    }

}