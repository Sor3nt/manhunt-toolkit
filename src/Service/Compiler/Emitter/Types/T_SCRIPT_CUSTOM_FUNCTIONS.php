<?php
namespace App\Service\Compiler\Emitter\Types;

use App\Service\Helper;

class T_SCRIPT_CUSTOM_FUNCTIONS {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $offset = $data['customData']['customFunctions'][strtolower($node['value'])];

        return [
            $getLine(substr(Helper::fromIntToHex($offset),0, 8)),
        ];
    }

}