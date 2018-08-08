<?php
namespace App\Service\Compiler\Emitter\Types;

use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_LEVEL_VAR_BOOLEAN {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['variables'][ $node['value'] ];

        return [
            $getLine('1b000000'),
            $getLine($mapped['offset']),
            $getLine('04000000'),
            $getLine('01000000')
        ];
    }

}