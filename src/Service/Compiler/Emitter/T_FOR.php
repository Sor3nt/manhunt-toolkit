<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FOR {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data, $isWhile = false ){
        $debugMsg = sprintf('[T_FOR] map ');

        $incrementVarMapped = $data['variables'][$node['variable']['value']];

        $code = [];

        // assign value to var
        $code[] = $getLine('12000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);

        if ($node['start']['type'] == Token::T_INT) {
            $code[] = $getLine(Helper::fromIntToHex($node['start']['value']), false, $debugMsg . ' int value ' . $node['start']['value']);
        }else{
            throw new \Exception('T_FOR: Unable to handle type');
        }

        $code[] = $getLine('15000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($incrementVarMapped['offset'], false, $debugMsg . 'offset');
        $code[] = $getLine('01000000', false, $debugMsg);

        $firstLineNumber = end($code)->lineNumber;

        foreach ($emitter($node['end']) as $item) {
            $item->debug = $debugMsg . ' ' . $item->debug;
            $code[] = $item;
        }

        $code[] = $getLine('13000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);
        $code[] = $getLine($incrementVarMapped['offset'], false, $debugMsg . 'offset');


        $code[] = $getLine('23000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('41000000', false, $debugMsg);

        $startLineNumber = end($code)->lineNumber + 3;
        $code[] = $getLine(Helper::fromIntToHex($startLineNumber * 4), false, $debugMsg . ' (start line)');

        $code[] = $getLine('3c000000', false, $debugMsg);

        $isTrue = [];

        $lastNumber = end($code)->lineNumber;

        //pre generate the bytecode (only for calculation)
        foreach ($node['params'] as $entry) {
            foreach ($emitter($entry, false) as $singleLine) {
                $singleLine->debug = $debugMsg . ' isTrue ' . $singleLine->debug;

                $isTrue[] = $singleLine;
            }
        }

        $endOffset = ($lastNumber + count($isTrue) + 6 ) * 4;

        // line offset for the IF end
        $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1, false, $debugMsg . '(end line)' );

        foreach ($node['params'] as $entry) {
            foreach ($emitter($entry) as $singleLine){
                $singleLine->debug = $debugMsg . ' params ' . $singleLine->debug;
                $code[] = $singleLine;
            }
        }

        //i dont know...
        $code[] = $getLine('2f000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);

        if ($node['end']['type'] == Token::T_FUNCTION){

            $code[] = $getLine('10000000', false, $debugMsg);
        }else{
            $code[] = $getLine('00000000', false, $debugMsg);
        }

        $code[] = $getLine('3c000000', false, $debugMsg);
        $code[] = $getLine(Helper::fromIntToHex($firstLineNumber * 4), false, $debugMsg . '(start line)');

        $code[] = $getLine('30000000', false, $debugMsg);
        $code[] = $getLine('04000000', false, $debugMsg);

        if ($node['end']['type'] == Token::T_FUNCTION){
            $code[] = $getLine('10000000', false, $debugMsg . '(function return)');
        }else{
            $code[] = $getLine('00000000', false, $debugMsg . '(NO function return)');
        }

        return $code;
    }

}