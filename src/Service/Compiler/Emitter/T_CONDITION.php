<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_CONDITION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [];

        $debugMsg = '[T_CONDITION] map ';

        $token = $node['body'][0];

        if ($token['type'] == Token::T_OPERATION){

            if (count($token['params']) == 1){


                if (
                    $data['game'] == MHT::GAME_MANHUNT &&
                    $token['params'][0]['type'] == Token::T_BOOLEAN
                ){
                    $code[] = $getLine('10000000', false, $debugMsg . 'mh1 boolean special');
                    $code[] = $getLine('01000000', false, $debugMsg . 'mh1 boolean special');
                    $code[] = $getLine('7d000000', false, $debugMsg . 'mh1 boolean special');
                }


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


                    //remove brackets from the operation
                    if ($operation['type'] == Token::T_BRACKET_OPEN){
                        $operation = $operation['params'][0];
                    }


                    $debugMsg = sprintf('[T_CONDITION] map: type ');

                    foreach ($emitter($operation) as $item){
                        $item->debug = $debugMsg . ' ' . $item->debug;
                        $code[] = $item;
                    }


                    /**
                     * We need for the parameters a special return code, depend on the used type
                     */

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
                            substr($mappedTo['type'], 0, 8) == "game_var" ||
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

                        }else if ($mappedTo['type'] == "array") {
                            $output = "array";

                        }else if ($mappedTo['type'] == "mhfxptr") {
                            $output = "regular";

                        }else if (isset($mappedTo['abstract']) && $mappedTo['abstract'] == "state") {
                            $output = "state";

                        }else{

                            throw new \Exception(sprintf(
                                'T_CONDITION: script config missed for %s',
                                $mappedTo['type']
                            ));
                        }

                    }else if (
                        $operation['type'] == Token::T_INT ||
                        $operation['type'] == Token::T_NIL ||
                        $operation['type'] == Token::T_BOOLEAN ||
                        $operation['type'] == Token::T_SELF
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

                    //generate the code based on the defined output (above)

                    if ($operation['type'] == Token::T_INT && $operation['value'] < 0) {
                        $code[] = $getLine('2a000000', false, $debugMsg . 'int lower 0');
                        $code[] = $getLine('01000000', false, $debugMsg . 'int lower 0');
                    }

                    if ($output == "string") {
                        $code[] = $getLine('10000000', false, $debugMsg . 'string');
                        $code[] = $getLine('01000000', false, $debugMsg . 'string');

                        $code[] = $getLine('10000000', false, $debugMsg . 'string');
                        $code[] = $getLine('02000000', false, $debugMsg . 'string');

                    }else if ($output == "float" || $output == "customFunction") {
                        $code[] = $getLine('10000000', false, $debugMsg . 'float');
                        $code[] = $getLine('01000000', false, $debugMsg . 'float');

                    }else if ($output == "state") {
                        $code[] = $getLine('10000000', false, $debugMsg . 'float');
                        $code[] = $getLine('01000000', false, $debugMsg . 'float');

                    }else if ($output == "array") {
                        $code[] = $getLine('10000000', false, $debugMsg . 'array');
                        $code[] = $getLine('01000000', false, $debugMsg . 'array');

                        $code[] = $getLine('10000000', false, $debugMsg . 'array');
                        $code[] = $getLine('02000000', false, $debugMsg . 'array');

                    }else if ($output == "regular"){
                        if ($isLastIndex){
                            $code[] = $getLine('0f000000', false, $debugMsg . 'regular');
                            $code[] = $getLine('04000000', false, $debugMsg . 'regular');
                        }else{
                            $code[] = $getLine('10000000', false, $debugMsg . 'regular');
                            $code[] = $getLine('01000000', false, $debugMsg . 'regular');
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


                /**
                 * this part tell the engine something about the comparision
                 * strings and floats need a special code
                 * any other conditions share the same code
                 */

                $toHandle = [];
                if (count($token['params']) == 2){

                    list($left, $right) = $token['params'];

                    $toHandle = [$left['type'], $right['type'] ];

                    foreach ($token['params'] as $side) {
                        if ($side['type'] == Token::T_VARIABLE) {
                            $mappedTo = T_VARIABLE::getMapping(
                                $side,
                                $data
                            );

                            $toHandle[] = $mappedTo['type'];
                        }
                    }
                }

                if (
                    in_array('stringarray', $toHandle) !== false ||
                    in_array('string', $toHandle) !== false ||
                    in_array(Token::T_STRING, $toHandle) !== false
                ) {
                    $code[] = $getLine('49000000', false, '[T_CONDITION] map finalize string');
                }else if (in_array(Token::T_FLOAT, $toHandle) !== false ||in_array('customFunction', $toHandle) !== false ){
                    $code[] = $getLine('4e000000', false, '[T_CONDITION] map finalize float');

                }else{
                    $code[] = $getLine('23000000', false, '[T_CONDITION] map finalize simple');
                    $code[] = $getLine('04000000', false, '[T_CONDITION] map finalize simple');
                    $code[] = $getLine('01000000', false, '[T_CONDITION] map finalize simple');
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