<?php
namespace App\Service\Compiler\Emitter;



use App\Service\Compiler\Evaluate;
use App\Service\Helper;

class T_INT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (int) $node['value'];

        if ($value < 0) $value = $value * -1;

        $code = [];

        Evaluate::readIndex(
            $value,
            $code,
            $getLine
        );


        //todo negate hier her umziehen

        return $code;
    }

}