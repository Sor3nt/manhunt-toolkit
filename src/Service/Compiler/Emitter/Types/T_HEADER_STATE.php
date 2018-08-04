<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_STATE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        $mapped = $data['variables'][$node['value']];

        return [
            $getLine('14000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine($mapped['offset']),
        ];
//
//        $mapped = false;
//
//        foreach ($data['types'] as $type) {
//            foreach ($type as $name => $map) {
//
//                if ($name == strtolower($node['value'])){
//
//                    $mapped = $map;
//                }
//            }
//        }
//
//
//        return [
//            $getLine('12000000'),
//            $getLine('01000000'),
//            $getLine($mapped['offset']),
//        ];
    }

}