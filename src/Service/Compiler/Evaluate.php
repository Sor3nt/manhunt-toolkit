<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Evaluate {


    /**
     * process commands
     *
     */
    static public function processString( $node, &$code, \Closure $getLine, $data ){

        // we have quotes around the string, come from the tokenizer
        $value = substr($node['value'], 1, -1);

        if (!isset($data['strings'][$value])){
            throw new \Exception(sprintf('Evaluate: string =>  %s is not in the map !', $value));
        }

        // when this is false, we are in precalc mode so we dont want to fetch the real value
        if ($data['calculateLineNumber']){
            $code[] = $getLine($data['strings'][$value]['offset']);
        }else{
            $code[] = $getLine("12345678");
        }
    }


    /**
     * Initialize commands
     *
     */

    static public function initializeParameterInteger( &$code, \Closure $getLine ){
        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
    }

    static public function initializeParameterString( &$code, \Closure $getLine ){
        $code[] = $getLine('12000000');
        $code[] = $getLine('02000000');
    }

    static public function initializeReadHeaderString( &$code, \Closure $getLine ){
        $code[] = $getLine('21000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }

    static public function initializeReadHeaderStringArray( &$code, \Closure $getLine ){
        $code[] = $getLine('21000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadHeaderBoolean( &$code, \Closure $getLine ){
        $code[] = $getLine('14000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadHeaderIntefer( &$code, \Closure $getLine ){
        $code[] = $getLine('13000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }

    static public function initializeReadLevelVar( &$code, \Closure $getLine ){
        $code[] = $getLine('1b000000');
    }

    static public function initializeReadScriptString( &$code, \Closure $getLine ){
        $code[] = $getLine('22000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }


    static public function initializeStatement( &$code, \Closure $getLine ){
        $code[] = $getLine('23000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('12000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }





    /**
     * Return commands
     */

    static public function returnResult( &$code, \Closure $getLine ){
        $code[] = $getLine('10000000');
        $code[] = $getLine('01000000');
    }

    static public function returnStringResult( &$code, \Closure $getLine ){
        $code[] = $getLine('10000000');
        $code[] = $getLine('02000000');
    }

    static public function returnConstantResult( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
    }

    static public function returnObjectResult( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('01000000');
    }

    static public function returnLevelVarResult( &$code, \Closure $getLine ){
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }

    //todo: das stimmt ggf nicht, das wird auch als init verwendet...
    static public function returnStringArrayResult( &$code, \Closure $getLine ){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
    }


    /**
     * Statement functions
     */

    static public function statementOperator( $node, &$code, \Closure $getLine ){

        switch ($node['type']){
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
            default:
                throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $node['type']));
                break;
        }
    }

    static public function setStatementFullCondition( &$code, \Closure $getLine ){
        $code[] = $getLine('33000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }

    static public function setStatementNot( &$code, \Closure $getLine ){
        $code[] = $getLine('29000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('01000000');
    }

    static public function setStatementAnd( &$code, \Closure $getLine ){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('25000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
    }

    static public function setStatementAddition( &$code, \Closure $getLine ){
        $code[] = $getLine('31000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }


    static public function setStatementSubstraction( &$code, \Closure $getLine ){
        $code[] = $getLine('33000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('01000000');
    }


    static public function setStatementOperator($node, &$code, \Closure $getLine ){
        self::returnConstantResult($code, $getLine);

        switch ($node['operator']){

            case Token::T_OR:
                $code[] = $getLine('27000000');
                break;
            case Token::T_AND:
                $code[] = $getLine('25000000');
                break;
            default:
                throw new \Exception(sprintf('Evaluate: setStatementOperator =>  %s is not a valid operator !', $node['operator']));
        }

        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
    }


    /**
     * assign functions
     */


    static public function assignToHeaderStringArray($offset, &$code, \Closure $getLine ){
        Evaluate::initializeParameterInteger($code, $getLine);

        $code[] = $getLine($offset);

        Evaluate::returnResult($code, $getLine);
    }

    static public function assignToScriptObject($offset, &$code, \Closure $getLine ){
        self::returnStringArrayResult( $code, $getLine);

        $code[] = $getLine($offset);

        self::returnObjectResult( $code, $getLine);
    }

    static public function assignToLevelVar($offset, &$code, \Closure $getLine ){
        $code[] = $getLine('1a000000');
        $code[] = $getLine('01000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('04000000');
    }

    static public function assignToScriptInteger($offset, &$code, \Closure $getLine ){
        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');
    }

    static public function assignToUnknownInteger($offset, &$code, \Closure $getLine ){
        $code[] = $getLine('16000000');
        $code[] = $getLine('04000000');

        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');
    }

    static public function assignToHeaderInteger($offset, &$code, \Closure $getLine ){
        $code[] = $getLine('11000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');

        // define the offset
        $code[] = $getLine($offset);

        $code[] = $getLine('01000000');

    }

    static public function assignToUnknownStringArray($mapped, &$code, \Closure $getLine ){
        Evaluate::initializeReadHeaderStringArray($code, $getLine);

        $code[] = $getLine($mapped['offset']);

        Evaluate::returnStringArrayResult($code, $getLine);


        $code[] = $getLine(Helper::fromIntToHex($mapped['size']));

        $code[] = $getLine('10000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('10000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine('48000000');


    }


    /**
     * Other functions
     */


    static public function processNumeric( $node, &$code, $data, \Closure $getLine, \Closure $emitter ){

        self::initializeParameterInteger($code, $getLine);

        $resultCode = $emitter( $node );
        foreach ($resultCode as $line) {
            $code[] = $line;
        }

        if (isset($data['conditionVariable']) && $data['conditionVariable']['type'] == Token::T_VARIABLE){

            $mapped = self::getVariableMap(
                $data['conditionVariable']['value'],
                $code,
                $data,
                $getLine,
                $emitter
            );

            if ($mapped === false) return false;

            switch ($mapped['section']) {

                case 'level_var':
                    self::returnConstantResult($code, $getLine);
                    break;

                case 'header':

                    switch ($mapped['type']) {

                        case 'boolean':

                            //while vs if ...

                            if ($data['customData']['isWhile']){
                                Evaluate::returnResult($code, $getLine);

                            }else{
                                Evaluate::returnConstantResult($code, $getLine);
                            }

                            break;
                        case 'integer':
                            Evaluate::returnResult($code, $getLine);
                            break;
                        default:
                            throw new \Exception(sprintf("handling incomplete for type %s", $mapped['type']));
                    }

                    break;

                default:
                    throw new \Exception(sprintf("handling incomplete for section %s", $mapped['section']));
            }

        }else{
            // we have here a integer/boolean/float/this value
            Evaluate::returnConstantResult($code, $getLine);
        }

        return true;
    }


    static public function processVariable( $node, &$code, $data, \Closure $getLine, \Closure $emitter ){

        $mapped = self::getVariableMap(
            $node['value'],
            $code,
            $data,
            $getLine,
            $emitter
        );

        if ($mapped === false) return false;

        switch ($mapped['section']){

            //todo: das sollte es nicht geben, gehört zur header section
            case 'level_var':
                Evaluate::initializeReadLevelVar($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                Evaluate::returnLevelVarResult($code, $getLine);

                break;
            case 'constant':
                self::initializeParameterInteger($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                self::returnConstantResult($code, $getLine);
                break;

            case 'header':

                switch ($mapped['type']){


                    case 'level_var integer':
                        self::initializeReadLevelVar($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);

                        self::returnLevelVarResult($code, $getLine);
                        break;

                    case 'integer':
                        self::initializeReadHeaderIntefer($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);
                        break;
                    case 'boolean':
                        self::initializeReadHeaderBoolean($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);
                        break;
                    default:
                        throw new \Exception(sprintf("Unknown header type %s", $mapped['type']));
                }

                break;

            case 'script':

                switch ($mapped['type']){
                    case 'integer':
                        self::initializeReadHeaderIntefer($code, $getLine);

                        // define the offset
                        $code[] = $getLine($mapped['offset']);

                        break;
                    case 'object':

                        // i dont know, object read init ?!
                        $code[] = $getLine('0f000000');
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('0f000000');
                        $code[] = $getLine('04000000');
                        $code[] = $getLine('44000000');

                        // read from script var
                        Evaluate::initializeReadScriptString($code, $getLine);
                        $code[] = $getLine($mapped['object']['offset']);

                        //nested call return result
                        Evaluate::returnResult($code, $getLine);

                        $code[] = $getLine('0f000000');
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('32000000');
                        $code[] = $getLine('01000000');

                        $code[] = $getLine($mapped['offset']);

                        //nested call return result
                        Evaluate::returnResult($code, $getLine);

                        $code[] = $getLine('0f000000');
                        $code[] = $getLine('02000000');
                        $code[] = $getLine('18000000');
                        $code[] = $getLine('01000000');
                        $code[] = $getLine('04000000');
                        $code[] = $getLine('02000000');

                        Evaluate::returnResult($code, $getLine);

                        break;
                    default:
                        throw new \Exception(sprintf("Unknown Script type %s", $mapped['type']));
                }

                break;

            default:
                throw new \Exception(sprintf("Unknown section type %s", $mapped['section']));
        }


        return true;
    }

    static public function getVariableMap( $value, &$code, $data, \Closure $getLine, \Closure $emitter ){

        if (isset(Manhunt2::$functions[ strtolower($value) ])) {


            // mismatch, some function has no params and looks loke variables
            // just redirect to the function handler
            $result = $emitter( [
                'type' => Token::T_FUNCTION,
                'value' => $value
            ] );

            foreach ($result as $item) {
                $code[] = $item;
            }

            return false;
        }else if (isset(Manhunt2::$constants[ $value ])) {
            $mapped = Manhunt2::$constants[ $value ];
            $mapped['section'] = "constant";

        }else if (isset(Manhunt2::$levelVarBoolean[ $value ])) {
            $mapped = Manhunt2::$levelVarBoolean[ $value ];
            $mapped['section'] = "level_var";

        }else if (isset($data['variables'][ $value ])){
            $mapped = $data['variables'][ $value ];

        }else{

            // we have a object notation here
            if (strpos($value, '.') !== false){
                $mapped = self::getObjectToAttributeSplit($data);
            }else{
                throw new \Exception(sprintf("T_FUNCTION: (numeric) unable to find variable offset for %s", $data['conditionVariable']['value']));
            }
        }

        return $mapped;

    }


    static public function getObjectToAttributeSplit( $data ){
        list($originalObject, $attribute) = explode('.', $data['conditionVariable']['value']);
        $originalMap = $data['variables'][$originalObject];

        if ($originalMap['type'] == "vec3d"){

            $mapped = [
                'section' => $originalMap['section'],
                'type' => 'object',
                'object' => $originalMap,
                'size' => 4
            ];

            switch ($attribute){
                case 'x':
                    break;
                case 'y':
                    $mapped['offset'] = '04000000';
                    break;
                case 'z':
                    $mapped['offset'] = '08000000';
                    break;
            }

            return $mapped;

        }else{
            throw new \Exception(sprintf("unknown object type %s", $originalMap['type']));
        }
    }

    static public function negateLastValue( &$code, \Closure $getLine ){
        $code[] = $getLine('4f000000');
        $code[] = $getLine('32000000');
        $code[] = $getLine('09000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('10000000');
        $code[] = $getLine('01000000');
    }



}