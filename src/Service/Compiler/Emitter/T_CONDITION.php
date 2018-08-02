<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;

class T_CONDITION {


    static public function finalize( $node, $data, &$code, \Closure $getLine ){
        $mappedTo = [];

        switch ($node['type']){
//
            case Token::T_FUNCTION:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
                break;
            case Token::T_INT:
            case Token::T_NIL:
//            case Token::T_TRUE:
//            case Token::T_FALSE:
                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');


                break;
//

            case Token::T_TRUE:
            case Token::T_FALSE:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

                break;

            case Token::T_VARIABLE:
                $mappedTo = T_VARIABLE::getMapping(
                    $node,
                    null,
                    $data
                );

                switch ($mappedTo['section']) {

                    case 'script':

                        switch ($mappedTo['type']) {

//                            case 'entityptr':
//                                $code[] = $getLine('0f000000');
//                                $code[] = $getLine('04000000');
//                                break;

                            case 'object':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                break;

                            default:
                                throw new \Exception($mappedTo['type'] . " Not implemented!");
                                break;
                        }
                        break;
                    case 'header':

                        switch ($mappedTo['type']) {

                            case 'constant':
                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                                break;
                            case 'boolean':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;
                            case 'level_var boolean':

                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                                break;

                            default:
                                throw new \Exception($mappedTo['type'] . " Not implemented!");
                                break;
                        }
                        break;

                    default:
                        throw new \Exception($mappedTo['section'] . " Not implemented!");
                        break;
                }

                break;
            default:
                throw new \Exception($node['type'] . " Not implemented!");
                break;


        }

        return $mappedTo;
    }

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        $token = $node['body'][0];

        if ($token['type'] == Token::T_OPERATION){

            if (count($token['params']) == 1){

                $result = $emitter($token['params'][0]);
                foreach ($result as $item) {
                    $code[] = $item;
                }

                if ($node['isNot']){
                    Evaluate::setStatementNot($code, $getLine);
                }

            }else{

                $operator = $token['operator'];

                $mapped = false;
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

                    if ($index + 1 == count($token['params'])){

                        if (isset($mappedTo['type']) && $mappedTo['type'] == "object"){
                            $code[] = $getLine('10000000');
                            $code[] = $getLine('01000000');

                        }else{
                            $code[] = $getLine('0f000000');
                            $code[] = $getLine('04000000');

                        }


                    }else{
                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');

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
                if (isset($mappedTo['type']) && $mappedTo['type'] == "object"){
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

                    Evaluate::statementOperator($operator, $code, $getLine);

                    $lastLine = end($code)->lineNumber + 4;

                    // line offset for the IF start (or so)
                    $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

                    Evaluate::setStatementFullCondition($code, $getLine);
                }
            }
        }

        return $code;
    }

}