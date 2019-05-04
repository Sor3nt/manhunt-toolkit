<?php
namespace App\Service\Compiler\Autocorrection\Statements;


use App\Service\Compiler\Token;

class Short {


    public function convertShortToFull( $tokens ){

        $tokens = $this->convertShort($tokens);

        $tokens = $this->convertShortIf($tokens);


        $tokens = $this->convertShortElse($tokens);



        return $tokens;

    }

    private function convertShort($tokens){
        $current = 0;

        $result = [];
        while($current < count($tokens)){
            $token = $tokens[$current];
            $current++;

            if ($token['type'] == Token::T_THEN){

                $result[] = $token;

               //we have a regular short statement
               if (
                   $tokens[$current]['type'] != Token::T_IF &&
                   $tokens[$current]['type'] != Token::T_BEGIN
               ){

                    $result[] = [
                        'type' => Token::T_BEGIN,
                        'value' => 'begin'
                    ];


                    list($current, $short) = $this->getUntilLineEnd($current, $tokens);
                    foreach ($short as $index => $item) {

                        $result[] = $item;

                    }

                    if ($tokens[$current]['type'] == Token::T_ELSE){

                        $current++;

                        $result[] = [
                            'type' => Token::T_END_ELSE,
                            'value' => 'end'
                        ];

                        $result[] = [
                            'type' => Token::T_ELSE,
                            'value' => 'else'
                        ];

                    }else{
                        $result[] = [
                            'type' => Token::T_IF_END,
                            'value' => 'end;'
                        ];

                    }

                }

            }else{

                $result[] = $token;
            }

        }

        return $result;

    }

    private function convertShortElse($tokens){
        $current = 0;

        $result = [];
        while($current < count($tokens)){
            $token = $tokens[$current];
            $current++;

           //we have a regular short statement
           if (
               $token['type'] == Token::T_ELSE &&
               $tokens[$current]['type'] != Token::T_BEGIN &&
               $tokens[$current]['type'] != Token::T_IF
           ){

               $result[] = $token;

               $result[] = [
                    'type' => Token::T_BEGIN,
                    'value' => 'begin'
                ];


                list($current, $short) = $this->getUntilLineEnd($current, $tokens);
                foreach ($short as $index => $item) {
                    $result[] = $item;
                }

                if($tokens[$current]['type'] != Token::T_ELSE){
                    $result[] = [
                        'type' => Token::T_IF_END,
                        'value' => 'end;'
                    ];
                }

            }else{

                $result[] = $token;
            }

        }

        return $result;

    }

    private function convertShortIf($tokens){

        $current = 0;

        $result = [];
        while($current < count($tokens)){
            $token = $tokens[$current];
            $current++;

            if ($token['type'] == Token::T_THEN){

                $result[] = $token;

                // we have a short statement followed by a IF statement
                if ($tokens[$current]['type'] == Token::T_IF){


                    $result[] = [
                        'type' => Token::T_BEGIN,
                        'value' => 'begin'
                    ];

                    $result[] = $tokens[$current];
                    $current++;

//                    if ($this->isIFShortStatement($current, $tokens)){
//
//                        list($current, $untilBlockEnd) = $this->getUntilLineEnd($current, $tokens);
//                    }else{
                        list($current, $untilBlockEnd) = $this->getIfTrue($current, $tokens);
//                    }

                    foreach ($untilBlockEnd as $item) {
                        $result[] = $item;
                    }

                    if (end($result)['type'] !== Token::T_IF_END){
                        $result[] = [
                            'type' => Token::T_IF_END,
                            'value' => 'end;'
                        ];

                    }


                }

            }else{

                $result[] = $token;
            }

        }

        return $result;
    }


    private function getUntilLineEnd($current, $tokens){
        $result = [];

        while($current < count($tokens)){
            $token = $tokens[$current];
            $current++;

            if ($token['type'] == Token::T_LINEEND) {
                $result[] = $token;
                return [$current, $result];
            }else if ($token['type'] == Token::T_ELSE){
                return [$current, $result];
            }else{
                $result[] = $token;
            }
        }

        return [$current, $result];
    }

    private function isIFShortStatement( $current, $tokens ){


        while($current < count($tokens)){
            $token = $tokens[$current];
            if (
                $token['type'] == Token::T_THEN &&
                $tokens[$current + 1]['type'] != Token::T_BEGIN
            ){
                return true;
            }

            $current++;
        }

        return false;
    }

    private function getIfTrue( $current, $tokens ){

        $result = [];

        $deep = 0;
        while($current < count($tokens)){
            $token = $tokens[$current];

            $result[] = $token;

            if ($token['type'] == Token::T_IF && $this->isIFShortStatement($current, $tokens) == false) {
                $deep++;
            }else if ($token['type'] == Token::T_IF_END){
                if ($deep == 0){
                    return [$current, $result];
                }else{
                    $deep--;
                }

            }

            $current++;
        }

        return [$current, $result];
    }

}