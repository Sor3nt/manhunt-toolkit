<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FOR {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $debugMsg = sprintf('[T_FOR] map ');

        $incrementVarMapped = $data['variables'][$node['variable']['value']];

        $code = [];

        Evaluate::readIndex($node['start']['value'], $code, $getLine);

        Evaluate::toNumeric($incrementVarMapped, $code, $getLine);


        $firstLineNumber = end($code)->lineNumber;

        Evaluate::emit($node['end'], $code, $emitter, $debugMsg);

        Evaluate::storeInteger($incrementVarMapped, $code, $getLine);

        $code[] = $getLine('23000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('02000000', false, $debugMsg);
        $code[] = $getLine('41000000', false, $debugMsg);


        $startLineNumber = end($code)->lineNumber;

//        if ($data['game'] == MHT::GAME_MANHUNT_2){
            $startLineNumber += 3;
//        }

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
        $gameOffset = $data['game'] == MHT::GAME_MANHUNT ? 9 : 6;
        $endOffset = ($lastNumber + count($isTrue) + $gameOffset ) * 4;

        // line offset for the IF end
        $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1, false, $debugMsg . '(end line)' );

        Evaluate::emitBlock($node['params'], $code, $emitter, $debugMsg . ' params ');

        if (
            $data['game'] == MHT::GAME_MANHUNT
        ){
            Evaluate::regularReturn($code, $getLine);
            $code[] = $getLine('7d000000', false, $debugMsg . 'mh1 boolean special');
        }

        if ($data['game'] == MHT::GAME_MANHUNT) {
            $code[] = $getLine('2d000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);

            if ($node['end']['type'] == Token::T_FUNCTION){

                $code[] = $getLine('10000000', false, $debugMsg);
            }else{
                $code[] = $getLine('00000000', false, $debugMsg);
            }

            Evaluate::goto($firstLineNumber * 4, $code, $getLine);

            $code[] = $getLine('2e000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);

        }else{
            $code[] = $getLine('2f000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);

            if ($node['end']['type'] == Token::T_FUNCTION){

                $code[] = $getLine('10000000', false, $debugMsg);
            }else{
                $code[] = $getLine('00000000', false, $debugMsg);
            }

            Evaluate::goto($firstLineNumber * 4, $code, $getLine);

            $code[] = $getLine('30000000', false, $debugMsg);
            $code[] = $getLine('04000000', false, $debugMsg);
        }

        if ($node['end']['type'] == Token::T_FUNCTION){
            $code[] = $getLine('10000000', false, $debugMsg . '(function return)');
        }else{
            $code[] = $getLine('00000000', false, $debugMsg . '(NO function return)');
        }


        return $code;
    }

}