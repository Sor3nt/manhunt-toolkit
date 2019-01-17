<?php
namespace App\Service\Compiler\Emitter\Types;

class T_HEADER_LEVEL_VAR_INTEGER {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['combinedVariables'][$node['value']];

        return [
            $getLine('1b000000'),
            $getLine($mapped['offset']),
            $getLine('04000000'),
            $getLine('01000000')
        ];
    }

}