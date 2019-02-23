<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
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

        $debugMsg = sprintf('[T_FUNCTION] finalize: %s ', $node['type']);

        switch ($node['type']){
            case Token::T_FLOAT:
            case Token::T_BOOLEAN:
            case Token::T_SELF:
            case Token::T_MULTIPLY:
                $code[] = $getLine('10000000', false, $debugMsg);
                $code[] = $getLine('01000000', false, $debugMsg);
            break;


            case Token::T_ADDITION:
            case Token::T_FUNCTION:
                break;

            case Token::T_INT:

                    if ($node['value'] >= 0){
                        $code[] = $getLine('10000000', false, $debugMsg . 'value=' . $node['value'] . ' (first)');
                        $code[] = $getLine('01000000', false, $debugMsg . '(last)');
                    }else{
                        $code[] = $getLine('2a000000', false, $debugMsg . 'value=' . $node['value'] . ' (negative) (first)');
                        $code[] = $getLine('01000000', false, $debugMsg);
                        $code[] = $getLine('10000000', false, $debugMsg);
                        $code[] = $getLine('01000000', false, $debugMsg . '(last)');
                    }

                break;

            case Token::T_STRING:

                if ($isProcedure == false && $isCustomFunction == false){
                    $code[] = $getLine('10000000', false, $debugMsg . 'value=' . $node['value'] . ' (first)');
                    $code[] = $getLine('01000000', false, $debugMsg);
                    $code[] = $getLine('10000000', false, $debugMsg);
                    $code[] = $getLine('02000000', false, $debugMsg . '(last)');
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
                            $code[] = $getLine('10000000', false, $debugMsg . ' (header read)');
                            $code[] = $getLine('01000000', false, $debugMsg . 'value=' . $node['value']);
                        }

                        $debugMsg = sprintf('[T_FUNCTION] finalize: header %s ', $mappedTo['type']);

                        if ($mappedTo['type'] == 'level_var stringarray'){
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('02000000', false, $debugMsg);
                        }

                        if ($mappedTo['type'] == 'stringarray'){
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('02000000', false, $debugMsg);
                        }



                        break;

                    case 'script':
                        $debugMsg = sprintf('[T_FUNCTION] finalize: script %s ', $mappedTo['type']);

                        switch ($mappedTo['type']) {

                            case 'entityptr':
                            case 'vec3d':
                            case 'integer':
                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('01000000', false, $debugMsg);
                                break;

                            case 'customFunction':
                                $code[] = $getLine('12000000', false, $debugMsg);
                                $code[] = $getLine('02000000', false, $debugMsg);
                                break;

                            case 'stringarray':
                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('01000000', false, $debugMsg);

                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('02000000', false, $debugMsg);
                                break;

                            case 'procedure':
                                $debugMsg = sprintf('[T_FUNCTION] finalize: procedure %s ', $mappedTo['valueType']);

                                switch ($mappedTo['valueType']){
                                    case 'string':
                                        $code[] = $getLine('12000000', false, $debugMsg);
                                        $code[] = $getLine('02000000', false, $debugMsg);

                                        $code[] = $getLine('00000000', false, $debugMsg); // 0 always ?

                                        $code[] = $getLine('10000000', false, $debugMsg);
                                        $code[] = $getLine('01000000', false, $debugMsg);

                                        $code[] = $getLine('10000000', false, $debugMsg);
                                        $code[] = $getLine('02000000', false, $debugMsg);
                                        break;
                                    case 'real':
                                        $code[] = $getLine('10000000', false, $debugMsg);
                                        $code[] = $getLine('01000000', false, $debugMsg);
                                        break;

                                    default:
                                        throw new \Exception($mappedTo['valueType'] . " Not implemented!");
                                        break;

                                }

                                break;
                            case 'real':
                                if ($writeDebug == false){
                                    $code[] = $getLine('10000000', false, $debugMsg);
                                    $code[] = $getLine('01000000', false, $debugMsg);
                                }
                                break;
                            case 'constant':
                                $code[] = $getLine('10000000', false, $debugMsg);
                                $code[] = $getLine('01000000', false, $debugMsg);

                                if ($mappedTo['valueType'] == "string"){
                                    $code[] = $getLine('10000000', false, $debugMsg . ' (string)');
                                    $code[] = $getLine('02000000', false, $debugMsg . ' (string)');
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
        $code = [  ];

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
            case Token::T_INT:
                $code[] = $getLine($this->getFunction('WriteDebugInteger')['offset']);
                break;
            case Token::T_STRING:
                $code[] = $getLine($this->getFunction('WriteDebugString')['offset']);
                break;
            case Token::T_VARIABLE:

                $mapping = T_VARIABLE::getMapping($param, $data);

                switch ($mapping['type']){
                    case 'real':
                        $code[] = $getLine($this->getFunction('WriteDebugReal')['offset']);
                        break;
                    case 'stringarray':
                        $code[] = $getLine($this->getFunction('WriteDebugString')['offset']);
                        break;
                    case 'integer':
                    case 'game_var integer':
                        $code[] = $getLine($this->getFunction('WriteDebugInteger')['offset']);
                        break;
                    case 'level_var integer':
                        $code[] = $getLine($this->getFunction('WriteDebugLevelVarInteger')['offset']);
                        break;
                    case 'object':
                        $code[] = $getLine($this->getFunction('WriteDebugObject')['offset']);
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

                switch ($function['return']){
                    case 'String':
                        $code[] = $getLine($this->getFunction('WriteDebugString')['offset']);
                        break;
                    case 'Integer':
                        $code[] = $getLine($this->getFunction('WriteDebugInteger')['offset']);
                        break;
                    case 'Real':
                        $code[] = $getLine($this->getFunction('WriteDebugReal')['offset']);
                        break;
                    default:
                        throw new \Exception(sprintf('T_FUNCTION: Return type %s is unknown', $function['return']));
                        break;
                }

                break;
            default:
                throw new \Exception(sprintf('T_FUNCTION: Param type %s is unknown', $param['type']));
                break;
        }


        // the writedebug call has a secret additional call, a flush command
        if (!isset($node['last']) || $node['last'] === true) {
            $code[] = $getLine($this->getFunction('WriteDebugFlush')['offset']);
        }
//
//        foreach ($code as &$line) {
//            $line->debug = sprintf('[T_FUNCTION] handleWriteDebugCall');
//        }


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

                    $resultCode = $emitter( $mathValue );
                    foreach ($resultCode as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }

                    $debugMsg = sprintf('[T_FUNCTION] map: addition %s', $mathValue['value']);
                    $code[] = $getLine('0f000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);


                    $code[] = $getLine('31000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);

                    $code[] = $getLine('10000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);

                    $skipNext = true;
                }else if ($param['type'] == Token::T_SUBSTRACTION){
                    throw new \Exception('T_SUBSTRACTION not iplemented');
                }else if ($param['type'] == Token::T_MULTIPLY){

                    $mathValue = $node['params'][$index + 1];

                    $resultCode = $emitter( $mathValue );
                    foreach ($resultCode as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }

                    $debugMsg = sprintf('[T_FUNCTION] map: subtraction %s', $mathValue['value']);
                    $code[] = $getLine('0f000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);


                    $code[] = $getLine('35000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);

                    $code[] = $getLine('10000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);

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
                    $debugMsg = sprintf('[T_FUNCTION] map: negative float %s', $param['value']);

                    $code[] = $getLine('4f000000', false, $debugMsg);
                    $code[] = $getLine('32000000', false, $debugMsg);
                    $code[] = $getLine('09000000', false, $debugMsg);
                    $code[] = $getLine('04000000', false, $debugMsg);
                    $code[] = $getLine('10000000', false, $debugMsg);
                    $code[] = $getLine('01000000', false, $debugMsg);
                }


                /**
                 * when a function want a float but receive a int instead
                 * we need to tell the engine to convert the int to float
                 */
                if (
                    count($forceFloatOrder) > 0 &&
                    $param['type'] == Token::T_INT
                ) {
                    if (count($forceFloatOrder)){
                        if ($forceFloatOrder[$index] === true){
                            $debugMsg = sprintf('[T_FUNCTION] map: convert int to float %s', $param['value']);

                            $code[] = $getLine('4d000000', false, $debugMsg);
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);

                        }
                    }
                }

                if ($param['type'] == Token::T_VARIABLE){
                    $mapping = T_VARIABLE::getMapping($param, $data);

                    if ($mapping['type'] == "constant") {
//                        $code[] = $getLine('hier');

                    }else if ($mapping['type'] == "customFunction"){
                        $debugMsg = sprintf('[T_FUNCTION] map: customFunction %s', $param['value']);

//                        if ($mapping['valueType'] == "string"){
                            $code[] = $getLine('00000000', false, $debugMsg); //maybe argument position ?
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('01000000', false, $debugMsg);
                            $code[] = $getLine('10000000', false, $debugMsg);
                            $code[] = $getLine('02000000', false, $debugMsg);

//                        }
                    }


                }

            }
        }

        /**
         * Translate function call
         */
        try{
            $function = $this->getFunction($node['value']);

        }catch (\Exception $e){

            if ($isProcedure || $isCustomFunction) {
                $procedureOffset = $mappedToBlock['offset'];

                $debugMsg = sprintf('[T_FUNCTION] map: call procedure/customFunction %s', $node['value']);

                $code[] = $getLine('10000000', false, $debugMsg); //procedure
                $code[] = $getLine('04000000', false, $debugMsg); //procedure
                $code[] = $getLine('11000000', false, $debugMsg); //procedure
                $code[] = $getLine('02000000', false, $debugMsg); //procedure
                $code[] = $getLine('00000000', false, $debugMsg); //procedure
                $code[] = $getLine('32000000', false, $debugMsg); //procedure
                $code[] = $getLine('02000000', false, $debugMsg); //procedure
                $code[] = $getLine('1c000000', false, $debugMsg); //procedure
                $code[] = $getLine('10000000', false, $debugMsg); //procedure
                $code[] = $getLine('02000000', false, $debugMsg); //procedure
                $code[] = $getLine('39000000', false, $debugMsg); //procedure
                $code[] = $getLine(Helper::fromIntToHex($procedureOffset * 4), false, $debugMsg . ' (offset)'); //procedure offset

                return $code;
            }

            throw $e;
        }

        $debugMsg = sprintf('[T_FUNCTION] map: call function %s', $node['value']);

        $code[] = $getLine($function['offset'], false, $debugMsg);


        /**
         * when we are inside a nested call, tell the interpreter to return the current value
         */

        if (isset($node['nested']) && $node['nested'] === true){

            $functionNoReturn = array_merge(Manhunt2::$functionNoReturn, ManhuntDefault::$functionNoReturn);

            if (
                //not sure, maybe this is just a fix for a unknown bug
                !in_array(strtolower($node['value']), $functionNoReturn )
            ){

                $debugMsg = sprintf('[T_FUNCTION] map: call function return');

                $code[] = $getLine('10000000', false, $debugMsg);
                $code[] = $getLine('01000000', false, $debugMsg);

            }
        }

        $this->processArguments($node, $code, $getLine, $emitter);

        return $code;
    }

    private function processArguments($node, &$code, \Closure $getLine, \Closure $emitter){
        if (!isset($node['arguments'])) return;

        $debugMsg = 'processArguments ';

        foreach ($node['arguments'] as $index => $argument) {

            $code[] = $getLine('12000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);
            $code[] = $getLine(Helper::fromIntToHex($index), false, $debugMsg . ' index');
            $code[] = $getLine('10000000', false, $debugMsg);
            $code[] = $getLine('01000000', false, $debugMsg);

            $resultCode = $emitter( $argument );
            foreach ($resultCode as $line) {
                $line->debug = $debugMsg .  ' ' . $line->debug;
                $code[] = $line;
            }

            $code[] = $getLine('10000000', false, $debugMsg . ' return');
            $code[] = $getLine('01000000', false, $debugMsg . ' return');

            if ($index == 0){
                $code[] = $getLine('07030000', false, $debugMsg . ' A OFFSET HMM');
            }else{
                $code[] = $getLine('08030000', false, $debugMsg . ' A OFFSET HMM');

            }
        }
    }

}