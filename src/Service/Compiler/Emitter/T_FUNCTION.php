<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FUNCTION {


    private $blockOffsets;
    private $combinedVariables;
    private $functions;

    public function __construct( $customData )
    {
        $this->blockOffsets = $customData['blockOffsets'];
        $this->combinedVariables = $customData['combinedVariables'];
        $this->functions = $customData['functions'];
    }

    public function finalize( $node, $data, &$code, \Closure $getLine, $writeDebug = false, $isProcedure = false, $isCustomFunction = false ){

        switch ($node['type']){
            case Token::T_FLOAT:
            case Token::T_BOOLEAN:
            case Token::T_SELF:
            case Token::T_MULTIPLY:
                Evaluate::regularReturn($code, $getLine);
            break;


            case Token::T_ADDITION:
            case Token::T_FUNCTION:
            case Token::T_INT:
                break;

            case Token::T_STRING:

                if ($isProcedure == false && $isCustomFunction == false){

                    Evaluate::stringReturn($code, $getLine);
                }

                break;

            case Token::T_VARIABLE:
                $mappedTo = T_VARIABLE::getMapping(
                    $node,
                    $data
                );

                switch ($mappedTo['section']) {
                    case 'header':

                        if ($data['game'] == MHT::GAME_MANHUNT && $writeDebug) {

                        }else{


                            if (
                                isset($mappedTo['objectType']) &&
                                $mappedTo['objectType'] == 'stringarray'
                            ){


                                Evaluate::stringReturn($code, $getLine);

                            }else{
                                Evaluate::regularReturn($code, $getLine);

                            }
                        }

                        break;

                    case 'script':
                        $debugMsg = sprintf('[T_FUNCTION] finalize: script %s ', $mappedTo['type']);

                        switch ($mappedTo['type']) {

                            case 'entityptr':
                            case 'vec3d':
                            case 'integer':
                                Evaluate::regularReturn($code, $getLine);
                                break;

                            case 'customFunction':
                                $code[] = $getLine('12000000', false, $debugMsg);
                                $code[] = $getLine('02000000', false, $debugMsg);
                                break;

                            case 'stringarray':
                                Evaluate::stringReturn($code, $getLine);

                                break;

                            case 'procedure':
                                $debugMsg = sprintf('[T_FUNCTION] finalize: procedure %s ', $mappedTo['valueType']);

                                switch ($mappedTo['valueType']){
                                    case 'string':
                                        $code[] = $getLine('12000000', false, $debugMsg);
                                        $code[] = $getLine('02000000', false, $debugMsg);

                                        $code[] = $getLine('00000000', false, $debugMsg); // 0 always ?

                                        Evaluate::stringReturn($code, $getLine);
                                        break;
                                    case 'real':
                                        Evaluate::regularReturn($code, $getLine);
                                        break;

                                    default:
                                        throw new \Exception($mappedTo['valueType'] . " Not implemented!");
                                        break;

                                }

                                break;
                            case 'real':
                                if ($writeDebug == false){
                                    Evaluate::regularReturn($code, $getLine);
                                }
                                break;
                            case 'constant':

                                if ($mappedTo['valueType'] == "string"){
                                    Evaluate::stringReturn($code, $getLine);
                                }else{
                                    Evaluate::regularReturn($code, $getLine);
                                }
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
    }

    public function handleWriteDebugCall($node, \Closure $getLine, \Closure $emitter, $data){

        $debugMsg = '[T_FUNCTION] handleWriteDebugCall ';
        $code = [];

        /**
         *
         * The WriteDebug call need to be separated into single calls.
         * Any call can only process one parameter...
         *
         */
        if (count($node['params']) > 1 ){

            foreach ($node['params'] as $index => $param) {
                $singleParam = $node;
                $singleParam['params'] = [$param];
                $singleParam['last'] = $index == count($node['params']) - 1;

                $result = $this->handleWriteDebugCall($singleParam, $getLine, $emitter, $data);

                foreach ($result as $item) {
                    $item->debug = '[WriteDebug] ' . $item->debug;
                    $code[] = $item;
                }
            }

            return $code;
        }

        /**
         * generate the parameter code
         */
        $param = $node['params'][0];
        $param['nested'] = false;

        foreach ($emitter( $param ) as $line){
            $line->debug = $debugMsg . ' ' . $line->debug;
            $code[] = $line;
        }


        $this->finalize($param, $data, $code, $getLine, true);

        /**
         * generate the needed function call
         */
        switch ($param['type']){

            case Token::T_STRING:
                $code[] = $getLine($this->getFunction('WriteDebugString')['offset']);
                break;
            case Token::T_VARIABLE:

                $mapping = T_VARIABLE::getMapping($param, $data);

                switch ($mapping['type']){
                    case 'real':
                    case 'integer':
                    case 'object':
                    case 'stringarray':
                        $code[] = $getLine($this->getFunction('WriteDebug' . ucfirst($mapping['type']) )['offset']);
                        break;

                    case 'procedure':
                        $code[] = $getLine($this->getFunction('WriteDebug')['offset']);
                        break;
                    default:
                        throw new \Exception(sprintf('T_VARIABLE: mapping type %s is unknown', $mapping['type']));
                        break;
                }

                break;
            case Token::T_FUNCTION:
                $function = $this->getFunction($param['value']);

                if (!isset($function['return'])){
                    throw new \Exception(sprintf('T_FUNCTION: Return type for %s missed', $param['value']));
                }

                $code[] = $getLine($this->getFunction('WriteDebug' . ucfirst($function['return']) )['offset']);


                break;
            default:
                throw new \Exception(sprintf('T_FUNCTION: Param type %s is unknown', $param['type']));
                break;
        }

        // the writedebug call has a secret additional call, a flush command
        if (!isset($node['last']) || $node['last'] === true) {
            $code[] = $getLine($this->getFunction('WriteDebugFlush')['offset']);
        }

        return $code;
    }

    public function getForceFloat( $functionName ){

        $functionName = strtolower($functionName);

        $functionForceFloat = array_merge(Manhunt2::$functionForceFloar, ManhuntDefault::$functionForceFloar);

        if (isset( $functionForceFloat[$functionName] )) return $functionForceFloat[$functionName];

        return [];
    }

    public function getFunction($functionName ){

        $functionName = strtolower($functionName);

        if ( !isset($this->functions[$functionName]) ){
            throw new \Exception(sprintf('Unknown function %s', $functionName));
        }

        return $this->functions[$functionName];
    }

    public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = '[T_FUNCTION] map ';
        $code = [ ];

        /**
         * sometimes is the mapping not correct, validate it
         */

        try {
            $mapping = T_VARIABLE::getMapping($node, $data);

            //todo: why do the variable mapper, map custom functions ?!
            if ($mapping['type'] != 'custom_functions'){
                return $emitter([
                    'type' => Token::T_VARIABLE,
                    'value' => $node['value']
                ]);
            }

        }catch(\Exception $e){

            if (strpos($e->getMessage(), 'unable to find variable') == false){
                throw $e;
            }
        }

        /**
         * Special WriteDebug handling
         */
        if (strtolower($node['value']) == "writedebug"){
            return $this->handleWriteDebugCall($node, $getLine, $emitter, $data);
        }

        $forceFloatOrder = $this->getForceFloat($node['value']);

        $isProcedure = false;
        $isCustomFunction = false;

        $mappedToBlock = false;

        if (isset($this->blockOffsets[ strtolower($node['value']) ]) ){

            $mappedToBlock = $this->blockOffsets[ strtolower($node['value']) ];

            switch ($this->blockOffsets[ strtolower($node['value']) ]['blockType']){

                case Token::T_PROCEDURE:
                    $isProcedure = true;
                    break;
                case Token::T_CUSTOM_FUNCTION:
                    $isCustomFunction = true;
                    break;
            }

        }

        if (isset($node['params']) && count($node['params'])){
            $skipNext = false;

            foreach ($node['params'] as $index => $param) {

                if ($skipNext){
                    $skipNext = false;
                    continue;
                }

                if ($param['type'] == Token::T_ADDITION){
                    $mathValue = $node['params'][$index + 1];

                    foreach ($emitter( $mathValue ) as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }

                    $debugMsg = sprintf('[T_FUNCTION] map: addition %s', $mathValue['value']);

                    Evaluate::returnCache($code, $getLine);


                    $code[] = $getLine('31000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);

                    Evaluate::regularReturn($code, $getLine);

                    $skipNext = true;
                }else if ($param['type'] == Token::T_SUBSTRACTION){
                    throw new \Exception('T_SUBSTRACTION not implemented');
                }else if ($param['type'] == Token::T_MULTIPLY){

                    $mathValue = $node['params'][$index + 1];

                    $resultCode = $emitter( $mathValue );
                    foreach ($resultCode as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }

                    $debugMsg = sprintf('[T_FUNCTION] map: subtraction %s', $mathValue['value']);
                    Evaluate::returnCache($code, $getLine);


                    $code[] = $getLine('35000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);

                    Evaluate::regularReturn($code, $getLine);

                    $skipNext = true;

                }else{
                    $resultCode = $emitter( $param, true, [
                        'isProcedure' => $isProcedure,
                        'isCustomFunction' => $isCustomFunction
                    ]);

                    foreach ($resultCode as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }
                }

                $this->finalize($param, $data, $code, $getLine, false, $isProcedure, $isCustomFunction);

                /**
                 * When the input value is a negative float
                 * we assign the positive value and negate them with this sequence
                 */
                if (
                    $param['type'] == Token::T_FLOAT &&
                    (
                        $param['value'] < 0 ||
                        // -0 cant be detected by php, need the hex value for it
                        Helper::fromFloatToHex($param['value']) == "00000080"
                    )
                ) {

                    Evaluate::negate(Token::T_FLOAT, $code, $getLine);
                    Evaluate::regularReturn($code, $getLine);
                }else if ($param['type'] == Token::T_INT){

                    //todo: hm irgendwie ist das komisch hier
                    //todo: genauer nochmal anschauen
                    if ($param['value'] < 0){
                        Evaluate::negate(Token::T_INT, $code,$getLine);
                    }

                    Evaluate::regularReturn($code, $getLine);
                }



                /**
                 * when a function need a float but receive a int instead
                 * we need to tell the engine to convert the int to float
                 */
                if (
                    count($forceFloatOrder) > 0 &&
                    $param['type'] == Token::T_INT &&
                    $forceFloatOrder[$index] === true
                ) {
                    $debugMsg = sprintf('[T_FUNCTION] map: convert int to float %s', $param['value']);

                    Evaluate::int2float($code, $getLine);

                    Evaluate::regularReturn($code, $getLine);

                }

            }
        }

        if ($isProcedure || $isCustomFunction) {
            $procedureOffset = $mappedToBlock['offset'];

            Evaluate::goto($node['value'], $procedureOffset * 4, $code, $getLine);

            return $code;
        }

        /**
         * Translate function call
         */
        $function = $this->getFunction($node['value']);

        $debugMsg = sprintf('[T_FUNCTION] map: call function %s', $node['value']);

        $code[] = $getLine($function['offset'], false, $debugMsg);


        /**
         * when we are inside a nested call, tell the interpreter to return the current value
         */

        if (isset($node['nested']) && $node['nested'] === true){

            /**
             * Mystery: any function who return vec3d or a string do not need a return code.
             */
            if (
                !isset($function['return']) || (
                    $function['return'] != "vec3d" &&
                    $function['return'] != "string"
                )
            ){
                Evaluate::regularReturn($code, $getLine);
            }

        }

        $this->processArguments($node, $code, $getLine, $emitter);

        return $code;
    }

    private function processArguments($node, &$code, \Closure $getLine, \Closure $emitter){
        if (!isset($node['arguments'])) return;

        $debugMsg = 'processArguments ';

        foreach ($node['arguments'] as $index => $argument) {

            Evaluate::readIndex($index, $code, $getLine);

            Evaluate::regularReturn($code, $getLine);

            $resultCode = $emitter( $argument );
            foreach ($resultCode as $line) {
                $line->debug = $debugMsg .  ' ' . $line->debug;
                $code[] = $line;
            }

            Evaluate::regularReturn($code, $getLine);

            if ($index == 0){
                $code[] = $getLine('07030000', false, $debugMsg . ' A OFFSET HMM');
            }else{
                $code[] = $getLine('08030000', false, $debugMsg . ' A OFFSET HMM');

            }
        }
    }

}