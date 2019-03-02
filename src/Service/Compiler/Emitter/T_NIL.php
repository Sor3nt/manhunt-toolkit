<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Helper;

class T_NIL {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        //todo: is im grunde nen boolean ...
        $code = [];

        Evaluate::readIndex(
            0,
            $code,
            $getLine
        );


        return $code;

    }

}