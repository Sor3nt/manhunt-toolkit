<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class EvaluateAssign {

    static public function process( $node, &$code, \Closure $getLine,\Closure $emitter, $data ){
        /**
         * when the variable is not found, check if its an object
         */

        $isObject = false;
        if (!isset($data['variables'][$node['value']])){

            if (strpos($node['value'], '.') !== false){

                $isObject = true;

                $mapped = Evaluate::getObjectToAttributeSplit($node['value'], $data);
            }else{
                throw new \Exception(sprintf('T_ASSIGN: unable to detect variable: %s', $node['value']));
            }
        }else{
            $mapped = $data['variables'][$node['value']];
        }


        $leftHand = $node['body'][0];

        //HACK

        //when we have a type usage, we have no variable entry
        //so the compiler think its a function...
        if ($leftHand['type'] == Token::T_FUNCTION ){
            $stateVar = str_replace('level_var ', '',$mapped['type']);

            if (isset($data['types'][$stateVar])){
                $leftHand['target'] = $stateVar;
                $leftHand['type'] = Token::T_VARIABLE;
            }
        }


        if ($mapped['type'] == "vec3d"){
            $code[] = $getLine('22000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine($mapped['offset']);

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

        }else if($isObject){
            $code[] = $getLine('22000000');
            $code[] = $getLine('04000000');
            $code[] = $getLine('01000000');
            $code[] = $getLine($mapped['object']['offset']);


            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            if ($mapped['offset'] != $mapped['object']['offset']){
                $code[] = $getLine('0f000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine('32000000');
                $code[] = $getLine('01000000');

                $code[] = $getLine($mapped['offset']);

            }

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

        }

        /**
         * Evaluate the node
         */
        $resultCode = $emitter($leftHand);
        foreach ($resultCode as $line) {
            $code[] = $line;
        }

        //we do here some math...
        if (isset($node['body'][1])){

            $rightHand = $node['body'][2];
            $operator = $node['body'][1];

            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $resultCode = $emitter($rightHand);
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            $code[] = $getLine('0f000000');
            $code[] = $getLine('04000000');

            if ($operator['type'] == Token::T_ADDITION) {
                Evaluate::setStatementAddition($code, $getLine);
            }else if ($operator['type'] == Token::T_SUBSTRACTION){
                Evaluate::setStatementSubstraction($code, $getLine);
            }else{
                throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath operator not supported: %s', $operator['type']));

            }
        }

        /*
         * Assign TO variable handling
         *
         * wee need here to difference between sindlge param or multi param
         * mutli params are always math operators and need other return codes
         */
        if (isset($node['body'][1]) == false){
            switch ($mapped['section']) {

                case 'header':

                    switch (strtolower($mapped['type'])) {
                        case 'boolean':
                        case 'integer':
                            self::toHeaderBoolean( $mapped['offset'], $code, $getLine);
                            break;
//                            self::toHeaderInteger( $mapped['offset'], $code, $getLine);
//                            break;

                        case 'level_var boolean':
                            self::toHeaderLevelVarBoolean( $mapped['offset'], $code, $getLine);
                            break;
                        case 'level_var tlevelstate':
                            self::toHeaderTLevelState( $mapped['offset'], $code, $getLine);
                            break;
                        case 'stringarray':
                            self::toHeaderStringArray( $mapped['offset'], $mapped['size'], $code, $getLine);
                            break;
                        default:
                            var_dump($mapped);
                            throw new \Exception("Not implemented!");
                    }

                    break;
                case 'script':
                    switch (strtolower($mapped['type'])) {
                        case 'entityptr':
                            self::toScriptEntityPtr( $mapped['offset'], $code, $getLine);

                            break;
                        case 'integer':
                        case 'boolean':
                            self::toScriptNumeric( $mapped['offset'], $code, $getLine);
                            break;

                        case 'vec3d':
                            self::toScriptVec3D( $mapped['offset'], $code, $getLine);
                            break;

                        case 'object':
                            self::toObject( $code, $getLine);
                            break;
                        default:
                            var_dump($mapped);
                            throw new \Exception("Not implemented!");

                    }
                    break;
                default:
                    var_dump($mapped);
                    throw new \Exception("Not implemented!");
                    break;

            }
        }else{

            switch ($mapped['section']) {

                case 'header':

                    switch ($mapped['type']) {

                        case 'level_var integer':
                            $code[] = $getLine('1a000000');
                            $code[] = $getLine('01000000');
                            $code[] = $getLine($mapped['offset']);
                            $code[] = $getLine('04000000');
                            break;
                        case 'integer':

                            $code[] = $getLine('11000000');
                            $code[] = $getLine('01000000');
                            $code[] = $getLine('04000000');

                            $code[] = $getLine('15000000');
                            $code[] = $getLine('04000000');

                            // define the offset
                            $code[] = $getLine($mapped['offset']);

                            $code[] = $getLine('01000000');
                            break;

                        default:
                            var_dump($mapped);
                            throw new \Exception("Not implemented!");
                    }

                    break;
                case 'script':
                    switch ($mapped['type']) {

                        case 'integer':

                            self::toHeaderInteger($mapped['offset'], $code, $getLine);
//                            $code[] = $getLine('15000000');
//                            $code[] = $getLine('04000000');
//                            $code[] = $getLine($mapped['offset']);
//                            $code[] = $getLine('01000000');

                            break;
                        default:
                            var_dump($mapped);
                            throw new \Exception("Not implemented!");

                    }
                    break;
                default:
                    var_dump($mapped);
                    throw new \Exception("Not implemented!");
                    break;

            }

        }

    }


    static public function toScriptEntityPtr( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }


    static public function toObject( &$code, \Closure $getLine){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('17000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('01000000');



    }

    static public function toScriptVec3D( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine( $offset );
//
        $code[] = $getLine('0f000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('44000000');

    }


    static public function toScriptNumeric( $offset, &$code, \Closure $getLine){


        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }


    static public function toHeaderTLevelState( $offset, &$code, \Closure $getLine){

        $code[] = $getLine('1a000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('04000000');
    }

    static public function toHeaderBoolean( $offset, &$code, \Closure $getLine){

        $code[] = $getLine('16000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }


    static public function toHeaderInteger( $offset, &$code, \Closure $getLine){

        $code[] = $getLine('11000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');


        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');

    }


    static public function toHeaderLevelVarBoolean( $offset, &$code, \Closure $getLine){

        $code[] = $getLine('1a000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('04000000');
    }

    static public function toHeaderStringArray( $offset, $size, &$code, \Closure $getLine){

        //define target offset
        $code[] = $getLine('21000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );

        //define the length
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine( Helper::fromIntToHex($size) );
        $code[] = $getLine('10000000');
        $code[] = $getLine('04000000');

        // save result
        $code[] = $getLine('10000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine('48000000');
    }




}