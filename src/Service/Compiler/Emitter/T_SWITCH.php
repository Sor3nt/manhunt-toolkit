<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_SWITCH {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = sprintf('[T_SWITCH] map: ');

        $code = [];

        //evaluate the switch variable
        Evaluate::emit($node['switch'], $code, $emitter, $debugMsg);

        $forceLineNumber = end($code)->lineNumber + 1;

        $calc = self::calculate( end($code)->lineNumber, $node, $emitter);

        $casesRev = array_reverse($node['cases']);

        foreach ($casesRev as $index => $case) {

            $code[] = $getLine('24000000', $forceLineNumber, $debugMsg . ' case');
            $forceLineNumber = false;

            $code[] = $getLine('01000000', false, $debugMsg . ' case');
            $code[] = $getLine( self::toIndex($case['index'], $data, $node['switch']), false, $debugMsg . ' case' );

            Evaluate::setOperation(Token::T_IS_EQUAL, $code, $getLine);

            $code[] = $getLine(Helper::fromIntToHex( $calc['cases'][$index] ), false, $debugMsg . ' case');

        }

        Evaluate::goto($calc['end'], $code, $getLine);

        foreach ($casesRev as $case) {

            foreach ($case['body'] as $bodyNode) {

                Evaluate::emit($bodyNode, $code, $emitter, '[T_SWITCH] map: ');
            }

            Evaluate::goto($calc['end'], $code, $getLine);

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
                foreach ($emitter($bodyNode, false) as $item){
                    $code[] = $item;
                }
            }

            $line += count($code) + 2;
        }

        $calc['end'] = $line * 4;

        return $calc;
    }

    static public function toIndex($node, $data, $switchVar){

        switch ($node['type']){
            case Token::T_VARIABLE:
                $mapping = T_VARIABLE::getMapping($switchVar, $data);
                if (isset($data['types'][ $mapping['type'] ])){

                    $mapping = $data['types'][ $mapping['type'] ];
                    $mapping = $mapping[ strtolower($node['value']) ];

                }else{
                    $mapping = T_VARIABLE::getMapping($node, $data);
                }

                return $mapping['offset'];

                break;
            case Token::T_BOOLEAN:
            case Token::T_INT:

                //just a hack: todo, boolean transform failed
                if ($node['value'] == "true") $node['value'] = 1;
                if ($node['value'] == "false") $node['value'] = 0;

                return Helper::fromIntToHex($node['value']);
                break;
            default:
                throw new \Exception('T_SWITCH: can not convert index from ' . $node['type']);
                break;
        }
    }

}