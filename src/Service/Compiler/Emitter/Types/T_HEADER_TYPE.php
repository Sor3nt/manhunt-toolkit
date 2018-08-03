<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_TYPE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = false;

        foreach ($data['types'] as $type) {
            foreach ($type as $name => $map) {

                if ($name == strtolower($node['value'])){

                    $mapped = $map;
                }
            }
        }


        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset']),
        ];
    }

}