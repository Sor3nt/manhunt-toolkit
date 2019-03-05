<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FOR {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data, $isWhile = false ){
        $debugMsg = sprintf('[T_FOR] map ');

        $incrementVarMapped = $data['variables'][$node['variable']['value']];

        $code = [];

        Evaluate::readIndex($node['start']['value'], $code, $getLine);

        Evaluate::toNumeric($incrementVarMapped, $code, $getLine);


        $firstLineNumber = end($code)->lineNumber;

        foreach ($emitter($node['end']) as $item) {
            $item->debug = $debugMsg . ' ' . $item->debug;
            $code[] = $item;
        }

        Evaluate::fromFinedANameforMeTodoSecondAgain($incrementVarMapped, $code, $getLine);

        $code[] = $getLine('23000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('41000000', false, $debugMsg);

        $startLineNumber = end($code)->lineNumber + 3;
        $code[] = $getLine(Helper::fromIntToHex($startLineNumber * 4), false, $debugMsg . ' (start line 1)');

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

        //todo: why 9 ? why 6 ?
        if ($data['game'] == MHT::GAME_MANHUNT){
            $endOffset = ($lastNumber + count($isTrue) + 9 ) * 4;
        }else{
            $endOffset = ($lastNumber + count($isTrue) + 6 ) * 4;

        }

        // line offset for the IF end
        $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1, false, $debugMsg . '(end line)' );

        foreach ($node['params'] as $entry) {
            foreach ($emitter($entry) as $singleLine){
                $singleLine->debug = $debugMsg . ' params ' . $singleLine->debug;
                $code[] = $singleLine;
            }
        }

        if (
            //könnte sein das dies innerhalb des params block gemacht werden muss...
            $data['game'] == MHT::GAME_MANHUNT
        ){
            Evaluate::regularReturn($code, $getLine);
            $code[] = $getLine('7d000000', false, $debugMsg . 'mh1 boolean special');
        }


        //i dont know...
        if ($data['game'] == MHT::GAME_MANHUNT) {
            $code[] = $getLine('2d000000', false, $debugMsg);
        }else{
            $code[] = $getLine('2f000000', false, $debugMsg);
        }

        $code[] = $getLine('04000000', false, $debugMsg);

        if ($node['end']['type'] == Token::T_FUNCTION){

            $code[] = $getLine('10000000', false, $debugMsg);
        }else{
            $code[] = $getLine('00000000', false, $debugMsg);
        }

        Evaluate::goto($firstLineNumber * 4, $code, $getLine);

        if ($data['game'] == MHT::GAME_MANHUNT) {
            $code[] = $getLine('2e000000', false, $debugMsg);
        }else{
            $code[] = $getLine('30000000', false, $debugMsg);
        }

        $code[] = $getLine('04000000', false, $debugMsg);

        if ($node['end']['type'] == Token::T_FUNCTION){
            $code[] = $getLine('10000000', false, $debugMsg . '(function return)');
        }else{
            $code[] = $getLine('00000000', false, $debugMsg . '(NO function return)');
        }

        return $code;
    }

}