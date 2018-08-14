<?php
namespace App\Service\Compiler\Emitter\Types;

use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;

class T_HEADER_CONSTANT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $constantsDefault = ManhuntDefault::$constants;
        $constants = Manhunt2::$constants;
        if (GAME == "mh1") $constants = Manhunt::$constants;

        if (isset($constantsDefault[$node['value']])) {
            $mapped = $constantsDefault[$node['value']];

        }else if (isset($constants[$node['value']])){
            $mapped = $constants[$node['value']];
        }else{
            throw new \Exception('Constant not found');
        }

        return [
            $getLine('12000000'),
            $getLine('01000000'),
            $getLine($mapped['offset'])
        ];
    }

}