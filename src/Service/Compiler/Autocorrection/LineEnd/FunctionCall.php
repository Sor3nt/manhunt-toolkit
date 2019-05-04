<?php
namespace App\Service\Compiler\Autocorrection\LineEnd;

use App\Service\Compiler\Parser;
use App\Service\Compiler\Parser\T_VARIABLE;
use App\Service\Compiler\Token;

class FunctionCall{

    public function autocorrect( $tokens ){

        $parser = new Parser();


        $current = 0;

        $result = [];
        while($current < count($tokens)) {
            $token = $tokens[$current];

            if (
                $token['type'] == Token::T_FUNCTION
            ){

                list($current2, $mapped) = Parser\T_FUNCTION::map($tokens, $current, function($tokens, $current) use ( $parser ){
                    return $parser->parseToken($tokens, $current);
                });
//

                while($current < $current2){
                    $result[] = $tokens[$current];

                    $current++;
                }
//
//
                if (
                    $tokens[$current]['type'] == Token::T_ELSE ||
                    $tokens[$current]['type'] == Token::T_END_CODE
                ){

//                    var_dump($tokens[$current]['type']);

                    $result[] = [
                        'type' => Token::T_LINEEND,
                        'value' => ';'
                    ];

//                    var_dump($result, "jaaa");
//                    exit;
                }
                $result[] = $tokens[$current];

//
            }else{
                $result[] = $token;
            }


            $current++;
        }

        return $result;


    }


}