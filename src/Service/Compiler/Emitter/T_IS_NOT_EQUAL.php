<?php
namespace App\Service\Compiler\Emitter;

class T_IS_NOT_EQUAL {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        return [
            $getLine('40000000', false, '[T_IS_NOT_EQUAL] map')
        ];


    }

}