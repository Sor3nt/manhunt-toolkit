<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Bytecode\Helper;

class T_SCRIPT_BOOLEAN {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){


        if ($data['calculateLineNumber']){
            $offset = $data['variables'][$node['value']]['offset'];
        }else{
            $offset = '12345678';
        }

        return [
            $getLine('14000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine($offset),


        ];
    }

}