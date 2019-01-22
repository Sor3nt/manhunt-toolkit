<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_CONDITION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [];

        $token = $node['body'][0];

        if ($token['type'] == Token::T_OPERATION){

            if (count($token['params']) == 1){

                foreach ($emitter($token['params'][0]) as $item) $code[] = $item;

                if ($node['isNot'] || $node['isOuterNot']){
                    self::setStatementNot($code, $getLine);
                }

            }else{


                /**
                 * little hack to map state variables
                 */

                $param1 = $token['params'][0]['value'];

                if (isset($data['variables'][ $param1 ])){

                    $var = $data['variables'][ $param1 ];

                    $searchedType = str_replace('level_var ', '', $var['type']);

                    if (isset($data['types'][ $searchedType ])){
                        $types = $data['types'][ $searchedType ];

                        $token['params'][1]['target'] = $searchedType;
                        $token['params'][1]['types'] = $types;
                    }
                }


                $operator = $token['operator'];

                foreach ($token['params'] as $index => $operation) {
                    if ($operation['type'] == Token::T_VARIABLE){
                        $mappedTo = T_VARIABLE::getMapping(
                            $operation,
                            $data
                        );
                    }

                    foreach ($emitter($operation) as $item) $code[] = $item;

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
                            }else if ($operation['type'] == Token::T_INT) {
                                if ($operation['value'] < 0) {
                                    $code[] = $getLine('2a000000');
                                    $code[] = $getLine('01000000');
                                }

                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                            }else if (isset($mappedTo['type']) && $mappedTo['type'] == "customFunction"){

                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                            }else{
                                $code[] = $getLine('0f000000');
                                $code[] = $getLine('04000000');

                            }

                        }else{
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

                if (count($token['params'])){

                    $lastParam = end($token['params']);
//
//                    if ($lastParam['type'] == Token::T_STRING){
//                        $code[] = $getLine('10000000');
//                        $code[] = $getLine('01000000');
//                        $code[] = $getLine('10000000');
//                        $code[] = $getLine('02000000');
//
//                    }else if ($lastParam['type'] == Token::T_FLOAT){
//                        $code[] = $getLine('10000000');
//                        $code[] = $getLine('01000000');
//                    }else if ($lastParam['type'] == Token::T_INT) {
//                        if ($lastParam['value'] < 0) {
//                            $code[] = $getLine('2a000000');
//                            $code[] = $getLine('01000000');
//                        }
//
//                        $code[] = $getLine('0f000000');
//                        $code[] = $getLine('04000000');
//
//                    }else if ($lastParam['type'] == Token::T_VARIABLE){
//                        $mappedTo = T_VARIABLE::getMapping(
//                            $lastParam,
//                            $data
//                        );
//
//                        if (isset($mappedTo['type']) && $mappedTo['type'] == "object") {
//                            $code[] = $getLine('10000000');
//                            $code[] = $getLine('01000000');
//                        }else if (isset($mappedTo['type']) && $mappedTo['type'] == "customFunction"){
//
//                            $code[] = $getLine('10000000');
//                            $code[] = $getLine('01000000');
//                        }
//                    }else{
//                        $code[] = $getLine('0f000000');
//                        $code[] = $getLine('04000000');
//
//
//                    }
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

                if ($node['isNot']) self::setStatementNot($code, $getLine);





                // not sure about this part
                //todo das stimmt hier garnicht, ich greif einfach auf das letzte mapping vom loop zu...
                if (isset($mappedTo['type']) && $mappedTo['type'] == "stringarray") {
                    $code[] = $getLine('49000000');
                }else if (
                    (isset($mappedTo['type']) && $mappedTo['type'] == "object") ||
                    (isset($operation) && $operation['type'] == Token::T_FLOAT)
                ){
//                    $code[] = $getLine('4e000000');
//                }else if (isset($operation) && $operation['type'] == Token::T_VARIABLE){
                    $code[] = $getLine('4e000000');
                }else if (isset($operation) && $operation['type'] == Token::T_STRING){
                    $code[] = $getLine('4e000000');
                }else{

                    if (isset($mappedTo) && $mappedTo['type'] == "customFunction"){
                        $code[] = $getLine('4e000000');

                    }else{
                        $code[] = $getLine('23000000');
                        $code[] = $getLine('04000000');
                        $code[] = $getLine('01000000');
                    }
                }





                $code[] = $getLine('12000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('01000000');


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
                    self::setStatementNot($code, $getLine);
                }
            }
        }

        return $code;
    }



    static public function setStatementNot( &$code, \Closure $getLine ){
        $code[] = $getLine('29000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }
}