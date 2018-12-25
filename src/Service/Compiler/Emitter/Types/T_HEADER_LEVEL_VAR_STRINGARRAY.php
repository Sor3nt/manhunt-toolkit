<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Helper;

class T_HEADER_LEVEL_VAR_STRINGARRAY{

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['variables'][ $node['value'] ];

        return [
            $getLine('1c000000'),
            $getLine('01000000'),
            $getLine($mapped['offset']),
            $getLine('1e000000'),


            $getLine('12000000'),
            $getLine('02000000'),

            $getLine(Helper::fromIntToHex( $mapped['size']  ))
        ];
    }

}