<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_IF {

    static public function handleBracketOpen($params, $fullNode, $parentOperator, \Closure $getLine, \Closure $emitter, $isWhile){
        $debugMsg = "[T_IF] handleBracketOpen ";

        $code = [];

        $current = 0;

        while($current < count($params)){
            $node = $params[$current];

            if ($node['type'] == Token::T_BRACKET_OPEN){
                $result = self::handleBracketOpen($node['params'], $node, $fullNode['operator'],  $getLine, $emitter, $isWhile);
                foreach ($result as $item) {
                    $item->debug = $debugMsg . ' _eval_1_ '. $item->debug;
                    $code[] = $item;
                }

                if($current + 1 != count($params)){
                    Evaluate::regularReturn($code, $getLine);
                }

            }else{

                $result = $emitter($node, true, [ 'isWhile' => $isWhile ]);
                foreach ($result as $item) {
                    $item->debug = $debugMsg . ' _eval_2_ '. $item->debug;
                    $code[] = $item;
                }

                if ($fullNode['operator'] != false){
                    Evaluate::returnCache($code, $getLine);
                    Evaluate::setStatementOperator($fullNode['operator'], $code, $getLine);
                }

                if (
                    isset($fullNode['last']) &&
                    $fullNode['last'] == true &&
                    $parentOperator !== false
                ){
                    Evaluate::returnCache($code, $getLine);
                    Evaluate::setStatementOperator($parentOperator, $code, $getLine);
                }

            }

            $current++;
        }

        return $code;
    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data, $isWhile = false ){
        $debugMsg = "[T_IF] map ";

        $code = [];

        foreach ($node['cases'] as $index => $case) {

            if (count($case['condition']) == 0){

                if (isset($case['isTrue']) && count($case['isTrue'])){
                    $code[] = $getLine('3c000000', false, $debugMsg . 'else (?)'); //else
                }

            }else{

                foreach ($case['condition'] as $conditionIndex => $condition) {

                    if ($condition['type'] == Token::T_BRACKET_OPEN){
                        $result =  self::handleBracketOpen($condition['params'], $condition, false, $getLine, $emitter, $isWhile);
                        foreach ($result as $item) {
                            $item->debug = $debugMsg . ' _eval_3_ '. $item->debug;
                            $code[] = $item;
                        }

                        if($conditionIndex + 1 != count($case['condition'])){
                            Evaluate::regularReturn($code, $getLine);
                        }

                    }else{
                        throw new \Exception('T_IF: Brackets order not valid');
                    }


                }

                $code[] = $getLine('24000000', false, $debugMsg);
                $code[] = $getLine('01000000', false, $debugMsg);
                $code[] = $getLine('00000000', false, $debugMsg);
                $code[] = $getLine('3f000000', false, $debugMsg);
            }

            $code[] = $getLine( 'START_OFFSET', false, $debugMsg . 'offset ' );
            $offsetIndex = count($code) - 1;

            foreach ($case['isTrue'] as $entry) {
                $codes = $emitter($entry, true, [ 'isWhile' => $isWhile ]);
                foreach ($codes as $singleLine) {
                    $singleLine->debug = $debugMsg . ' '. $singleLine->debug;
                    $code[] = $singleLine;
                }
            }


            $endOffset = (end($code)->lineNumber) * 4;

            if (
                $isWhile ||
                (
                    isset($case['next']) &&
                    ($case['next'] == Token::T_ELSE ||  $case['next'] == Token::T_IF)
                )
            ){
                //add the return value size (0x10 0x01)
                $endOffset += 8;
            }

            $code[$offsetIndex]->hex = Helper::fromIntToHex($endOffset);
            $code[$offsetIndex]->debug = $debugMsg . ' '. $code[$offsetIndex]->debug . ' line ' . (end($code)->lineNumber);

            if (isset($case['next'])) {

                if ($case['next'] == Token::T_IF){
                    $code[] = $getLine('3c000000', false, $debugMsg . 'next if');
                    $code[] = $getLine("STATEMENT_LAST_LINE_OFFSET", false, $debugMsg . 'next if offset');

                }
            }else{

                foreach ($code as &$item) {
                    if ($item->hex == "STATEMENT_LAST_LINE_OFFSET"){
                        $item->hex = Helper::fromIntToHex((end($code)->lineNumber) * 4);
                    }
                }
            }
        }

        return $code;
    }

}