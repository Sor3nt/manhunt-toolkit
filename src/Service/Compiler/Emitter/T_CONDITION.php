<?php
namespace App\Service\Compiler\Emitter;


use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\Token;

class T_CONDITION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $code = [];

        if (count($node['body']) == 3) {
            if ($node['isNot']){
                throw new \Exception('T_CONDITION: The expression NOT can not be combined with an operator!');
            }

            list($variable, $operation, $value) = $node['body'];


            $result = self::parseValue($variable, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::returnResult($code, $getLine);

            $result = self::parseValue($value, $getLine, $emitter, array_merge($data, [ 'conditionVariable' => $variable]));
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::initializeStatement($code, $getLine);
            Evaluate::statementOperator($operation, $code, $getLine);

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);

        }else if (count($node['body']) == 1){

            $result = self::parseValue(current($node['body']), $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            if ($node['isNot']){
                Evaluate::setStatementNot($code, $getLine);
            }

        }else if (count($node['body']) == 4){

            list($variable, $operation, $value, $addon) = $node['body'];


            $result = self::parseValue($variable, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            Evaluate::returnResult($code, $getLine);

            $result = self::parseValue($value, $getLine, $emitter, array_merge($data, [ 'conditionVariable' => $variable]));
            foreach ($result as $item) {
                $code[] = $item;
            }

            $result = self::parseValue($addon, $getLine, $emitter, $data);
            foreach ($result as $item) {
                $code[] = $item;
            }

            //TODO: OR verbauen
            Evaluate::setStatementAnd($code, $getLine);

            Evaluate::initializeStatement($code, $getLine);
            Evaluate::statementOperator($operation, $code, $getLine);

            $lastLine = end($code)->lineNumber + 4;

            // line offset for the IF start (or so)
            $code[] = $getLine( Helper::fromIntToHex($lastLine * 4) );

            Evaluate::setStatementFullCondition($code, $getLine);

        }


        return $code;
    }




    static public function parseValue( $node, \Closure $getLine, \Closure $emitter, $data){

        $code = [];


        if ($node['type'] == Token::T_FUNCTION){

            $result = $emitter($node);
            foreach ($result as $item) {
                $code[] = $item;
            }

            return $code;

        /**
         * Define for INT, FLOAT and STRING a construct and destruct sequence
         */
        }else if (
            $node['type'] == Token::T_INT ||
            $node['type'] == Token::T_FLOAT ||
            $node['type'] == Token::T_NIL ||
            $node['type'] == Token::T_TRUE ||
            $node['type'] == Token::T_FALSE ||
            $node['type'] == Token::T_SELF
        ) {


            Evaluate::initializeParameterInteger($code, $getLine);

            $resultCode = $emitter( $node );

            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            if (isset($data['conditionVariable']) && $data['conditionVariable']['type'] == Token::T_VARIABLE){


                if (isset(Manhunt2::$functions[ strtolower($data['conditionVariable']['value']) ])) {
                    // mismatch, some function has no params and looks loke variables
                    // just redirect to the function handler
                    return $emitter( [
                        'type' => Token::T_FUNCTION,
                        'value' => $data['conditionVariable']['value']
                    ] );

                }else if (isset(Manhunt2::$constants[ $data['conditionVariable']['value'] ])) {
                    $mapped = Manhunt2::$constants[$data['conditionVariable']['value']];
                    $mapped['section'] = "constant";

                }else if (isset(Manhunt2::$levelVarBoolean[ $data['conditionVariable']['value'] ])) {
                    $mapped = Manhunt2::$levelVarBoolean[$data['conditionVariable']['value']];
                    $mapped['section'] = "level_var";

                }else if (isset($data['variables'][$data['conditionVariable']['value']])){
                    $mapped = $data['variables'][$data['conditionVariable']['value']];

                }else{

                    // we have a object notation here
                    if (strpos($data['conditionVariable']['value'], '.') !== false){
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

                        }else{
                            throw new \Exception(sprintf("T_CONDITION: T_FUNCTION => unknown object type %s", $originalMap['type']));
                        }
                    }else{

                        throw new \Exception(sprintf("T_FUNCTION: (numeric) unable to find variable offset for %s", $data['conditionVariable']['value']));
                    }
                }

                if (
                    $mapped['section'] == "header" &&

                    //todo kann sein das des nicht stimmt...
                    $mapped['type'] == "integer"
                ) {

                    Evaluate::returnResult($code, $getLine);
                }else if (

                    $mapped['section'] == "header" &&
                    $mapped['type'] == "boolean"
                ){

                    //while vs if ...

                    if ($data['customData']['isWhile']){
                        $code[] = $getLine('10000000');
                        $code[] = $getLine('01000000');

                    }else{
                        $code[] = $getLine('0f000000');
                        $code[] = $getLine('04000000');
                    }

                }else{
                    $code[] = $getLine('0f000000');
                    $code[] = $getLine('04000000');

                }

            }else{
                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');

            }

            return $code;

        }else if ($node['type'] == Token::T_VARIABLE){

            if (isset(Manhunt2::$functions[ strtolower($node['value']) ])) {
                // mismatch, some function has no params and looks loke variables
                // just redirect to the function handler
                return $emitter( [
                    'type' => Token::T_FUNCTION,
                    'value' => $node['value']
                ] );

            }else if (isset(Manhunt2::$constants[ $node['value'] ])) {
                $mapped = Manhunt2::$constants[$node['value']];
                $mapped['section'] = "constant";

            }else if (isset(Manhunt2::$levelVarBoolean[ $node['value'] ])) {
                $mapped = Manhunt2::$levelVarBoolean[$node['value']];
                $mapped['section'] = "level_var";

            }else if (isset($data['variables'][$node['value']])){

                $mapped = $data['variables'][$node['value']];


            }else{

                // we have a object notation here
                if (strpos($node['value'], '.') !== false){
                    list($originalObject, $attribute) = explode('.', $node['value']);
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

                    }else{
                        throw new \Exception(sprintf("T_CONDITION: T_FUNCTION => unknown object type %s", $originalMap['type']));
                    }
                }else{
                    throw new \Exception(sprintf("T_CONDITION: T_FUNCTION => unable to find variable offset for %s", $node['value']));

                }

            }

            // initialize string
            if ($mapped['section'] == "constant") {
                Evaluate::initializeParameterInteger($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                Evaluate::returnConstantResult($code, $getLine);

            }else if (
                $mapped['section'] == "header" && $mapped['type'] == "boolean"
            ) {
                Evaluate::initializeReadHeaderBoolean($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);


            }else if (
                $mapped['section'] == "body" && $mapped['type'] == "boolean"
            ){

                Evaluate::initializeReadHeaderBoolean($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

            }else if (
                $mapped['section'] == "script" &&
                isset($mapped['type']) && $mapped['type'] == "object"
            ) {

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




            }else if ($mapped['section'] == "level_var") {
                Evaluate::initializeReadLevelVar($code, $getLine);

                // define the offset
                $code[] = $getLine($mapped['offset']);

                Evaluate::returnLevelVarResult($code, $getLine);
            }else{
                var_dump($mapped);

                throw new \Exception(sprintf("T_CONDITION: T_FUNCTION => handling incomplete for %s", $mapped['section']));

            }

            return $code;

        }else{
            var_dump($node);
            throw new \Exception(sprintf('T_CONDITION: %s is not supported', $node['type']));
        }
    }


}