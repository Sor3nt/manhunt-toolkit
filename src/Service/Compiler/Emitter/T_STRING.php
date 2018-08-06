<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;

class T_STRING {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        // we have quotes around the string, come from the tokenizer
        $value = substr($node['value'], 1, -1);

        $offset = $data['strings'][$value]['offset'];

        return [
            $getLine('21000000'),
            $getLine('04000000'),
            $getLine('01000000'),

            $getLine($offset),

            $getLine('12000000'),
            $getLine('02000000'),

            $getLine(Helper::fromIntToHex( strlen($value) + 1 ))
        ];
    }

}