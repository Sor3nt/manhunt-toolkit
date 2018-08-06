<?php
namespace App\Service\Compiler\Emitter\Types;

class T_SCRIPT_INTEGER {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $offset = $data['variables'][$node['value']]['offset'];

        return [
            $getLine('13000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine($offset)
        ];
    }

}