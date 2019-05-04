<?php
namespace App\Service\Compiler\Autocorrection\LineEnd;

use App\Service\Compiler\Parser;
use App\Service\Compiler\Parser\T_VARIABLE;
use App\Service\Compiler\Token;

class Assign{

    public function autocorrect( $tokens ){

        $parser = new Parser();


        $current = 0;

        $result = [];
        while($current < count($tokens)) {
            $token = $tokens[$current];

            if (
                !isset($tokens[$current - 1]) ||
                ($tokens[$current - 1]['type'] != Token::T_FOR)
            ){

//exit;
                list($current2, $mapped) = T_VARIABLE::map($tokens, $current, function($tokens, $current) use ( $parser ){
                    return $parser->parseToken($tokens, $current);
                });

                if ($mapped['type'] == Token::T_ASSIGN && isset($mapped['body'])){

                    while($current < $current2){
                        $result[] = $tokens[$current];

                        $current++;
                    }

                    $result[] = $tokens[$current];

                    if (
                        $tokens[$current]['type'] != Token::T_LINEEND &&
                        $tokens[$current]['type'] != Token::T_TO
                    ){
                        $result[] = [
                            'type' => Token::T_LINEEND,
                            'value' => ';'
                        ];
                    }

    //                $current--;

                }else{
                    $result[] = $token;
                }
            }else{
                $result[] = $token;
            }

            $current++;
        }

        return $result;


    }


}