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
                    $item->debug = $debugMsg . ' '. $item->debug;
                    $code[] = $item;
                }

                if($current + 1 != count($params)){
                    $code[] = $getLine('10000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);
                }

            }else{

                $result = $emitter($node, true, [ 'isWhile' => $isWhile ]);
                foreach ($result as $item) {
                    $item->debug = $debugMsg . ' '. $item->debug;
                    $code[] = $item;
                }

                if ($fullNode['operator'] != false){
                    Evaluate::setStatementOperator($fullNode, $code, $getLine);
                }

                if (
                    isset($fullNode['last']) &&
                    $fullNode['last'] == true &&
                    $parentOperator !== false
                ){
                    Evaluate::setStatementOperator(['operator' => $parentOperator], $code, $getLine);
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


            $byteAddon = 0;
            if (count($case['condition']) == 0){

                if (isset($case['isTrue']) && count($case['isTrue'])){
                    $code[] = $getLine('3c000000', false, $debugMsg . 'else (?)'); //else
                }

            }else{

                foreach ($case['condition'] as $conditionIndex => $condition) {

                    if ($condition['type'] == Token::T_BRACKET_OPEN){
                        $result =  self::handleBracketOpen($condition['params'], $condition, false, $getLine, $emitter, $isWhile);
                        foreach ($result as $item) {
                            $item->debug = $debugMsg . ' '. $item->debug;
                            $code[] = $item;
                        }

                        if($conditionIndex + 1 != count($case['condition'])){
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
                        }

                        //hmmm todo: gibts das so überhaupt noch ?
                    }else if (
                        $condition['type'] == Token::T_AND ||
                        $condition['type'] == Token::T_OR
                    ) {
                        continue;
                    }else{
                        throw new \Exception('T_IF: Brackets order not valid');
                    }


                }

                $code[] = $getLine('24000000', false, $debugMsg);
                $code[] = $getLine('01000000', false, $debugMsg);
                $code[] = $getLine('00000000', false, $debugMsg);
                $code[] = $getLine('3f000000', false, $debugMsg);
            }

            $isTrue = [];

            $lastNumber = end($code)->lineNumber;
            //pre generate the bytecode (only for calculation)

            foreach ($case['isTrue'] as $entry) {
                $codes = $emitter($entry, false, [ 'isWhile' => $isWhile ]);
                foreach ($codes as $singleLine) {
                    $singleLine->debug = $debugMsg . ' '. $singleLine->debug;
                    $isTrue[] = $singleLine;
                }
            }


            $endOffset = ($lastNumber + count($isTrue) + 1) * 4;

            if ($isWhile) $endOffset = $endOffset + 8;

            if (isset($case['next'])){

                if ($case['next'] == Token::T_ELSE ) {
                    $endOffset += 8;
                }else if ($case['next'] == Token::T_IF ){
                    $endOffset += 8;
                }
            }

            // line offset for the IF end
//            $code[] = $getLine( ($endOffset / 4) . " - " . Helper::fromIntToHex($endOffset) );
            $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1, $debugMsg . 'offset ' . $endOffset );

            foreach ($case['isTrue'] as $entry) {
                $codes = $emitter($entry, true, [ 'isWhile' => $isWhile ]);
                foreach ($codes as $singleLine) {
                    $singleLine->debug = $debugMsg . ' '. $singleLine->debug;
                    $code[] = $singleLine;
                }
            }

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