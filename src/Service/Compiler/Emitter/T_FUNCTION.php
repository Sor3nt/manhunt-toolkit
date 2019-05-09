<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
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

    public function finalize( $node, $data, &$code, \Closure $getLine){


        if ($node['type'] == Token::T_VARIABLE){
            $mappedTo = T_VARIABLE::getMapping(
                $node,
                $data
            );

            switch ($mappedTo['objectType']) {

                case 'customFunction':

                    $code[] = $getLine('12000000', false, '');
                    $code[] = $getLine('02000000', false, '');
                    $code[] = $getLine('00000000', false, '');

                    Evaluate::stringReturn($code, $getLine);
                    break;
                case Token::T_INT:
                    Evaluate::regularReturn($code, $getLine);
                    break;
            }
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

        foreach ($emitter( $param, true, ['fromFunction' => true] ) as $line){
            $line->debug = $debugMsg . ' ' . $line->debug;
            $code[] = $line;
        }


        $this->finalize($param, $data, $code, $getLine);

        /**
         * generate the needed function call
         */
        switch ($param['type']){

            case Token::T_INT:
                $code[] = $getLine($this->getFunction('writedebuginteger')['offset']);
                break;
            case Token::T_STRING:
                $code[] = $getLine($this->getFunction('writedebugstring')['offset']);
                break;
            case Token::T_VARIABLE:

                $mapping = T_VARIABLE::getMapping($param, $data);

                switch ($mapping['type']){
                    case Token::T_REAL:
                    case Token::T_INT:
                    case 'object':
                    case Token::T_STRING_ARRAY:
                        $code[] = $getLine($this->getFunction('writedebug' . $mapping['type'] )['offset']);
                        break;
                    case 'game_var integer':
                        $code[] = $getLine($this->getFunction('writedebuginteger' )['offset']);
                        break;

                    case 'procedure':
                        $code[] = $getLine($this->getFunction('writedebug')['offset']);
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

                $code[] = $getLine($this->getFunction('writedebug' . $function['return'] )['offset']);


                break;
            default:
                throw new \Exception(sprintf('T_FUNCTION: Param type %s is unknown', $param['type']));
                break;
        }

        // the writedebug call has a secret additional call, a flush command
        if (!isset($node['last']) || $node['last'] === true) {
            $code[] = $getLine($this->getFunction('writedebugflush')['offset']);
        }

        return $code;
    }

    public function getForceFloat( $functionName ){

        $functionForceFloat = ManhuntDefault::$functionForceFloar;
        if (isset( $functionForceFloat[$functionName] )) return $functionForceFloat[$functionName];

        return [];
    }

    public function getFunction($functionName ){

        if ( !isset($this->functions[$functionName]) ){
            throw new \Exception(sprintf('Unknown function %s', $functionName));
        }

        return $this->functions[$functionName];
    }

    public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = '[T_FUNCTION] map ';
        $code = [ ];

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
        }else{
            try {
                return $emitter([
                    'type' => Token::T_VARIABLE,
                    'value' => $node['value']
                ]);

            }catch(\Exception $e){

                if (strpos($e->getMessage(), 'unable to find variable') == false){
                    throw $e;
                }
            }
        }


        /**
         * Special WriteDebug handling
         */
        if ($node['value'] == "writedebug"){
            return $this->handleWriteDebugCall($node, $getLine, $emitter, $data);
        }

        $forceFloatOrder = $this->getForceFloat($node['value']);


        if (isset($node['params']) && count($node['params'])){

            foreach ($node['params'] as $index => $param) {

                if (
                    $param['type'] == Token::T_SUBSTRACTION ||
                    $param['type'] == Token::T_ADDITION ||
                    $param['type'] == Token::T_MULTIPLY
                ){
                    $mathValue = $node['params'][$index + 1];

                    Evaluate::emit($mathValue, $code, $emitter, $debugMsg);


                    Evaluate::setIntMathOperator( $param['type'], $code, $getLine);

                    Evaluate::regularReturn($code, $getLine);

                    //we need to break here because we read a future index ($index+1)
                    //since math calc has only 2 params, we need to stop here
                    break;

                }else{
                    $resultCode = $emitter( $param, true, [
                        'fromFunction' => true,
                        'isProcedure' => $isProcedure,
                        'isCustomFunction' => $isCustomFunction
                    ]);

                    foreach ($resultCode as $line) {
                        $line->debug = $debugMsg .  ' ' . $line->debug;
                        $code[] = $line;
                    }

                    $this->finalize($param, $data, $code, $getLine);



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

//                        if($data['game'] == MHT::GAME_MANHUNT_2){

                            Evaluate::int2float($code, $getLine);
//                        }
                    }
                }


            }
        }

        if ($isProcedure || $isCustomFunction) {
            $procedureOffset = $mappedToBlock['offset'];

            Evaluate::gotoBlock($node['value'], $procedureOffset * 4, $code, $getLine);

            return $code;
        }

        /**
         * Translate function call
         */
        $function = $this->getFunction($node['value']);

        $debugMsg = sprintf('[T_FUNCTION] map: call function %s', $node['value']);

        $code[] = $getLine($function['offset'], false, $debugMsg, true);


        /**
         * we are inside a nested call, tell the interpreter to return the current value
         */
        if (isset($node['nested']) && $node['nested'] === true){

            /**
             * Mystery: any function who return vec3d or a string do not need a return code.
             */
            if (
                !isset($function['return']) || (
                    $function['return'] != Token::T_VEC3D &&
                    $function['return'] != Token::T_STRING
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

            Evaluate::emit($argument, $code, $emitter, $debugMsg);


            Evaluate::regularReturn($code, $getLine);

            if ($index == 0){
                $code[] = $getLine('07030000', false, $debugMsg . ' A OFFSET HMM');
            }else{
                $code[] = $getLine('08030000', false, $debugMsg . ' A OFFSET HMM');

            }
        }

        $code[] = $getLine('0e030000', false, $debugMsg . ' END?');

    }

}