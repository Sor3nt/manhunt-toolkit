<?php
namespace App\Service\Compiler\Parser;

use App\Service\Compiler\Token;

class T_CUSTOM_FUNCTION {


    static public function map( $tokens, $current, \Closure $parseToken ){
        $code = [];


        $starCurrent = $current;

        $isForward = true;

        while ($current < count($tokens)) {
            $token = $tokens[$current];

            if ($token['type'] == Token::T_BEGIN){
                $isForward = false;
                break;
            }

            if ($token['type'] == Token::T_FORWARD){
                $isForward = true;
                break;
            }

            $current++;

        }

        $current = $starCurrent;

        /**
         * we have a forward define section
         */
        if ($isForward == true){
            $current++;

            $customFunctionParametersResult = [];

            if ($tokens[$current + 1]['value'] == ":"){

                $functionName = $tokens[$current ]['value'];
                $returnType = $tokens[$current + 2 ]['value'];
                $current = $current + 2;
            }else{
                $functionName = $tokens[$current ]['value'];

                $current = $current + 2;

                $customFunctionParametersRaw = [];
                while($tokens[$current]['type'] !== Token::T_BRACKET_CLOSE){
                    $customFunctionParametersRaw[] = $tokens[$current];
                    $current++;
                }

                // skip bracket close
                $current++;

                // skip t_define
                $current++;

                $customFunctionParameters = array_chunk($customFunctionParametersRaw, 3);

                foreach ($customFunctionParameters as $customFunctionParameter) {

                    $customFunctionParametersResult[ $customFunctionParameter[0]['value'] ] = [
                        'offset' => "OFFSET-TODO",
                        'type' => $customFunctionParameter[2]['value']
                    ];

                }

                $returnType = $tokens[$current]['value'];
            }


            $functionName = trim($functionName);
            $returnType = trim($returnType);

            $node = [
                'type' => Token::T_FORWARD,
                'to' => $functionName,
                'returnType' => $returnType,
                'parameters' => $customFunctionParametersResult,
                'section' => Token::T_CUSTOM_FUNCTION,
                'params' => [],
            ];

            $current++;

            if ($tokens[$current]['type'] == Token::T_BRACKET_OPEN){

                $current++;

                while ($current < count($tokens)) {

                    if ($tokens[$current]['type'] == Token::T_BRACKET_CLOSE){
                        $current++;
                        break;
                    }else{
                        $node['params'][] = $tokens[$current];
                    }

                    $current++;
                }
            }

            if ($tokens[$current]['type'] !== Token::T_LINEEND){
                throw new \Exception('Parser: parseForward T_LINEEND expected');
            }

            $current++;

            if (strtolower($tokens[$current]['value']) != "forward"){
                throw new \Exception('Parser: parseForward FORWARD expected');
            }

            $current++;

            /**
             * we have a procedure define section
             */
        }else{
            return T_SCRIPT::map($tokens, $current, $parseToken);
        }

        return [
            $current, $node
        ];
    }
}