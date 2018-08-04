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

        //        $casesRev = array_reverse($node['cases']);

        foreach ($node['cases'] as $index => $case) {

            $code[] = $getLine('24000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine( self::toIndex($case['index'], $data, $node['switch']) );

            $code[] = $getLine('3f000000');
            $code[] = $getLine(Helper::fromIntToHex( $calc['cases'][$index] ));

        }

        $code[] = $getLine('3c000000');
        $code[] = $getLine(Helper::fromIntToHex( $calc['end'] ));

        foreach ($node['cases'] as $case) {

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


//        $casesRev = array_reverse($node['cases']);

        foreach ($node['cases'] as $case) {
            $line += 5;
        }

        $line += 2;

        foreach ($node['cases'] as $case) {
            $calc['cases'][] = $line;


            $code = [];
            foreach ($case['body'] as $bodyNode) {
                $result = $emitter($bodyNode, false);
                foreach ($result as $item) {
                    $code[] = $item;
                }

            }
//            $result = $emitter($case['body'], false);

            $line += count($code);

            $line += 2;

        }

        $calc['end'] = $line;

        return $calc;
    }

    static public function toIndex($node, $data, $switchVar){

        switch ($node['type']){
            case Token::T_VARIABLE:
//                var_dump($switchVar);

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
            default:
                throw new \Exception('T_SWITCH: can not convert index from ' . $node['type']);
                break;
        }
    }

}