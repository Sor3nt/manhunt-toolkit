<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Helper;
use App\Service\Compiler\Token;

class T_ASSIGN {


    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){
        $code = [];
        $mapped = T_VARIABLE::getMapping($node, null, $data);
        $isObject = $mapped['type'] == "object";

        $leftHand = $node['body'][0];

        //HACK
        //when we have a type usage, we have no variable entry
        //so the compiler think its a function...
        if ($leftHand['type'] == Token::T_FUNCTION ){




            if (isset($data['customData']['customFunctions'][strtolower($node['value'])])){
//                $leftHand['type'] = Token::T_CUSTOM_FUNCTION;
                $code[] = $getLine('10000000');
                $code[] = $getLine('02000000');
                $code[] = $getLine('11000000');
                $code[] = $getLine('02000000');
                $code[] = $getLine('0a000000');
                $code[] = $getLine('34000000');
                $code[] = $getLine('02000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('20000000');
                $code[] = $getLine('01000000');
                $code[] = $getLine('04000000');
                $code[] = $getLine('02000000');
                $code[] = $getLine('0f000000');
                $code[] = $getLine('02000000');
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

            }


            $stateVar = str_replace('level_var ', '',$mapped['type']);

            if (isset($data['types'][$stateVar])){
                $leftHand['target'] = $stateVar;
                $leftHand['type'] = Token::T_VARIABLE;
            }
        }

        if ($mapped['type'] == "vec3d"){

            if ($mapped['section'] == "header"){
                $code[] = $getLine('21000000');

            }else{
                $code[] = $getLine('22000000');

            }
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

                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');
            }


        }

        /**
         * Evaluate the node
         */
        $resultCode = $emitter($leftHand);
        foreach ($resultCode as $line) {
            $code[] = $line;
        }

        //we do here some math...
        if (isset($node['body'][1]) && isset($node['body'][2])){

            $rightHand = $node['body'][2];
            $operator = $node['body'][1];


            $code[] = $getLine('10000000');
            $code[] = $getLine('01000000');

            $resultCode = $emitter($rightHand);
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

            if ($rightHand['type'] == Token::T_INT) {
                $code[] = $getLine('0f000000');
                $code[] = $getLine('04000000');

                if ($operator['type'] == Token::T_ADDITION) {

                    $code[] = $getLine('31000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');

                }else if ($operator['type'] == Token::T_SUBSTRACTION){

                    $code[] = $getLine('33000000');
                    $code[] = $getLine('04000000');
                    $code[] = $getLine('01000000');

                    $code[] = $getLine('11000000');
                    $code[] = $getLine('01000000');
                    $code[] = $getLine('04000000');


                }else{
                    throw new \Exception(sprintf('T_ASSIGN: handleSimpleMath operator not supported: %s', $operator['type']));

                }

//                if ($mapped['type'] == "level_var integer") {
//
//                }else{

//                }

            }else if ($rightHand['type'] == Token::T_FLOAT){
                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

                if ($operator['type'] == Token::T_ADDITION) {

                    $code[] = $getLine('50000000');
                }else if ($operator['type'] == Token::T_SUBSTRACTION) {

                    $code[] = $getLine('51000000');
                }


            }else if (
                $rightHand['type'] == Token::T_FUNCTION ||
                $rightHand['type'] == Token::T_VARIABLE
            ){

                try{
                    $rightMapped = T_VARIABLE::getMapping($rightHand, null, $data);

                }catch(\Exception $e){
                    throw new \Exception('righthand function handling not implemented');

                }


                $result = $emitter($rightMapped);

                foreach ($result as $item) {
                    $code[] = $item;
                }

                $code[] = $getLine('10000000');
                $code[] = $getLine('01000000');

                if ($operator['type'] == Token::T_MULTIPLY) {
                    $code[] = $getLine('52000000');
                }else{
                    throw new \Exception('Float substration not implemented');
                }


            }else{
                throw new \Exception(sprintf('T_ASSIGN: rightHand operator not supported: %s', $rightHand['type']));
            }
        }

        /*
         * Assign TO variable handling
         *
         * wee need here to difference between single param or multi param
         * mutli params are always math operators and need other return codes
         */
        if (isset($node['body'][1]) == false){


            //we are inside a function and want to return a value
//            if (isset($data['customData']['customFunctions'][strtolower($node['value'])])){
////                $leftHand['type'] = Token::T_CUSTOM_FUNCTION;
//
//                $code[] = $getLine('ka');
//
//
//            }else
                if (isset($mapped['abstract']) && $mapped['abstract'] == "state"){

                if (isset($mapped['isLevelVar']) && $mapped['isLevelVar'] == true){
                    self::toHeaderTLevelState( $mapped['offset'], $code, $getLine);

                }else{

                    self::toTLevelState( $mapped['offset'], $code, $getLine);
                }

            }else{
                switch ($mapped['section']) {

                    case 'header':

                        switch (strtolower($mapped['type'])) {
                            case 'boolean':
                            case 'integer':

                                self::toHeaderBoolean( $mapped['offset'], $code, $getLine);
                                break;
//                            self::toHeaderInteger( $mapped['offset'], $code, $getLine);
//                            break;

                            case 'level_var integer':
                            case 'level_var boolean':
                                self::toHeaderLevelVarBoolean( $mapped['offset'], $code, $getLine);
                                break;
                            case 'entityptr':
                                self::toHeaderentityptr( $mapped['offset'], $code, $getLine);
                                break;
                            case 'level_var tlevelstate':
                                self::toHeaderTLevelState( $mapped['offset'], $code, $getLine);
                                break;
                            case 'stringarray':
                                self::toHeaderStringArray( $mapped['offset'], $mapped['size'], $code, $getLine);
                                break;

                            case 'vec3d':
                                self::toHeaderVec3D( $mapped['offset'], $code, $getLine);
                                break;

                            default:
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

                            case 'custom_functions':
                                self::toCustomFunctions( $code, $getLine);
                                break;
                            case 'real':
                                self::toReal($mapped['offset'], $code, $getLine);
                                break;
                            default:
                                throw new \Exception("Not implemented!");

                        }
                        break;
                    default:
                        throw new \Exception("Not implemented!");
                        break;

                }
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

                            //TODO: das ist hier falscher platz
                            //gehhört nicht dazu
//                            $code[] = $getLine('11000000');
//                            $code[] = $getLine('01000000');
//                            $code[] = $getLine('04000000');

                            $code[] = $getLine('15000000');
                            $code[] = $getLine('04000000');

                            // define the offset
                            $code[] = $getLine($mapped['offset']);

                            $code[] = $getLine('01000000');
                            break;

                        default:
                            throw new \Exception("Not implemented!");
                    }

                    break;
                case 'script':
                    switch ($mapped['type']) {
                        case 'object':
                            self::toObject( $code, $getLine);
                            break;

                        case 'integer':

                            self::toHeaderInteger($mapped['offset'], $code, $getLine);

                            break;
                        default:
                            throw new \Exception("Not implemented!");

                    }
                    break;
                default:

                    throw new \Exception("Not implemented!");
                    break;

            }

        }

        return $code;
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


    static public function toCustomFunctions( &$code, \Closure $getLine){
        $code[] = $getLine('0f000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('17000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('02000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('13000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('04000000'); //offset?
    }

    static public function toReal($offset,&$code, \Closure $getLine){
        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine($offset);
        $code[] = $getLine('01000000');
    }

    static public function toScriptVec3D( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine( $offset );

        $code[] = $getLine('0f000000');
        $code[] = $getLine('01000000');
        $code[] = $getLine('0f000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine('44000000');

    }

    static public function toHeaderVec3D( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine( $offset );

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

    static public function toTLevelState( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('16000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }

    static public function toHeaderBoolean( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('16000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }

    static public function toHeaderInteger( $offset, &$code, \Closure $getLine){

        $code[] = $getLine('15000000');
        $code[] = $getLine('04000000');
        $code[] = $getLine( $offset );
        $code[] = $getLine('01000000');
    }

    static public function toHeaderentityptr( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('16000000');
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