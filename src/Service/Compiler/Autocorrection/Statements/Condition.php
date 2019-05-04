<?php
namespace App\Service\Compiler\Autocorrection\Statements;

use App\Service\Compiler\Token;

/**
 * Class Condition
 * @package App\Service\Compiler\Autocorrection\Statements
 *
 * Helper class to add missed brackets to the conditions
 *
 * "if a = b" will be converted to "if (a = b)"
 */
class Condition {

    public function autocorrectConditionBrackets( $tokens ){

        $current = 0;

        $result = [];
        while($current < count($tokens)){
            $token = $tokens[$current];
            $current++;

            if ($token['type'] == Token::T_IF){
                $result[] = $token;

                list($current, $conditions) = $this->getIfCondition( $current, $tokens );

                foreach ($conditions as &$condition) {

                    $condition = $this->fixCondition( $condition );
                    foreach ($condition as $item) {
                        $result[] = $item;
                    }
                }

                $result[] = [
                    'type' => Token::T_THEN,
                    'value' => 'then'
                ];

                continue;

            }else{

                $result[] = $token;
            }

        }

        return $result;
    }


    private function getIfCondition( $current, $tokens ){
        $results = [];

        $result = [];

        while($current < count($tokens)){
            $token = $tokens[$current];

            $current++;

            if (
                $token['type'] == Token::T_OR ||
                $token['type'] == Token::T_AND
            ){
                $results[] = $result;
                $result = [];

            }


            //we reach the statement end
            if ($token['type'] == Token::T_THEN){
                $results[] = $result;
                return [$current, $results];
            }

            $result[] = $token;

        }

        throw new \Exception('getIfCondition failed');
    }

    private function fixCondition( $condition ){

        $firstToken = $condition[0];
        $lastToken = end($condition);

        $result = [];

        $isAndOr =  $firstToken['type'] == Token::T_AND ||
            $firstToken['type'] == Token::T_OR;

        $wrapFirst = $isAndOr;
        if ($firstToken['type'] == Token::T_FUNCTION) $wrapFirst = false;

        if ($isAndOr){
            $result[] = $firstToken;
            $firstToken = $condition[1];
        }

        if (
            (
                $firstToken['type'] != Token::T_BRACKET_OPEN &&
                $lastToken['type'] != Token::T_BRACKET_CLOSE
            ) || (
                $firstToken['type'] == Token::T_FUNCTION
            )
        ){

            $result[] = [
                'type' => Token::T_BRACKET_OPEN,
                'value' => '('
            ];

            foreach ($condition as $index => $item) {
                //skip entry , we dont want wrap OR and AND
                if ($wrapFirst && $index == 0) continue;

                $result[] = $item;
            }

            $result[] = [
                'type' => Token::T_BRACKET_CLOSE,
                'value' => ')'
            ];


        }else{

            //we have already brackets, yay
            //just resturn the original
            return $condition;

        }


        return $result;
    }

}