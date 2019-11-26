<?php

namespace App\Service\CompilerV2;

use App\Service\Helper;

class Evaluate{
    
    
    public $msg = "";

    /** @var Compiler */
    private $compiler;

    /**
     * Evaluate constructor.
     * @param Compiler $compiler
     * @param Associations $association
     * @throws \Exception
     */
    public function __construct( Compiler $compiler, Associations $association )
    {

        $this->compiler = $compiler;
        
        switch ($association->type){
            
            case Tokens::T_SCRIPT:

                $this->msg = sprintf("Initialize Script %s", $association->value);
                $this->add('10000000');
                $this->add('0a000000');
                $this->add('11000000');
                $this->add('0a000000');
                $this->add('09000000');


                $scriptSize = $compiler->getScriptSize($association->value);
                if ($scriptSize > 0){
                    $this->msg = sprintf("Reserve Memory %s", $scriptSize);

                    $this->add('34000000');
                    $this->add('09000000');
                    $this->add(Helper::fromIntToHex($scriptSize));
                }

                foreach ($association->childs as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                $this->msg = sprintf("Closing Script %s", $association->value);
                $this->add('11000000');
                $this->add('09000000');
                $this->add('0a000000');
                $this->add('0f000000');
                $this->add('0a000000');
                $this->add('3b000000');
                $this->add('00000000');

                break;

            case Tokens::T_VARIABLE:
                $this->msg = sprintf("Use Variable %s", $association->value);


//                if ($association->varType == "integer"){
//                    $this->msg = sprintf("Read Integer %s", $association->value);
//
//                    $this->add($association->section == "header" ? '14000000' : '13000000');
//                    $this->add('01000000');
//                    $this->add('04000000');
//                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');
//
//                    $this->add('10000000', 'Return');
//                    $this->add('01000000', 'Return');
//
//                }else
                    if ($association->varType == "vec3d") {
                    $this->add($association->section == "header" ? '21000000' : '22000000', 'Section ' . $association->section);
                    $this->add('04000000');
                    $this->add('01000000');
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                    $this->add('10000000', 'Return');
                    $this->add('01000000', 'Return');
                    //
                }

                if ($association->assign !== false){


//                    $this->msg = sprintf("Assign to %s", $association->value);
                    new Evaluate($this->compiler, $association->assign);

                    $this->msg = sprintf("Assign to Variable %s", $association->value);

                    if (
                        $association->varType == "integer" ||
                        $association->varType == "boolean"
                    ) {

                        $this->add($association->section == "header" ? '16000000' : '15000000', 'Section ' . $association->section);
                        $this->add('04000000');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                        $this->add('01000000');

                    }else if ($association->varType == "entityptr"){
                        $this->add('15000000');
                        $this->add('04000000');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                        $this->add('01000000');
                    }else if ($association->varType == "vec3d"){
                        $this->add('12000000');
                        $this->add('03000000');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                        $this->add('0f000000');
                        $this->add('01000000');
                        $this->add('0f000000');
                        $this->add('04000000');
                        $this->add('44000000');

                    }else{
                        throw new \Exception(sprintf("Assign type %s not implemented", $association->varType));
                    }


                }


                if ($association->math !== false){

//var_dump($association);
//exit;
                    $this->msg = sprintf("Variable %s Math Operation ", $association->value);
//
                    if ($association->varType == "integer"){
                        $this->add($association->section == "header" ? '14000000' : '13000000', 'Read Variable');
                        $this->add('01000000', 'Read Integer');
                        $this->add('04000000', 'Read Integer');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                    }

                    $this->add('10000000');
                    $this->add('01000000');

                    foreach ($association->math->childs as $condition) {
                        new Evaluate($this->compiler, $condition);
                    }

                    $this->add('0f000000');
                    $this->add('04000000');

                    if ($association->math->type == Tokens::T_ADDITION) {
                        $this->add('31000000');
                        $this->add('01000000');
                        $this->add('04000000');
                    }else if ($association->math->type == Tokens::T_MULTIPLY){
                        $this->add('35000000');
                        $this->add('04000000');
                    }else if ($association->math->type == Tokens::T_SUBSTRACTION){
                        $this->add('33000000');
                        $this->add('04000000');
                        $this->add('01000000');
                        $this->add('11000000');
                        $this->add('01000000');
                        $this->add('04000000');
                    }else if ($association->math->type == Tokens::T_DIVISION){
                        $this->add('T_DIVISION');
                    }else{
                        throw new \Exception("Math-Type not implemented " . $association->math->type);
                    }
                }


                break;
            case Tokens::T_IS_EQUAL:
                $this->add('T_IS_EQUAL');

                break;
            case Tokens::T_IF:
                $this->msg = sprintf("IF Statement ");
//var_dump($association);
//exit;
                $endOffsets = [];
                foreach ($association->cases as $index => $case) {

                    $compareAgainst = false;

                    //apply the condition
                    /** @var Associations $condition */
                    foreach ($case->condition as $conditionIndex => $condition) {

                        $firstEntry = $condition->childs[0];

                        if ($firstEntry->type == Tokens::T_FUNCTION) {

                            if ($firstEntry->return == null) {
                                throw new \Exception(sprintf("No Return type available for function %s", $condition->value));
                            }

                            $compareWith = $firstEntry->return;
                        } else if ($firstEntry->type == Tokens::T_VARIABLE) {
                            $compareWith = $firstEntry->varType;
                        } else {
                            $compareWith = "integer";
                        }

                        if ($firstEntry->varType == "integer") {

                            $this->add($firstEntry->section == "header" ? '14000000' : '13000000', 'Read Integer');
                            $this->add('01000000', 'Read Integer Variable');
                            $this->add('04000000', 'Read Integer Variable');
                            $this->add(Helper::fromIntToHex($firstEntry->offset), 'Offset');

                            $this->add('10000000', 'Return Integer Variable');
                            $this->add('01000000', 'Return Integer Variable');
                        }

                        foreach ($condition->childs as $child) {
                            new Evaluate($this->compiler, $child);
                        }



                        if (count($case->condition) > 1){
//                            var_dump($case->condition);
//                            exit;
                            if ($compareWith !== "integer") {

                                $this->add('0f000000', "return 0f 04");
                                $this->add('04000000', "return 0f 04");

                            } else {
                                $this->add('10000000', 'Return Condition');
                                $this->add('01000000', 'Return Condition');
                            }
                        }

                        if ($case->isNot !== null || $condition->isNot !== null){
                            $this->add('29000000', 'Not');
                            $this->add('01000000', 'Not');
                            $this->add('01000000', 'Not');
                        }

//                        var_dump($condition);
//                        exit;
                        if ($condition->operatorValue == null) continue;
                        new Evaluate($this->compiler, $condition->operatorValue);

                        //HACK

//                        var_dump($condition);
                        if ($compareWith == "integer") {
                            $this->add('0f000000', "HACK");
                            $this->add('04000000', "HACK");
                        }

                        //HACK


//                        if ($condition->statementOperator == Tokens::T_AND){

                            $this->add('23000000');
                            $this->add('04000000');
                            $this->add('01000000');

                            $this->add('12000000');
                            $this->add('01000000');
                            $this->add('01000000');

                            if ($condition->operator == Tokens::T_IS_NOT_EQUAL){
                                $this->add('40000000');
                            }else{
                                $this->add('3f000000');
                            }

                            $offset = count($compiler->codes);
                            $this->add('OFFSET', 'Offset');

                            $this->add('33000000');
                            $this->add('01000000');
                            $this->add('01000000');

                            $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                            if ($conditionIndex > 0){
                                $this->add('0f000000', "return 0f 04 (operator)");
                                $this->add('04000000', "return 0f 04 (operator)");
                            }else if (count($case->condition) > 1){
                                $this->add('10000000', "return current condition");
                                $this->add('01000000', "return current condition");

                            }
//                        }
//
//


////
//                        if ($compareWith == "string"){
//                            $this->add('49000000', 'Compare string');
//                        }else if ($compareWith == "float"){
//                            $this->add('4e000000', 'Compare float');
//
//                        }else{
//                            $this->add('23000000', 'Compare Int/Boolean/Const');
//                            $this->add('04000000', 'Compare Int/Boolean/Const');
//                            $this->add('01000000', 'Compare Int/Boolean/Const');
//                        }
//

//                        $this->add('12000000', '');
//                        $this->add('01000000', '');
//                        $this->add('01000000', '');
//
//
//
//                        if ($condition->statementOperator){
//
//
//                            switch ($condition->operatorValue){
//                                case Tokens::T_IS_EQUAL:
//                                    $this->add('3f000000');
//                                    break;
//                                case Tokens::T_IS_NOT_EQUAL:
//                                    $this->add('40000000');
//                                    break;
//                                case Tokens::T_IS_SMALLER:
//                                    $this->add('3d000000');
//                                    break;
//                                case Tokens::T_IS_SMALLER_EQUAL:
//                                    $this->add('3e000000');
//                                    break;
//                                case Tokens::T_IS_GREATER:
//                                    $this->add('42000000');
//                                    break;
//                                case Tokens::T_IS_GREATER_EQUAL:
//                                    $this->add('41000000');
//                                    break;
//                                default:
//                                    throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $condition->operatorValue));
//                                    break;
//                            }
//
                            if ($condition->statementOperator ){
                                switch ($condition->statementOperator){

                                    case Tokens::T_OR:
                                        $this->add('27000000', 'OR');
                                        break;
                                    case Tokens::T_AND:
                                        $this->add('25000000', 'AND');
                                        break;
                                    default:
                                        throw new \Exception(sprintf('Evaluate: statementOperator =>  %s is not a valid operator !', $condition->statementOperator));
                                        break;
                                }

                                $this->add('01000000', 'Next Condition ');
                                $this->add('04000000', 'Next Condition');

                                if ($conditionIndex + 1 != count($case->condition)) {
                                    $this->add('10000000', 'return ');
                                    $this->add('01000000', 'return');
                                }


                            }
//
//
//
//                        }
//
//                        if ($conditionIndex + 1 != count($case->condition)) {
//                            $this->add('10000000', 'return ');
//                            $this->add('01000000', 'return');
//                        }

                    }

                    $this->add('24000000');
                    $this->add('01000000');
                    $this->add('00000000');
                    $this->add('3f000000');

                    $offset = count($compiler->codes);
                    $this->add('OFFSET', "Offset");
//var_dump($case);
//exit;
                    foreach ($case->onTrue as $item) {
                        new Evaluate($this->compiler, $item);
                    }


                    if (count($association->cases) != $index + 1){
                        $this->add('3c000000');

                        $endOffsets[] = count($compiler->codes);
                        $this->add('END OFFSET', "End Offset");

                    }

                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                }

                foreach ($endOffsets as $offset) {
                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;
            case Tokens::T_FUNCTION:
                foreach ($association->childs as $param) {


                    if ($param->varType == "string") {
                        // move the internal pointer to the offset
                        $this->add($param->section == "header" ? '21000000' : '22000000', 'Read String from Section ' . $param->section);
                        $this->add('04000000', 'Read String');
                        $this->add('01000000', 'Read String');
                        $this->add(Helper::fromIntToHex($param->offset), 'Offset');

                        //then read the given size
                        $this->add('12000000', 'Read String');
                        $this->add('02000000', 'Read String');
                        $this->add(Helper::fromIntToHex($param->size), "Size of " . $param->size);
                    }


                    new Evaluate($this->compiler, $param);

                    //we need to return the result after any math operation
                    if ($param->math !== false){
                        $this->msg = sprintf("Function %s Math Return", $association->value);
                        $this->add('10000000');
                        $this->add('01000000');
                    }

                    //regular parameter return
                    $this->msg = sprintf("Function %s Return", $association->value);
                    $this->add('10000000');
                    $this->add('01000000');

                    //special return for strings
                    if ( $param->type == Tokens::T_STRING || $param->varType == "string" ){
                        $this->msg = sprintf("Function %s Return String", $association->value);

                        $this->add('10000000');
                        $this->add('02000000');
                    }
                }


                $this->msg = sprintf("Call Function %s", $association->value);
                $this->add($association->offset);

                break;

            case Tokens::T_SWITCH:
                /** @var Associations $caseVariable */
                $caseVariable = $association->value;

                $this->msg = sprintf("Switch %s", $caseVariable->value);

                new Evaluate($this->compiler, $caseVariable);

                $caseStartOffsets = [];
                $caseEndOffsets = [];

                if ($caseVariable->varType == "integer") {
                    $this->add($association->section == "header" ? '14000000' : '13000000', 'Section ' . $association->section);
                    $this->add('01000000');
                    $this->add('04000000');

                    $this->add(Helper::fromIntToHex($caseVariable->offset), 'Offset');
                }


                $cases = array_reverse($association->cases);
                foreach (array_reverse($association->cases) as $index => $case) {

                    $realIndex = count($cases) - $index - 1;

                    $this->add('24000000', 'Case ' . $realIndex);
                    $this->add('01000000');
                    $this->add(Helper::fromIntToHex($realIndex), 'Offset');
                    $this->add('3f000000');

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseStartOffsets[] = count($compiler->codes);
                    $this->add('CASE OFFSET', 'Case Offset');
                }

                foreach (array_reverse($association->cases) as $index => $case) {
                    $this->add('3c000000');

                    //we dont know yet the correct offset, we store the position and
                    //fix it in the next loop
                    $caseEndOffsets[] = count($compiler->codes);
                    $this->add('END OFFSET', 'Last Case Offset');

                    //fix the missed start offsets
                    $compiler->codes[ $caseStartOffsets[$index] ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                    new Evaluate($this->compiler, $case);
                }

                $this->add('3c000000');

                $caseEndOffsets[] = count($compiler->codes);
                $this->add('END OFFSET', 'Last Case Offset');

                //fix the missed end offsets
                foreach ($caseEndOffsets as $caseEndOffset) {
                    $compiler->codes[ $caseEndOffset ]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;

            case Tokens::T_CASE:
                $this->msg = sprintf("Case %s", $association->value);
                foreach ($association->onTrue as $condition) {
                    new Evaluate($this->compiler, $condition);
                }


                break;
            case Tokens::T_FLOAT:
                $this->msg = sprintf("Read Float %s", $association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromFloatToHex($association->value), "Offset");

                break;

            case Tokens::T_CONSTANT:
                $this->msg = sprintf("Read Constant %s", $association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add($compiler->gameClass->getConstant($association->value)['offset'], "Offset");
                break;

            case Tokens::T_INT:
            case Tokens::T_BOOLEAN:
                $this->msg = sprintf("Read Integer/Boolean %s", (int)$association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromIntToHex((int)$association->value), 'Offset');

                break;
            case Tokens::T_STRING:

                $stringIndex = substr($association->value, 4);
                $string = $compiler->strings[$stringIndex];


                $this->msg = sprintf("Move String Pointer to %s", $string['offset']);

                $this->add('21000000');
                $this->add('04000000');
                $this->add('01000000');

                $this->add(Helper::fromIntToHex($string['offset']), 'Offset');

                $this->msg = sprintf("Read String %s", $string['value']);

                $this->add('12000000');
                $this->add('02000000');
                $this->add(Helper::fromIntToHex($string['size']), "Length");

                break;
            default:
                throw new \Exception(sprintf("Unable to evaluate %s ", $association->type));
        }
    }


    private function add($code, $appendix = null ){
        $msg = $this->msg;

        if (!is_null($appendix)) $msg .= ' | ' . $appendix;


        $this->compiler->codes[] = [
            'code' => $code,
            'msg' => $msg
        ];
    }
}