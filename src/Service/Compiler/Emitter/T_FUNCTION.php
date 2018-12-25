<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_FUNCTION {


    private $blockOffsets;
    private $combinedVariables;

    public function __construct( $customData )
    {
        $this->blockOffsets = $customData['blockOffsets'];
        $this->combinedVariables = $customData['combinedVariables'];
    }

    public function finalize( $node, $data, &$code, \Closure $getLine, $writeDebug = false, $isProcedure = false, $isCustomFunction = false ){


        switch ($node['type']){
            case Token::T_FLOAT:
            case Token::T_FALSE:
            case Token::T_TRUE:
            case Token::T_SELF:
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
            break;


            case Token::T_ADDITION:
            case Token::T_FUNCTION:
                break;

            case Token::T_INT:

                    if ($node['value'] >= 0){
                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');
                    }else{
                        $code[] = $getLine('2a000000');
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');
                    }

                break;

            case Token::T_STRING:

                if ($isProcedure == false){
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('02000000');
                }
                break;

            case Token::T_VARIABLE:
                $mappedTo = T_VARIABLE::getMapping(
                    $node,
                    null,
                    $data
                );


                switch ($mappedTo['section']) {
                    case 'header':

                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');

                        if ($mappedTo['type'] == 'stringarray'){
                            $code[] = $getLine('10000000');
                            $code[] = $getLine('02000000');
                        }

                        break;
                    case 'script':


                        switch ($mappedTo['type']) {

                            case 'entityptr':
                            case 'vec3d':
                            case 'integer':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');
                                break;

                            case 'stringarray':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                $code[] = $getLine('10000000');
                                $code[] = $getLine('02000000');
                                break;

                            case 'procedure':

                                switch ($mappedTo['valueType']){
                                    case 'string':
                                        $code[] = $getLine('12000000');
                                        $code[] = $getLine('02000000');

                                        $code[] = $getLine('00000000'); // 0 always ?

                                        $code[] = $getLine('10000000');
                                        $code[] = $getLine('01000000');

                                        $code[] = $getLine('10000000');
                                        $code[] = $getLine('02000000');
                                        break;
                                    case 'real':
                                        $code[] = $getLine('10000000');
                                        $code[] = $getLine('01000000');
                                        break;

                                    default:
                                        throw new \Exception($mappedTo['valueType'] . " Not implemented!");
                                        break;

                                }

                                break;
                            case 'real':
                                if ($writeDebug == false){
                                    $code[] = $getLine('10000000');
                                    $code[] = $getLine('01000000');
                                }
                                break;
                            case 'constant':
                                $code[] = $getLine('10000000');
                                $code[] = $getLine('01000000');

                                if ($mappedTo['valueType'] == "string"){
                                    $code[] = $getLine('10000000');
                                    $code[] = $getLine('02000000');
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

        $resultCode = $emitter( $param );
        foreach ($resultCode as $line) {
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

                $mapping = T_VARIABLE::getMapping($param, $emitter, $data);

                switch ($mapping['type']){
                    case 'real':
                        $code[] = $getLine($this->getFunction('WriteDebugReal')['offset']);
                        break;
                    case 'stringarray':
                        $code[] = $getLine($this->getFunction('WriteDebugString')['offset']);
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
                        var_dump($function);
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


        return $code;
    }

    public function getForceFloat( $functioName ){


        $functioName = strtolower($functioName);

        $functionForceFloar = Manhunt2::$functionForceFloar;
        if (GAME == "mh1") $functionForceFloar = Manhunt::$functionForceFloar;

        $functionForceFloar = array_merge($functionForceFloar, ManhuntDefault::$functionForceFloar);

        if (isset( $functionForceFloar[$functioName] )){
            return $functionForceFloar[$functioName];
        }

        return [];
    }

    public function getFunction($functionName ){

        $functionName = strtolower($functionName);

        if (
            !isset($this->combinedVariables[$functionName])
        ){
            throw new \Exception(sprintf('Unknown function %s', $functionName));
        }

        return $this->combinedVariables[$functionName];
    }

    public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [ ];

        /**
         * sometimes is the mapping not correct, validate it
         */
        try {
            T_VARIABLE::getMapping($node, null, $data);
            return $emitter([
                'type' => Token::T_VARIABLE,
                'value' => $node['value']
            ]);
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
                        $code[] = $line;
                    }

                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');


                    $code[] = $getLine('31000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');

                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');

                    $skipNext = true;
                }else if ($param['type'] == Token::T_SUBSTRACTION){
                    throw new \Exception('T_SUBSTRACTION not iplemented');


                }else{
                    $resultCode = $emitter( $param, true, [
                        'isProcedure' => $isProcedure,
                        'isCustomFunction' => $isCustomFunction
                    ]);

                    foreach ($resultCode as $line) {
                        $code[] = $line;
                    }

                }

                $this->finalize($param, $data, $code, $getLine, false, $isProcedure, $isCustomFunction);

                /**
                 * When the input value is a negative float
                 * we assign the positive value and negate them with this sequence
                 */
                if (
                    ( $param['type'] == Token::T_FLOAT) &&
                    $param['value'] < 0
                ) {

                    $code[] = $getLine('4f000000');
                    $code[] = $getLine('32000000');
                    $code[] = $getLine('09000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('10000000');
                    $code[] = $getLine('01000000');
                }


                if (
                    count($forceFloatOrder) > 0 &&
                    $param['type'] == Token::T_INT
                ) {


                    if (count($forceFloatOrder)){
                        if ($forceFloatOrder[$index] === true){
                            $code[] = $getLine('4d000000');
                            $code[] = $getLine('10000000');
                            $code[] = $getLine('01000000');

                        }
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

            if ($isProcedure) {
                $procedureOffset = $mappedToBlock['offset'];

                $code[] = $getLine('10000000'); //procedure
                $code[] = $getLine('04000000'); //procedure
                $code[] = $getLine('11000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('00000000'); //procedure
                $code[] = $getLine('32000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('1c000000'); //procedure
                $code[] = $getLine('10000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('39000000'); //procedure
                $code[] = $getLine(Helper::fromIntToHex($procedureOffset * 4)); //procedure offset

                return $code;

            }else if ($isCustomFunction){

                $procedureOffset = $mappedToBlock['offset'];

                $code[] = $getLine('10000000'); //procedure
                $code[] = $getLine('04000000'); //procedure
                $code[] = $getLine('11000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('00000000'); //procedure
                $code[] = $getLine('32000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('1c000000'); //procedure
                $code[] = $getLine('10000000'); //procedure
                $code[] = $getLine('02000000'); //procedure
                $code[] = $getLine('39000000'); //procedure
                $code[] = $getLine( Helper::fromIntToHex($procedureOffset * 4 ) ); // customFunction offset

                return $code;
            }

            throw $e;
        }

        $code[] = $getLine($function['offset']);


        /**
         * when we are inside a nested call, tell the interpreter to return the current value
         */

        if (isset($node['nested']) && $node['nested'] === true){

            $functionNoReturn = Manhunt2::$functionNoReturn;
            if (GAME == "mh1") $functionNoReturn = Manhunt::$functionNoReturn;

            $functionNoReturn = array_merge($functionNoReturn, ManhuntDefault::$functionNoReturn);

            if (
                //not sure, maybe this is just a fix for a unknown bug
                !in_array(strtolower($node['value']), $functionNoReturn )
            ){

                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

            }
        }

        return $code;
    }

}