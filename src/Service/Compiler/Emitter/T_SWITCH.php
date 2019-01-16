<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Token;
use App\Service\Helper;

class T_SWITCH {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        //evaluate the switch variable
        $result = $emitter($node['switch']);
        foreach ($result as $item) {
            $code[] = $item;
        }

        $forceLineNumber = end($code)->lineNumber + 1;

        $calc = self::calculate( end($code)->lineNumber, $node, $emitter);

        $casesRev = array_reverse($node['cases']);

        foreach ($casesRev as $index => $case) {

            $code[] = $getLine('24000000', $forceLineNumber);
            $forceLineNumber = false;

            $code[] = $getLine('01000000');
            $code[] = $getLine( self::toIndex($case['index'], $data, $node['switch']) );

            $code[] = $getLine('3f000000');
            $code[] = $getLine(Helper::fromIntToHex( $calc['cases'][$index] ));

        }

        $code[] = $getLine('3c000000');
        $code[] = $getLine(Helper::fromIntToHex( $calc['end'] ));

        foreach ($casesRev as $case) {

            foreach ($case['body'] as $bodyNode) {
                $result = $emitter($bodyNode);
                foreach ($result as $item) {
                    $code[] = $item;
                }
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

        $line += (count($node['cases']) * 5) + 2;

        foreach ($casesRev as $case) {
            $calc['cases'][] = $line * 4;

            $code = [];
            foreach ($case['body'] as $bodyNode) {
                foreach ($emitter($bodyNode, false) as $item) $code[] = $item;
            }

            $line += count($code) + 2;
        }

        $calc['end'] = $line * 4;

        return $calc;
    }

    static public function toIndex($node, $data, $switchVar){

        switch ($node['type']){
            case Token::T_VARIABLE:
                $mapping = T_VARIABLE::getMapping($switchVar, null, $data);
                if (isset($data['types'][ $mapping['type'] ])){

                    $mapping = $data['types'][ $mapping['type'] ];
                    $mapping = $mapping[ strtolower($node['value']) ];

                }else{
                    $mapping = T_VARIABLE::getMapping($node, null, $data);
                }

                return $mapping['offset'];

                break;
            case Token::T_INT:
                return Helper::fromIntToHex($node['value']);
                break;
            case Token::T_FALSE:
                return Helper::fromIntToHex(0);
                break;
            case Token::T_TRUE:
                return Helper::fromIntToHex(1);
                break;
            default:
                throw new \Exception('T_SWITCH: can not convert index from ' . $node['type']);
                break;
        }
    }

}