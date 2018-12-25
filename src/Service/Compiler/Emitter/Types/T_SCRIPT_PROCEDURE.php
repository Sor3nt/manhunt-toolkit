<?php
namespace App\Service\Compiler\Emitter\Types;

use App\Service\Helper;

class T_SCRIPT_PROCEDURE {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $offset = $data['customData']['procedureVars'][$node['value']]['offset'];

        return [
            $getLine('13000000'),
            $getLine('01000000'),
            $getLine('04000000'),
            $getLine(substr(Helper::fromIntToHex($offset),0, 8))
        ];
    }

}