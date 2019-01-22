<?php
namespace App\Service\Compiler\Emitter;

use App\Service\Compiler\Token;
use App\Service\Helper;

class T_CUSTOM_FUNCTION {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = '[T_CUSTOM_FUNCTION] map ';

        $code = [ ];

        /**
         * Create script start sequence
         */
        $code[] = $getLine('10000000', false, $debugMsg . 'function start');
        $code[] = $getLine('0a000000', false, $debugMsg . 'function start');
        $code[] = $getLine('11000000', false, $debugMsg . 'function start');
        $code[] = $getLine('0a000000', false, $debugMsg . 'function start');
        $code[] = $getLine('09000000', false, $debugMsg . 'function start');

        /**
         * generate the needed bytes for the script
         */

        $sum = 0;
        foreach ($data['variables'] as $variable) {

            if (
                $variable['section'] == "script"
            ){
                $sum += $variable['size'];

            }
        }


        // add return size
        $code[] = $getLine('34000000', false, $debugMsg . 'reserve return bytes');
        $code[] = $getLine('09000000', false, $debugMsg . 'reserve return bytes');

        switch (strtolower($node['returnType'])){
            case 'vec3d':
                $code[] = $getLine(Helper::fromIntToHex(12), false, $debugMsg . 'reserve return bytes 12');
                break;
            case 'string':
            case 'real':
            case 'integer':
            case 'boolean':
                $code[] = $getLine(Helper::fromIntToHex(4), false, $debugMsg . 'reserve return bytes 4');
                break;

            default:
                throw new \Exception('Unknown returntype ' . $node['returnType']);
        }



        if ($sum > 0){

            $code[] = $getLine('34000000', false, $debugMsg . 'reserve bytes');
            $code[] = $getLine('09000000', false, $debugMsg . 'reserve bytes');
            $code[] = $getLine(Helper::fromIntToHex($sum), false, $debugMsg . 'reserve bytes ' . $sum);

        }


        /**
         * parse out the parameters
         */
        $varCurrent = 0;
        $vars = [];
        if (isset($node['vars'])){

            while ($varCurrent < count($node['vars'])) {
                $varToken = $node['vars'][$varCurrent];

                if ($varToken['type'] == Token::T_LINEEND){
                    $varCurrent++;
                    continue;
                }

                $newVars = [];
                while( $varToken['type'] == Token::T_VARIABLE ){
                    $newVars[] = $varToken['value'];
                    $varCurrent++;
                    $varToken = $node['vars'][$varCurrent];
                }

                if ($varToken['type'] != Token::T_DEFINE) throw new \Exception('Need T_DEFINE, got ' . $varToken['type']);

                $varCurrent++;
                $varToken = $node['vars'][$varCurrent];

                foreach ($newVars as $newVar) {
                    $vars[ $newVar ] = [
                        'name' => $newVar,
                        'valueType' => $varToken['value']
                    ];
                }

                $varCurrent++;
            }


            /**
             * calculate the offsets
             */
            $vars = array_reverse($vars);

            $varOffset = -12;
            foreach ($vars as &$var) {
                $var['offset'] = substr(Helper::fromIntToHex($varOffset),0, 8);
//                $var['offset'] = $varOffset;
                $var['section'] = 'script';
                $var['type'] = 'customFunction';
                $varOffset -= 4;
            }
        }

        foreach ($node['body'] as $innerNode) {
            $resultCode = $emitter( $innerNode, true, [ 'customFunctionVars' => $vars ] );

            if (is_null($resultCode)){
                throw new \Exception('Return was null, a emitter missed a return statement ?');
            }

            foreach ($resultCode as $line) {
                $line->debug = $debugMsg . ' ' . $line->debug;
                $code[] = $line;
            }
        }

        /**
         * Create script end sequence
         */
        $code[] = $getLine('11000000' , false, $debugMsg . 'function end');
        $code[] = $getLine('09000000' , false, $debugMsg . 'function end');
        $code[] = $getLine('0a000000' , false, $debugMsg . 'function end');
        $code[] = $getLine('0f000000' , false, $debugMsg . 'function end');
        $code[] = $getLine('0a000000' , false, $debugMsg . 'function end');
        $code[] = $getLine('3a000000' , false, $debugMsg . 'function end');
        $code[] = $getLine(Helper::fromIntToHex(4 + (count($vars) * 4)) , false, $debugMsg . 'reserve byte' );


        return $code;
    }

}