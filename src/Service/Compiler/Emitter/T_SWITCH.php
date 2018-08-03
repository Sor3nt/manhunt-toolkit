<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_SWITCH {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        //evaluate the switch variable
        $result = $emitter($node['switch']);
        foreach ($result as $item) {
            $code[] = $item;
        }


        $calc = self::calculate( end($code)->lineNumber, $node, $emitter);

        $casesRev = array_reverse($node['cases']);

        foreach ($casesRev as $index => $case) {

            $code[] = $getLine('24000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine( self::toIndex($case['index']) );

            $code[] = $getLine('3f000000');
            $code[] = $getLine(Helper::fromIntToHex( $calc['cases'][$index] ));

        }

        $code[] = $getLine('3c000000');
        $code[] = $getLine(Helper::fromIntToHex( $calc['end'] ));

        foreach ($casesRev as $case) {

            $result = $emitter($case['body']);
            foreach ($result as $item) {
                $code[] = $item;
            }

            $code[] = $getLine('3c000000');
            $code[] = $getLine(Helper::fromIntToHex( $calc['end'] ));

        }

        return $code;
    }

    static public function calculate($line, $node, \Closure $emitter ){


        $calc = [
            'cases' => [],
            'end' => []
        ];


        $casesRev = array_reverse($node['cases']);

        foreach ($casesRev as $case) {
            $line += 5;
        }

        $line += 2;

        foreach ($casesRev as $case) {
            $calc['cases'][] = $line;
            $result = $emitter($case['body'], false);

            $line += count($result);

            $line += 2;

        }

        $calc['end'] = $line;

        return $calc;
    }

    static public function toIndex($node){
        switch ($node['type']){

            case Token::T_INT:
                return Helper::fromIntToHex($node['value']);
                break;
            default:
                throw new \Exception('T_SWITCH: can not convert index from ' . $node['type']);
                break;
        }
    }

}