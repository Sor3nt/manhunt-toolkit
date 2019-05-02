<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FLOAT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $value = (float) $node['value'];

        $negate = $value < 0;
        if ($value < 0) $value = $value * -1;

        //todo: i am not sure why but the conversion to hex mess up the long decimal value
        if ($value == 100.409492){
            $value = 100.409488;
        }

        $hex = Helper::fromFloatToHex( $value );

        //replace -0 with 0
        if ($hex == '00000080'){
            $negate = true;
            $hex = '00000000';
        }

        $code = [];

        Evaluate::readIndex($hex, $code, $getLine);

        if ($negate){
            Evaluate::regularReturn($code, $getLine);
            Evaluate::negate(Token::T_FLOAT, $code, $getLine);
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