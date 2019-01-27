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
















                    $output = "none";

                    if ( $operation['type'] == Token::T_FUNCTION ) {

                        $funcMappedTo = $data['customData']['functions'][ $operation['value'] ];

                        if (!isset($funcMappedTo['return'])){
                            throw new \Exception(sprintf('No return value configured for %s', $operation['value']));
                        }

                        if ($funcMappedTo['return'] != 'String') $output = "regular";

                    }else if ($operation['type'] == Token::T_VARIABLE){
                        $mappedTo = T_VARIABLE::getMapping(
                            $operation,
                            $data
                        );

                        if (
                            substr($mappedTo['type'], 0, 9) == "level_var" ||
                            $mappedTo['type'] == "entityptr" ||
                            $mappedTo['type'] == "constant" ||
                            $mappedTo['type'] == "integer" ||
                            $mappedTo['type'] == "boolean" ||
                            $mappedTo['type'] == "object"
                        ) {
                            $output = "regular";

                        }else if ($mappedTo['type'] == "customFunction") {
                            $output = "customFunction";

                        }else if ($mappedTo['type'] == "stringarray") {
                            $output = "string";

                        }else{

                            throw new \Exception(sprintf(
                                'T_CONDITION: script config missed for %s',
                                $mappedTo['type']
                            ));
                        }

                    }else if (
                        $operation['type'] == Token::T_INT ||
                        $operation['type'] == Token::T_NIL ||
                        $operation['type'] == Token::T_TRUE ||
                        $operation['type'] == Token::T_SELF ||
                        $operation['type'] == Token::T_FALSE
                    ){
                        $output = "regular";

                    }else if ( $operation['type'] == Token::T_FLOAT ){
                        $output = "float";

                    }else if ( $operation['type'] == Token::T_STRING ){

                        $output = "string";

                    }else{

                        throw new \Exception(sprintf(
                            'T_CONDITION:  missed for %s',
                            $operation['type']
                        ));

                    }


                    if ($operation['type'] == Token::T_INT && $operation['value'] < 0) {
                        $code[] = $getLine('2a000000', false, $debugMsg . $operation['type']);
                        $code[] = $getLine('01000000', false, $debugMsg . $operation['type']);
                    }

                    if ($output == "string") {
                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('01000000', false, $debugMsg);

                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('02000000', false, $debugMsg);

                    }else if ($output == "float" || $output == "customFunction") {
                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('01000000', false, $debugMsg);

                    }else if ($output == "regular"){
                        if ($isLastIndex){
                            $code[] = $getLine('0f000000', false, $debugMsg);
                            $code[] = $getLine('04000000', false, $debugMsg);
                        }else{
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
                        }

                    }



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



    static public function finalize( &$code, \Closure $getLine ){

    }


    static public function setStatementNot( &$code, \Closure $getLine ){
        $debugMsg = sprintf('[T_CONDITION] setStatementNot: NOT');
        $code[] = $getLine('29000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
        $code[] = $getLine('01000000', false, $debugMsg);
    }
}