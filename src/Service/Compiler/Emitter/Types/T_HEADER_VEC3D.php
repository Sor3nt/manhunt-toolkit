<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Compiler\FunctionMap\Manhunt2;

class T_HEADER_VEC3D {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        if ($data['calculateLineNumber']){
            $mapped = $data['variables'][ $node['value'] ];
        }else{
            $mapped = [
                'offset' => '12345678'
            ];
        }

        return [
            $getLine('21000000'),
            $getLine('04000000'),
            $getLine('01000000'),
            $getLine($mapped['offset']),
        ];
    }

}