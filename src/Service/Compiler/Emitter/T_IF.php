<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
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

                if($current + 1 != count($params)){
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');
                }

            }else{
                $result = $emitter($node, true, [ 'isWhile' => $isWhile ]);
                foreach ($result as $item) {
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

        $code = [];

        foreach ($node['cases'] as $index => $case) {

            if (count($case['condition']) == 0){

                if (isset($case['isTrue']) && count($case['isTrue'])){
                    $code[] = $getLine('3c000000'); //else
                }

            }else{
                foreach ($case['condition'] as $conditionIndex => $condition) {

                    if ($condition['type'] == Token::T_BRACKET_OPEN){
                        $result =  self::handleBracketOpen($condition['params'], $condition, false, $getLine, $emitter, $isWhile);
                        foreach ($result as $item) {
                            $code[] = $item;
                        }


                        if($conditionIndex + 1 != count($case['condition'])){
                            Evaluate::returnResult($code, $getLine);

                        }
                    }else if (
                        $condition['type'] == Token::T_AND ||
                        $condition['type'] == Token::T_OR
                    ) {
                        continue;
                    }else{
                        throw new \Exception('T_IF: Brackets order not valid');
                    }
                }

                $code[] = $getLine('24000000');
                $code[] = $getLine('01000000');
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
            $code[] = $getLine( Helper::fromIntToHex($endOffset), $lastNumber + 1 );

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