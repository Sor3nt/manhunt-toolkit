<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;

class T_STRING {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        // we have quotes around the string, come from the tokenizer
        $value = substr($node['value'], 1, -1);

        $value = strlen($value) + 1;

        return [ $getLine(Helper::fromIntToHex( $value ))];
    }

}