<?php
namespace App\Service\Compiler\Emitter\Types;

class T_SCRIPT_ENTITYPTR {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['variables'][$node['value']];

        return [
            $getLine('13000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine($mapped['offset'])
        ];
    }

}