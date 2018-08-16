<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;

class T_FOR {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data, $isWhile = false ){


        $incrementVarMapped = $data['variables'][$node['variable']['value']];


        $code = [];

        // assign to value to var
        $code[] = $getLine('12000000');

        $code[] = $getLine('01000000');

        if ($node['start']['type'] == Token::T_INT) {
            $code[] = $getLine(Helper::fromIntToHex($node['start']['value']));
        }else{
            throw new \Exception('T_FOR: Unable to handle type');

        }

        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($incrementVarMapped['offset']);
        $code[] = $getLine('01000000');

        $firstLineNumber = end($code)->lineNumber;

        if ($node['end']['type'] == Token::T_INT) {
            $code[] = $getLine('12000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine(Helper::fromIntToHex($node['end']['value']   ));

        }else if ($node['end']['type'] == Token::T_FUNCTION) {

            $codes = $emitter($node['end']);
            foreach ($codes as $singleLine) {
                $code[] = $singleLine;
            }

        }else{
            throw new \Exception('T_FOR: Unable to handle type');
        }


        $code[] = $getLine('13000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($incrementVarMapped['offset']);


        $code[] = $getLine('23000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('41000000');
        $startLineNumber = end($code)->lineNumber + 3;
        $code[] = $getLine(Helper::fromIntToHex($startLineNumber * 4));

        $code[] = $getLine('3c000000');

        $isTrue = [];

        $lastNumber = end($code)->lineNumber;
        //pre generate the bytecode (only for calculation)
        foreach ($node['params'] as $entry) {
            $codes = $emitter($entry, false);
            foreach ($codes as $singleLine) {
                $isTrue[] = $singleLine;
            }
        }

//var_dump($lastNumber, count($isTrue));
//        exit;
        $endOffset = ($lastNumber + count($isTrue) + 6 ) * 4;

        // line offset for the IF end
        $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1 );
//        $code[] = $getLine( count($isTrue) . " ", $lastNumber + 1 );

        foreach ($node['params'] as $entry) {
            $codes = $emitter($entry);
            foreach ($codes as $singleLine) {
                $code[] = $singleLine;
            }
        }

//        $code[] = $getLine('debug');

        //i dont know...
        $code[] = $getLine('2f000000');
        $code[] = $getLine('04000000');

        if ($node['end']['type'] == Token::T_FUNCTION){

            $code[] = $getLine('10000000'); // HMMM 0 / 1...
        }else{
            $code[] = $getLine('00000000'); // HMMM 0 / 1...

        }



        $code[] = $getLine('3c000000');
        $code[] = $getLine(Helper::fromIntToHex($firstLineNumber * 4));

        $code[] = $getLine('30000000');
        $code[] = $getLine('04000000');

        if ($node['end']['type'] == Token::T_FUNCTION){

            $code[] = $getLine('10000000'); // HMMM 0 / 1...
        }else{
            $code[] = $getLine('00000000'); // HMMM 0 / 1...

        }

        return $code;
    }

}