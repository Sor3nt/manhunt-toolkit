<?php
namespace App\Service\Compiler\Emitter\Types;

class T_SCRIPT_CONSTANT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = $data['const'][$node['value']];

        return $emitter($mapped);

    }

}