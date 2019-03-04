<?php
namespace App\Service\Compiler\Emitter;



use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_INT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (int) $node['value'];

        $negate = $value < 0;
        if ($value < 0){
            $value = $value * -1;
        }

        $code = [];

        Evaluate::readIndex(
            $value,
            $code,
            $getLine
        );

        if ($negate){
            Evaluate::negate(Token::T_INT, $code,$getLine);
        }

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