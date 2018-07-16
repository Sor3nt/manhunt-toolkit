<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_IS_EQUAL {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        return [
            $getLine('3f000000')
        ];
    }

}