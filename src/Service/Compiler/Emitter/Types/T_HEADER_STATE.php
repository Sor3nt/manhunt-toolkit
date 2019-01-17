<?php
namespace App\Service\Compiler\Emitter\Types;

class T_HEADER_STATE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['combinedVariables'][$node['value']];

        return [
            $getLine('14000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine($mapped['offset'])
        ];
    }

}