<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Token;

class T_CONDITION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [];

        $token = $node['body'][0];

        if ($token['type'] == Token::T_OPERATION){

            if (count($token['params']) == 1){

                $result = $emitter($token['params'][0]);
                foreach ($result as $item) {
                    $code[] = $item;
                }

                if ($node['isNot'] || $node['isOuterNot']){
                    Evaluate::setStatementNot($code, $getLine);
                }

            }else{

                $operator = $token['operator'];

                foreach ($token['params'] as $index => $operation) {

                    if ($operation['type'] == Token::T_VARIABLE){
                        $mappedTo = T_VARIABLE::getMapping(
                            $operation,
                            null,
                            $data
                        );
                    }

                    $result = $emitter($operation);
                    foreach ($result as $item) {
                        $code[] = $item;
                    }

                    if (
                        isset($mappedTo['type']) &&
                        (
                            $mappedTo['type'] == "stringarray"
                        )
                    ){
                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');

                        $code[] = $getLine('10000000');
                        $code[] = $getLine('02000000');
                    }else{
                        if ($index + 1 == count($token['params'])){

                            if (isset($mappedTo['type']) && $mappedTo['type'] == "object") {
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                            }else if ($operation['type'] == Token::T_STRING){
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('02000000');
                            }else if ($operation['type'] == Token::T_FLOAT){
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                            }else if ($operation['type'] == Token::T_INT){
                                if ($operation['value'] >= 0){
                                    $code[] = $getLine('0f000000');
                                    $code[] = $getLine('04000000');
                                }else{
                                    $code[] = $getLine('2a000000');
                                    $code[] = $getLine('01000000');

                                    $code[] = $getLine('0f000000');
                                    $code[] = $getLine('04000000');

                                }
                            }else{
                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                            }

                        }else{
//                            $code[] = $getLine($token['params'][$index]['value']);
//                            $code[] = $getLine('10000000');
//                            $code[] = $getLine('01000000');


                            $functionNoReturnDefault = ManhuntDefault::$functionNoReturn;
                            $functionNoReturn = Manhunt2::$functionNoReturn;
                            if (GAME == "mh1") $functionNoReturn = Manhunt::$functionNoReturn;
                            if (
                                !isset($token['params'][$index]['value']) ||
                                (
                                    isset($token['params'][$index]['value']) &&
                                    !in_array(strtolower($token['params'][$index]['value']), $functionNoReturnDefault ) &&
                                    !in_array(strtolower($token['params'][$index]['value']), $functionNoReturn )

                                )
                            ){

                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                            }

                        }
                    }
                }

                if ($token['operation']['type'] == Token::T_AND) {

                    $code[] = $getLine('25000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');

                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');
                }else if ($token['operation']['type'] == Token::T_OR){
                    throw new \Exception(" Or implementation missed");
                }

                if ($node['isNot']){
                    Evaluate::setStatementNot($code, $getLine);
                }

                // not sure about this part
                if (isset($mappedTo['type']) && $mappedTo['type'] == "stringarray") {
                    $code[] = $getLine('49000000');
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }else if (isset($mappedTo['type']) && $mappedTo['type'] == "object"){
                    $code[] = $getLine('4e000000');
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }else if (isset($operation) && $operation['type'] == Token::T_FLOAT){
                    $code[] = $getLine('4e000000');
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }else if (isset($operation) && $operation['type'] == Token::T_STRING){
                    $code[] = $getLine('4e000000');
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }else{
                    $code[] = $getLine('23000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('12000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }

                if ($operator){

                    switch ($operator['type']){
                        case Token::T_IS_EQUAL:
                            $code[] = $getLine('3f000000');
                            break;
                        case Token::T_IS_NOT_EQUAL:
                            $code[] = $getLine('40000000');
                            break;
                        case Token::T_IS_SMALLER:
                            $code[] = $getLine('3d000000');
                            break;
                        case Token::T_IS_GREATER:
                            $code[] = $getLine('42000000');
                            break;
                        case Token::T_IS_GREATER_EQUAL:
                            $code[] = $getLine('41000000');
                            break;
                        default:
                            throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $operator['type']));
                            break;
                    }

//                    Evaluate::statementOperator($operator, $code, $getLine);

                    $lastLine = end($code)->lineNumber + 4;

                    // line offset for the IF start (or so)
                    $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

                    if($token['params'][1] == Token::T_FLOAT) {
                        $code[] = $getLine('12000000');
                    }else{
                        $code[] = $getLine('33000000');
                    }

                    $code[] = $getLine('01000000');
                    $code[] = $getLine('01000000');
                }

                if (isset($node['isOuterNot']) && $node['isOuterNot']){
                    Evaluate::setStatementNot($code, $getLine);
                }
            }
        }
        return $code;
    }

}