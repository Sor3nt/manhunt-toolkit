<?php
namespace App\Service\Compiler\Emitter\Types;

use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_CONSTANT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $constants = Manhunt2::$constants;
        if (GAME == "mh1") $constants = Manhunt::$constants;

        $mapped = $constants[$node['value']];

        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset'])
        ];
    }

}