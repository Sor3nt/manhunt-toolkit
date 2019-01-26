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

        $debugMsg = '[T_CONDITION] map ';

        $token = $node['body'][0];

        if ($token['type'] == Token::T_OPERATION){

            if (count($token['params']) == 1){

                foreach ($emitter($token['params'][0]) as $item){
                    $item->debug = $debugMsg . ' ' . $item->debug;
                    $code[] = $item;
                }

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

                $lastIndex = count($token['params']) - 1;
                foreach ($token['params'] as $index => $operation) {
                    $isLastIndex = $index == $lastIndex;

                    $debugMsg = sprintf('[T_CONDITION] map: type ');

                    foreach ($emitter($operation) as $item){
                        $item->debug = $debugMsg . ' ' . $item->debug;
                        $code[] = $item;
                    }

                    if (
                        $operation['type'] == Token::T_FUNCTION
                    ) {

                        $funcMappedTo = $data['customData']['functions'][ $operation['value'] ];

                        if (!isset($funcMappedTo['return'])){
                            throw new \Exception(sprintf('No return value configured for %s', $operation['value']));
                        }


                        if ($funcMappedTo['return'] == 'String') {
                        }else if ($isLastIndex){
                            $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
                            $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
//
                        }else{
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
//
                        }

                    }else if ($operation['type'] == Token::T_NIL){
//                        var_dump($operation);

//                        throw new \Exception('sd');
//
//                        if (isset($operation['nested']) && $operation['nested']){
//                            $code[] = $getLine('10000000', false, $debugMsg);
//                            $code[] = $getLine('01000000', false, $debugMsg);
//
//                        }else{

                        if ($isLastIndex){
                            $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
                            $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
//
                        }else{
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
//
                        }
//                        }


                    }else if ($operation['type'] == Token::T_VARIABLE){
                        $mappedTo = T_VARIABLE::getMapping(
                            $operation,
                            $data
                        );

                        if ($mappedTo['section'] == "script") {

                            if (
                                $mappedTo['type'] == "entityptr" ||
                                $mappedTo['type'] == "level_var boolean" ||
                                $mappedTo['type'] == "integer" ||
                                $mappedTo['type'] == "boolean" ||
                                $mappedTo['type'] == "object"
                            ) {

                                if ($isLastIndex){
                                    $code[] = $getLine('0f000000', false, $debugMsg);
                                    $code[] = $getLine('04000000', false, $debugMsg);
                                }else{
                                    $code[] = $getLine('10000000', false, $debugMsg);
                                    $code[] = $getLine('01000000', false, $debugMsg);
                                }


                            }else if ($mappedTo['type'] == "customFunction") {
                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('01000000', false, $debugMsg);
                            }else{

                                throw new \Exception(sprintf(
                                    'T_CONDITION: script config missed for %s',
                                    $mappedTo['type']
                                ));
                            }


//                                $code[] = $getLine('10000000', false, $debugMsg);
//                            $code[] = $getLine('01000000', false, $debugMsg);
                        }else if ($mappedTo['section'] == "header"){

                            if (
                                $mappedTo['type'] == "integer" ||
                                $mappedTo['type'] == "boolean" ||
                                $mappedTo['type'] == "constant" ||
                                $mappedTo['type'] == "level_var boolean" ||
                                $mappedTo['type'] == "level_var integer"

                            ) {

                                if ($isLastIndex){
                                    $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
                                    $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
                                }else{
                                    $code[] = $getLine('10000000', false, $debugMsg);
                                    $code[] = $getLine('01000000', false, $debugMsg);
                                }


                            }else if ($mappedTo['type'] == "stringarray") {

                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('01000000', false, $debugMsg);

                                $code[] = $getLine('10000000', false, $debugMsg . $mappedTo['type']);
                                $code[] = $getLine('02000000', false, $debugMsg . $mappedTo['type']);

                            }else{

                                throw new \Exception(sprintf(
                                    'T_CONDITION: header config missed for %s',
                                    $mappedTo['type']
                                ));
                            }
                        }else{
                            throw new \Exception(sprintf(
                                'T_CONDITION: other config missed for %s',
                                $mappedTo['type']
                            ));
                        }


                    }else if ( $operation['type'] == Token::T_INT){
                        if ($operation['value'] < 0) {
                            $code[] = $getLine('2a000000', false, $debugMsg . $operation['type']);
                            $code[] = $getLine('01000000', false, $debugMsg . $operation['type']);
                        }

                        if ($index == 0){
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);

                        }else{
                            $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
                            $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);

                        }

                    }else if (
                        $operation['type'] == Token::T_TRUE ||
                        $operation['type'] == Token::T_FALSE
                    ){

                        if ($index != $lastIndex){
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
//
                        }else{
                            $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
                            $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
//
                        }

//

//                        $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
//                        $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
                    }else if (
                        $operation['type'] == Token::T_FLOAT
                    ){
                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('01000000', false, $debugMsg);
                    }else if (
                        $operation['type'] == Token::T_STRING
                    ){
                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('01000000', false, $debugMsg);

                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('02000000', false, $debugMsg);
                    }else{

                        throw new \Exception(sprintf(
                            'T_CONDITION:  missed for %s',
                            $operation['type']
                        ));

                    }


//
//                    if (
//                        isset($mappedTo['type']) &&
//                        (
//                            $mappedTo['type'] == "stringarray"
//                        )
//                    ){
//
//                        $code[] = $getLine('10000000', false, $debugMsg . $mappedTo['type']);
//                        $code[] = $getLine('01000000', false, $debugMsg . $mappedTo['type']);
//
//                        $code[] = $getLine('10000000', false, $debugMsg . $mappedTo['type']);
//                        $code[] = $getLine('02000000', false, $debugMsg . $mappedTo['type']);
//                    }else{
//                        if ($index + 1 == count($token['params'])){
//
//                            if (isset($mappedTo['type']) && $mappedTo['type'] == "object") {
//                                $code[] = $getLine('10000000', false, $debugMsg . $mappedTo['type']);
//                                $code[] = $getLine('01000000', false, $debugMsg . $mappedTo['type']);
//                            }else if ($operation['type'] == Token::T_STRING){
//                                $code[] = $getLine('10000000', false, $debugMsg . $operation['type']);
//                                $code[] = $getLine('01000000', false, $debugMsg . $operation['type']);
//                                $code[] = $getLine('10000000', false, $debugMsg . $operation['type']);
//                                $code[] = $getLine('02000000', false, $debugMsg . $operation['type']);
//
//                            }else if ($operation['type'] == Token::T_FLOAT){
//                                $code[] = $getLine('10000000', false, $debugMsg . $operation['type']);
//                                $code[] = $getLine('01000000', false, $debugMsg . $operation['type']);
//                            }else if ($operation['type'] == Token::T_INT) {
//                                if ($operation['value'] < 0) {
//                                    $code[] = $getLine('2a000000', false, $debugMsg . $operation['type']);
//                                    $code[] = $getLine('01000000', false, $debugMsg . $operation['type']);
//                                }
//
//                                $code[] = $getLine('0f000000', false, $debugMsg . $operation['type']);
//                                $code[] = $getLine('04000000', false, $debugMsg . $operation['type']);
//
//                            }else if (isset($mappedTo['type']) && $mappedTo['type'] == "customFunction"){
//
//                                $code[] = $getLine('10000000', false, $debugMsg . $mappedTo['type']);
//                                $code[] = $getLine('01000000', false, $debugMsg . $mappedTo['type']);
//
//                            }else{
//                                $code[] = $getLine('0f000000', false, $debugMsg);
//                                $code[] = $getLine('04000000', false, $debugMsg);
//
//                            }
//
//                        }else{
//                            $functionNoReturnDefault = ManhuntDefault::$functionNoReturn;
//                            $functionNoReturn = Manhunt2::$functionNoReturn;
//
//                            if (
//                                !isset($token['params'][$index]['value']) ||
//                                (
//                                    isset($token['params'][$index]['value']) &&
//                                    !in_array(strtolower($token['params'][$index]['value']), $functionNoReturnDefault ) &&
//                                    !in_array(strtolower($token['params'][$index]['value']), $functionNoReturn )
//
//                                )
//                            ){
//
//                                $debugMsg = sprintf('[T_CONDITION] map: return ');
//
//                                $code[] = $getLine('10000000', false, $debugMsg);
//                                $code[] = $getLine('01000000', false, $debugMsg);
//                            }
//
//                        }
//                    }
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
                    $debugMsg = sprintf('[T_CONDITION] map: T_AND ');

                    $code[] = $getLine('25000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);

                    $code[] = $getLine('0f000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);
                }else if ($token['operation']['type'] == Token::T_OR){
                    throw new \Exception(" Or implementation missed");
                }

                if ($node['isNot']) self::setStatementNot($code, $getLine);





                // not sure about this part
                //todo das stimmt hier garnicht, ich greif einfach auf das letzte mapping vom loop zu...
                if (isset($mappedTo['type']) && $mappedTo['type'] == "stringarray") {
                    $code[] = $getLine('49000000', false, '[T_CONDITION] map (finalize?): stringarray');
                }else if (
                    (isset($mappedTo['type']) && $mappedTo['type'] == "object") ||
                    (isset($operation) && $operation['type'] == Token::T_FLOAT)
                ){
//                    $code[] = $getLine('4e000000');
//                }else if (isset($operation) && $operation['type'] == Token::T_VARIABLE){
                    $code[] = $getLine('4e000000', false, '[T_CONDITION] map (finalize?): stringarray');
                }else if (isset($operation) && $operation['type'] == Token::T_STRING){
                    $code[] = $getLine('4e000000', false, '[T_CONDITION] map (finalize?): T_STRING');
                }else{

                    if (isset($mappedTo) && $mappedTo['type'] == "customFunction"){
                        $code[] = $getLine('4e000000', false, '[T_CONDITION] map (finalize?): customFunction');

                    }else{
                        $code[] = $getLine('23000000', false, '[T_CONDITION] map (finalize?): other');
                        $code[] = $getLine('04000000', false, '[T_CONDITION] map (finalize?): other');
                        $code[] = $getLine('01000000', false, '[T_CONDITION] map (finalize?): other');
                    }
                }





                $code[] = $getLine('12000000', false, '[T_CONDITION] map ( after finalize?)');
                $code[] = $getLine('01000000', false, '[T_CONDITION] map ( after finalize?)');
                $code[] = $getLine('01000000', false, '[T_CONDITION] map ( after finalize?)');


                if ($operator){
                    $debugMsg = sprintf('[T_CONDITION] map: operation ' . $operator['type']);

                    switch ($operator['type']){
                        case Token::T_IS_EQUAL:
                            $code[] = $getLine('3f000000', false, $debugMsg);
                            break;
                        case Token::T_IS_NOT_EQUAL:
                            $code[] = $getLine('40000000', false, $debugMsg);
                            break;
                        case Token::T_IS_SMALLER:
                            $code[] = $getLine('3d000000', false, $debugMsg);
                            break;
                        case Token::T_IS_GREATER:
                            $code[] = $getLine('42000000', false, $debugMsg);
                            break;
                        case Token::T_IS_GREATER_EQUAL:
                            $code[] = $getLine('41000000', false, $debugMsg);
                            break;
                        default:
                            throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $operator['type']));
                            break;
                    }

                    $lastLine = end($code)->lineNumber + 4;

                    // line offset for the IF start (or so)
                    $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

                    if($token['params'][1] == Token::T_FLOAT) {
                        $code[] = $getLine('12000000', false, '[T_CONDITION] map ( ka ) float');
                    }else{
                        $code[] = $getLine('33000000', false, '[T_CONDITION] map ( ka ) NO float');
                    }

                    $code[] = $getLine('01000000', false, '[T_CONDITION] map ( ka ) end');
                    $code[] = $getLine('01000000', false, '[T_CONDITION] map ( ka ) end');
                }

                if (isset($node['isOuterNot']) && $node['isOuterNot']){
                    self::setStatementNot($code, $getLine);
                }
            }
        }

        return $code;
    }



    static public function setStatementNot( &$code, \Closure $getLine ){
        $debugMsg = sprintf('[T_CONDITION] setStatementNot: NOT');
        $code[] = $getLine('29000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }
}