<?php
namespace App\Service\Compiler\Autocorrection\LineEnd;

use App\Service\Compiler\Parser;
use App\Service\Compiler\Token;
use App\Service\Helper;

/**
 * Class FunctionCall
 * @package App\Service\Compiler\Autocorrection\LineEnd
 *
 * Will fix missed line ends like
 *
 * "sleep(200)" to "sleep(200);"
 */
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

                //we call the mapping just to get the endline (current2)
                list($current2) = Parser\T_FUNCTION::map($tokens, $current, function($tokens, $current) use ( $parser ){
                    return $parser->parseToken($tokens, $current);
                });

                while($current < $current2){
                    $result[] = $tokens[$current];

                    $current++;
                }

                if (
                    $tokens[$current]['type'] == Token::T_ELSE ||
                    Helper::isTokenEndToken($tokens[$current])
                ){
                    $result[] = [
                        'type' => Token::T_LINEEND,
                        'value' => ';'
                    ];

                }

                $result[] = $tokens[$current];

            }else{
                $result[] = $token;
            }


            $current++;
        }

        return $result;
    }


}