<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Helper;

class T_BOOLEAN {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        Evaluate::readIndex(
            (int) $node['value'],
            $code,
            $getLine
        );


        if (
            isset($data['customData']) &&
            isset($data['customData']['fromFunction']) &&
            $data['customData']['fromFunction']
        ){
            Evaluate::regularReturn($code, $getLine);
        }

        return $code;
    }

}