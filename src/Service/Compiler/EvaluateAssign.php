<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class EvaluateAssign {

    static public function process( $node, &$code, \Closure $getLine,\Closure $emitter, $data ){
        /**
         * when the variable is not found, check if its an object
         */
        //Todo: auslagern
        if (!isset($data['variables'][$node['value']])){

            if (strpos($node['value'], '.') !== false){

                $mapped = Evaluate::getObjectToAttributeSplit(array_merge($data, [
                    'conditionVariable' => [ 'value' => $node['value'] ]
                ]));
            }else{
                throw new \Exception(sprintf('T_ASSIGN: unable to detect variable: %s', $node['value']));
            }
        }else{
            $mapped = $data['variables'][$node['value']];
        }

        $leftHand = $node['body'][0];


        /**
         * Init the param (if needed)
         */

//        Evaluate::initialize($leftHand['type'], $mapped, $code, $getLine);

        /**
         * Evaluate the node
         */
        $resultCode = $emitter($leftHand);
        foreach ($resultCode as $line) {
            $code[] = $line;
        }


        /*
         * Assign to variable handling
         */
        switch ($mapped['section']) {

            case 'header':

                switch (strtolower($mapped['type'])) {
                    case 'integer':
                    case 'boolean':
                    self::toHeaderNumeric( $mapped['offset'], $code, $getLine);
                        break;
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
                    case 'integer':
                    case 'boolean':
                        self::toScriptNumeric( $mapped['offset'], $code, $getLine);
                        break;

                    case 'vec3d':
                        self::toScriptVec3D( $mapped['offset'], $code, $getLine);
                        break;

//                    case 'object':
//                        self::toObject( $code, $getLine);
//                        break;
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


//    static public function toObject( &$code, \Closure $getLine){
//        $code[] = $getLine('17000000');
//        $code[] = $getLine('04000000');
//        $code[] = $getLine('02000000');
//        $code[] = $getLine('01000000');
//    }

    static public function toScriptVec3D( $offset, &$code, \Closure $getLine){
        $code[] = $getLine('12000000');
        $code[] = $getLine('03000000');
        $code[] = $getLine( $offset );
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

    static public function toHeaderNumeric( $offset, &$code, \Closure $getLine){

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