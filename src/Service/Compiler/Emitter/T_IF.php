<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Token;

class T_IF {


    static public function handleBracketOpen($params, $fullNode, $parentOperator, \Closure $getLine, \Closure $emitter, $isWhile){


        $code = [];

        $current = 0;

        while($current < count($params)){
            $node = $params[$current];

            if ($node['type'] == Token::T_BRACKET_OPEN){
                $result = self::handleBracketOpen($node['params'], $node, $fullNode['operator'],  $getLine, $emitter, $isWhile);
                foreach ($result as $item) {
                    $code[] = $item;
                }

            }else{
                $result = $emitter($node, true, [ 'isWhile' => $isWhile ]);
                foreach ($result as $item) {
                    $code[] = $item;
                }


                if ($fullNode['operator'] != false){
                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');
                    if ($fullNode['operator'] == Token::T_OR) $code[] = $getLine('27000000');
                    if ($fullNode['operator'] == Token::T_AND) $code[] = $getLine('25000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');
                }


                if (
                    isset($fullNode['last']) &&
                    $fullNode['last'] == true &&
                    $parentOperator !== false
                ){

                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');
                    if ($parentOperator == Token::T_OR) $code[] = $getLine('27000000');
                    if ($parentOperator == Token::T_AND) $code[] = $getLine('25000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');

                    // the condition has a operastor
                }


                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
            }

            $current++;
        }

        return $code;
    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data, $isWhile = false ){

        $code = [];

        foreach ($node['cases'] as $index => $case) {

            if (count($case['condition']) == 0){

                if (isset($case['isTrue']) && count($case['isTrue'])){
                    $code[] = $getLine('3c000000'); //else
                }

            }else{
                foreach ($case['condition'] as $condition) {

                    if ($condition['type'] == Token::T_BRACKET_OPEN){
                        $result =  self::handleBracketOpen($condition['params'], $condition, false, $getLine, $emitter, $isWhile);
                        foreach ($result as $item) {
                            $code[] = $item;
                        }
                    }else{
                        die("ehhh hmmm");

                    }
                }

                // the last block has no nested return call, overwrite it
                $nestedReturnCall2 = array_pop($code);
                $nestedReturnCall1 = array_pop($code);

                $nestedReturnCall1->hex = '24000000';
                $nestedReturnCall2->hex = '01000000';

                $code[] = $nestedReturnCall1;
                $code[] = $nestedReturnCall2;

                $code[] = $getLine('00000000');
                $code[] = $getLine('3f000000');
            }

            //pre generate the bytecode (only for calculation)
            $isTrue = [];

            $lastNumber = end($code)->lineNumber;


            foreach ($case['isTrue'] as $entry) {
                $codes = $emitter($entry, false, [ 'isWhile' => $isWhile ]);
                foreach ($codes as $singleLine) {
                    $isTrue[] = $singleLine;
                }
            }

            // calculate the length
            $lastLine = end($code)->lineNumber;
            $endOffset = (($lastLine + count($isTrue)) + 1) * 4;

            if ($isWhile) $endOffset = $endOffset + 8;

            // line offset for the IF end
            // note: we force the line to a new lineNumber since the emitter mess up the index by calculate the offset... todo
            $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1 );

//wenn in isTrue nur ein eintrag ist, muss das gesonders behandelt werden
            // create the bytecode
            foreach ($case['isTrue'] as $entry) {
                $codes = $emitter($entry, true, [ 'isWhile' => $isWhile ]);
                foreach ($codes as $singleLine) {
                    $code[] = $singleLine;
                }
            }


        }

        return $code;
    }

}