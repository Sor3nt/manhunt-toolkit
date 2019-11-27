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
            
            case Tokens::T_PROCEDURE:
                $this->msg = sprintf("Initialize Custom Function %s", $association->value);
                $this->add('10000000');
                $this->add('0a000000');
                $this->add('11000000');
                $this->add('0a000000');
                $this->add('09000000');

//                $returnSize = 0;
//                if ($association->return !== null)
//                    $returnSize = $compiler->calcSize($association->return);
//
//
                $scriptSize = $compiler->getScriptSize($association->value);
                if ($scriptSize > 0){
                    $this->msg = sprintf("Reserve Memory %s", $scriptSize);

                    $this->add('34000000');
                    $this->add('09000000');
                    $this->add(Helper::fromIntToHex($scriptSize), sprintf('Reserve %s bytes', $scriptSize));
                }

                foreach ($association->childs as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                $this->msg = sprintf("Closing Custom Function %s", $association->value);
                $this->add('11000000');
                $this->add('09000000');
                $this->add('0a000000');
                $this->add('0f000000');
                $this->add('0a000000');
                $this->add('3a000000');

                $variables = $compiler->getVariablesByScriptName($association->value);

                $this->add(Helper::fromIntToHex(4 + (count($variables) * 4)), 'Reserve Pointer Offsets');

                break;
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

            case Tokens::T_STATE:
                $this->msg = sprintf("Read STATE %s", $association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromFloatToHex($association->value), "Offset");

//                var_dump($association);
//                exit;
                break;
            case Tokens::T_VARIABLE:
                $this->msg = sprintf("Use Variable %s", $association->value);



                if ($association->assign !== false){
                    if ($association->varType == "vec3d") {
                        $this->add($association->section == "header" ? '21000000' : '22000000', 'Section ' . $association->section);
                        $this->add('04000000');
                        $this->add('01000000');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                        $this->add('10000000', 'Return');
                        $this->add('01000000', 'Return');
                    }

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
                    }else if ($association->assign->type == Tokens::T_STATE){
                        $this->add('16000000');
                        $this->add('04000000');
                        $this->add(Helper::fromIntToHex($association->assign->offset), 'Offset');
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
//                        var_dump($association);
//                        throw new \Exception(sprintf("Assign type %s not implemented", $association->varType));
                    }
                }


                if ($association->math !== false){

                    $this->msg = sprintf("Variable %s Math Operation ", $association->value);

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
            case Tokens::T_DO:
            case Tokens::T_IF:
                $this->msg = sprintf("IF Statement ");

                $startOffset = count($compiler->codes);
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
                                throw new \Exception(sprintf("No Return type available for function ->%s<-", $condition->value));
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

                        if ($condition->operatorValue == null) continue;

                        new Evaluate($this->compiler, $condition->operatorValue);


                        if ($compareWith == "integer") {

                            //abschluss von integer / const und wohl auch boolean
                            $this->add('0f000000', "Return Temp Result");
                            $this->add('04000000', "Return Temp Result");
                        }


//                        if ($condition->statementOperator == Tokens::T_AND){

                            $this->add('23000000');
                            $this->add('04000000');
                            $this->add('01000000');

                            $this->add('12000000');
                            $this->add('01000000');
                            $this->add('01000000');

                            switch ($condition->operator){
                                case Tokens::T_IS_EQUAL:
                                    $this->add('3f000000');
                                    break;
                                case Tokens::T_IS_NOT_EQUAL:
                                    $this->add('40000000');
                                    break;
                                case Tokens::T_IS_SMALLER:
                                    $this->add('3d000000');
                                    break;
                                case Tokens::T_IS_SMALLER_EQUAL:
                                    $this->add('3e000000');
                                    break;
                                case Tokens::T_IS_GREATER:
                                    $this->add('42000000');
                                    break;
                                case Tokens::T_IS_GREATER_EQUAL:
                                    $this->add('41000000');
                                    break;
                                default:
                                    throw new \Exception(sprintf('Evaluate:: Unknown statement operator %s', $condition->operator));
                                    break;
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

                    }

                    $this->add('24000000');
                    $this->add('01000000');
                    $this->add('00000000');
                    $this->add('3f000000');

//                    $endOffsets[] = count($compiler->codes);
                    $offset = count($compiler->codes);
                    $this->add('OFFSET', "Offset");

                    foreach ($case->onTrue as $item) {
                        new Evaluate($this->compiler, $item);
                    }

                    if (count($association->cases) != $index + 1){
                        $this->add('3c000000', 'Jump to');

                        $endOffsets[] = count($compiler->codes);
                        $this->add('END OFFSET', "End Offset");

                    /**
                     * The While do loops jumps back to start
                     */
                    }else if ($association->type == Tokens::T_DO ){
                        $this->add('3c000000', 'Jump to ' . ($startOffset * 4));

                        $this->add(Helper::fromIntToHex($startOffset * 4), "start Offset");
                    }

                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);

                }

                foreach ($endOffsets as $offset) {
                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;
            case Tokens::T_FUNCTION:

                foreach ($association->childs as $param) {


//                    $this->movePointer($param);
                    if ($param->varType == "string") {

                        // move the internal pointer to the offset

                        if (in_array($param->section, ['header', 'script']) !== false){
                            $this->add($param->section == "header" ? '21000000' : '22000000', 'Read String from Section ' . $param->section);
                            $this->add('04000000', 'Read String');
                            $this->add('01000000', 'Read String');
                            $this->add(Helper::fromIntToHex($param->offset), 'Offset');

                            //then read the given size
                            $this->add('12000000', 'Read String');
                            $this->add('02000000', 'Read String');
                            $this->add(Helper::fromIntToHex($param->sizeWithoutPad4), "Size of " . $param->sizeWithoutPad4);

                        }else{
                            //custom parameter
                            $this->add('13000000', 'Read String from Section ' . $param->section);
                            $this->add('01000000', 'Read String');
                            $this->add('04000000', 'Read String');
                            $this->add(substr(Helper::fromIntToHex($param->offset),0, 8), 'Offset');

                            //then read the given size
                            $this->add('12000000', 'Read String');
                            $this->add('02000000', 'Read String');
                            $this->add('00000000', "Offset / Size (todo)");

                        }

                    }

                    new Evaluate($this->compiler, $param);


                    /**
                     * i guess the procedure need only the pointer and not the actual value
                     */
                    if ($association->isProcedure === true) continue;


                    if($param->type == Tokens::T_STRING){
                        $stringIndex = substr($param->value, 4);
                        $string = $compiler->strings[$stringIndex];

                        $this->msg = sprintf("Read String %s", $param->value);

                        $this->add('12000000');
                        $this->add('02000000');
                        $this->add(Helper::fromIntToHex($string['size']), "Length");

                    }


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
                if ($association->isProcedure === true){
                    $this->add('10000000');
                    $this->add('01000000');

                    $this->add('10000000');
                    $this->add('04000000');
                    $this->add('11000000');
                    $this->add('02000000');
                    $this->add('00000000');
                    $this->add('32000000');
                    $this->add('02000000');
                    $this->add('1c000000');
                    $this->add('10000000');
                    $this->add('02000000');
                    $this->add('39000000');

                }

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

//                $this->msg = sprintf("Case %s", $association->value);
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
//
//                $this->msg = sprintf("Read String %s", $string['value']);
//
//                $this->add('12000000');
//                $this->add('02000000');
//                $this->add(Helper::fromIntToHex($string['size']), "Length");

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


    private function getTypeByAssociation( Associations $variable ){

        if ($variable->type == Tokens::T_VARIABLE){
            return $variable->varType;
        }

        if ($variable->type == Tokens::T_STRING) return 'string';
        if ($variable->type == Tokens::T_CONSTANT) return 'integer';
        if ($variable->type == Tokens::T_BOOLEAN) return 'integer';
        if ($variable->type == Tokens::T_INT) return 'integer';
        if ($variable->type == Tokens::T_FLOAT) return 'float';

        die ("cant convert");
    }

    private function movePointer( Associations $association ){

        $type = $this->getTypeByAssociation( $association );
var_dump($association->section);
        switch ($type){
            case 'string':


                if (in_array($association->section, ['header', 'script']) !== false){
                    $this->add($association->section == "header" ? '21000000' : '22000000', 'Read String from Section ' . $association->section);
                    $this->add('04000000', 'Read String');
                    $this->add('01000000', 'Read String');
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                    //then read the given size
                    $this->add('12000000', 'Read String');
                    $this->add('02000000', 'Read String');
                    $this->add(Helper::fromIntToHex($association->sizeWithoutPad4), "Size of " . $association->sizeWithoutPad4);

                }else{
                    //custom parameter
                    $this->add('13000000', 'Read String from Section ' . $association->section);
                    $this->add('01000000', 'Read String');
                    $this->add('04000000', 'Read String');
                    $this->add(substr(Helper::fromIntToHex($association->offset),0, 8), 'Offset');

                    //then read the given size
                    $this->add('12000000', 'Read String');
                    $this->add('02000000', 'Read String');
                    $this->add('00000000', "Offset / Size (todo)");

                }

                break;

        }
    }

    private function readData($type ){

        switch ($type){
            case 'string':
                break;

        }
    }
}