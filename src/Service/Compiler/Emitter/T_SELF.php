<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;

class T_SELF {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        Evaluate::readIndex(
            '49000000',
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

//        var_dump($data);
//        exit;

        return $code;

    }

}