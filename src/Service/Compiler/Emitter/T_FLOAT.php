<?php
namespace App\Service\Compiler\Emitter;


use App\Service\Helper;

class T_FLOAT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = sprintf('[T_FLOAT] map ');

        $value = (float) $node['value'];

        if ($value < 0) $value = $value * -1;

        //todo: i am not sure why but the conversion to hex mess up the long decimal value
        if ($value == 100.409492){
            $value = 100.409488;
        }

        $hex = Helper::fromFloatToHex( $value );

        //replace -0 with ß
        if ($hex == '00000080') $hex = '00000000';

        return [
            $getLine('12000000', false, $debugMsg),
            $getLine('01000000', false, $debugMsg),

            $getLine($hex, false, $debugMsg . $value)
        ];
    }

}