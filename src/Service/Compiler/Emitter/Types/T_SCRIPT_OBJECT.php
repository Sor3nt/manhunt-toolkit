<?php
namespace App\Service\Compiler\Emitter\Types;


use App\Service\Compiler\Evaluate;

class T_SCRIPT_OBJECT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $mapped = Evaluate::getObjectToAttributeSplit($node['value'], $data);

        $code = [];

        if ($mapped['offset'] == $mapped['object']['offset']) {

            $code[] = $getLine('22000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine($mapped['object']['offset']);


            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $code[] = $getLine('0f000000');
            $code[] = $getLine('02000000');

            $code[] = $getLine('18000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('02000000');
        }else{

            $code[] = $getLine('22000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine($mapped['object']['offset']);

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');


            $code[] = $getLine('0f000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('32000000');
            $code[] = $getLine('01000000');

            $code[] = $getLine($mapped['offset']);

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine('0f000000');
            $code[] = $getLine('02000000');
            $code[] = $getLine('18000000');
            $code[] = $getLine('01000000');

            $code[] = $getLine($mapped['offset']);

            $code[] = $getLine('02000000');
        }

        return $code;
    }

}