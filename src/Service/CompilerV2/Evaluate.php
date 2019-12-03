<?php

namespace App\Service\CompilerV2;

use App\Service\Helper;
use Exception;

class Evaluate{

    public $msg = "";

    /** @var Compiler */
    private $compiler;

    /**
     * Evaluate constructor.
     * @param Compiler $compiler
     * @param Associations $association
     * @throws Exception
     */
    public function __construct( Compiler $compiler, Associations $association )
    {

        $this->compiler = $compiler;
        
        switch ($association->type){
            
            case Tokens::T_PROCEDURE:
                $this->compiler->currentScriptName = $association->value;

                $this->msg = sprintf("Initialize Custom Function %s", $association->value);
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

                /**
                 * The last line represents the arguments
                 * Each argument reserve 4bytes.
                 * First 4bytes are always reserved.
                 */
                $variables = $compiler->getArgumentsByScriptName($association->value);
                $this->add(Helper::fromIntToHex(4 + (count($variables) * 4)), 'Variable count ' . count($variables));

                break;

            case Tokens::T_SCRIPT:
                $this->compiler->currentScriptName = $association->value;

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
                $this->msg = sprintf("Use Variable %s / %s", $association->value, $association->varType);

                if ($association->assign !== false){

                    /**
                     * Some Elements need to be initialized first
                     *
                     * Init left hand
                     */
                    if ($association->varType == "vec3d") {
                        $this->readData($association, "vec3d");

                    /**
                     * We assign to an array index
                     *
                     * itemsSpawned[1] := FALSE;
                     */
                    }else if ($association->fromArray == true) {
                        $this->readData($association, "array");

                        $this->add('10000000');
                        $this->add('01000000');

                        $this->add('12000000');
                        $this->add('01000000');
                        $this->add(Helper::fromIntToHex((int)$association->index), "Array index " . $association->index);

                        $this->add('34000000');
                        $this->add('01000000');
                        $this->add('01000000');
                        $this->add('12000000');
                        $this->add('04000000');
                        $this->add('04000000');
                        $this->add('35000000');
                        $this->add('04000000');
                        $this->add('0f000000');
                        $this->add('04000000');
                        $this->add('31000000');
                        $this->add('04000000');
                        $this->add('01000000');
                        $this->add('10000000');
                        $this->add('04000000');

                    }

                    if (
                        $association->assign->type == Tokens::T_FUNCTION ||
                        $association->assign->type == Tokens::T_VARIABLE
                    ){
                        new Evaluate($this->compiler, $association->assign);
                    }else{

                        $rightHandReturn = $this->getVarType($association->assign);
                        $this->readData($association->assign, $rightHandReturn);
                    }

                    /**
                     * These types accept only floats, given int need to be converted
                     */
                    if ($association->varType == "real"){
                        if ($association->assign->type == Tokens::T_INT){
                            $this->add('10000000');
                            $this->add('01000000');

                            //convert to float
                            $this->add('4d000000', 'Convert INT to FLOAT');

                        }
                    }

                    /**
                     * Block 2: Write to leftHand
                     */

                    $this->msg = sprintf("Assign to Variable %s", $association->value);

                    if ($association->fromArray == true) {
                        $this->writeData($association, "array");
                    }else{
                        $this->writeData($association, $association->varType);
                    }
                }

                if ($association->math !== false){

                    $this->msg = sprintf("Variable %s Math Operation ", $association->value);

                    $this->getPointer($association, $association->varType);

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
                        throw new Exception("Math-Type not implemented " . $association->math->type);
                    }
                }

                //we have a regular variable
                if ($association->assign === false && $association->math === false){
                    $this->getPointer($association, $association->varType);
                }

                break;
            case Tokens::T_CONDITION:

                $compareAgainst = false;

                $onlyConditions = true;
                foreach ($association->childs as $index => $param) {

                    $isState = $compiler->getState($param->varType);

                    if ($isState) {
                        $compareAgainst = "state";
                        $this->getPointer($param, "state");

                        //TODO das gehört doch auch in T_VARIABLE ODER ?!
                    }else if ($param->varType == "string") {
                        $compareAgainst = "string";
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "vec3d") {
                        $compareAgainst = "vec3d";
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "ecollectabletype") {
                        $compareAgainst = "ecollectabletype";
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "eaicombattype") {
                        $compareAgainst = "eaicombattype";
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }

                    new Evaluate($this->compiler, $param);

                    if ($param->type !== Tokens::T_CONDITION) $onlyConditions = false;

                    if ($association->operatorValue !== null){
                        $this->add('10000000', "return param");
                        $this->add('01000000', "return param");
                    }
                }

                if ($association->isNot === true){
                    $this->add('29000000', 'Not');
                    $this->add('01000000', 'Not');
                    $this->add('01000000', 'Not');
                }

                if ($association->operatorValue !== null){

                    //todo should check both sides to find the right type
                    if ($association->operatorValue->type == Tokens::T_STRING){
                        $this->add('10000000', 'Return string');
                        $this->add('02000000', 'Return string');
                    }

                    if ($compareAgainst == "state"){
                        $this->add('12000000', 'Simple Int');
                        $this->add('01000000', 'Simple Int');
                        $this->add(Helper::fromIntToHex($association->operatorValue->offset), ' state offset');

                    }else{
                        new Evaluate($this->compiler, $association->operatorValue);
                    }

                    if ($association->operatorValue->type == Tokens::T_STRING){
                        $this->add('12000000');
                        $this->add('02000000');
                        $this->add(Helper::fromIntToHex(strlen($association->operatorValue->value) + 1));

                        $this->add('10000000', 'Return string');
                        $this->add('01000000', 'Return string');

                        $this->add('10000000', 'Return string');
                        $this->add('02000000', 'Return string');


                        $this->add('49000000', 'compare string');

                    }else{

                        $this->add('0f000000', "Return last case");
                        $this->add('04000000', "Return last case");

                        $this->add('23000000');
                        $this->add('04000000');
                        $this->add('01000000');
                    }

                    $this->add('12000000');
                    $this->add('01000000');
                    $this->add('01000000');

                    switch ($association->operator){
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
                            throw new Exception(sprintf('Evaluate:: Unknown statement operator %s', $association->operator));
                            break;
                    }

                    $offset = count($compiler->codes);
                    $this->add('OFFSET', 'Offset 1');

                    $this->add('33000000');
                    $this->add('01000000');
                    $this->add('01000000');

                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                if ($association->statementOperator ){
                    $this->add('0f000000', "apply to operator");
                    $this->add('04000000', "apply to operator");

                    switch ($association->statementOperator){

                        case Tokens::T_OR:
                            $this->add('27000000', 'OR');
                            break;
                        case Tokens::T_AND:
                            $this->add('25000000', 'AND');
                            break;
                        default:
                            throw new Exception(sprintf('Evaluate: statementOperator =>  %s is not a valid operator !', $association->statementOperator));
                            break;
                    }

                    $this->add('01000000', 'apply operator ' . $association->statementOperator);
                    $this->add('04000000', 'apply operator ' . $association->statementOperator);
                }

                if ($association->isLastCondition !== true && $onlyConditions == false){

                    /**
                     * wenn die eine condition in einer condition ist, ist die außere condition im grunde leer
                     * daher darf dann auch kein 10 01 passieren
                     */
                    $this->add('10000000', "next condition");
                    $this->add('01000000', "next condition");
                }

                break;
            case Tokens::T_DO:
            case Tokens::T_IF:

                $this->msg = sprintf("IF Statement ");

                $startOffset = count($compiler->codes);

                $endOffsets = [];
                foreach ($association->cases as $index => $case) {

                     //apply the condition
                    /** @var Associations $condition */
                    foreach ($case->condition as $conditionIndex => $condition) {
                        new Evaluate($this->compiler, $condition);
                    }

                    $this->add('24000000');
                    $this->add('01000000');
                    $this->add('00000000');
                    $this->add('3f000000');

                    $offset = count($compiler->codes);
                    $this->add('OFFSET', "Offset 2");

                    foreach ($case->onTrue as $item) {
                        new Evaluate($this->compiler, $item);
                    }

                    if (count($association->cases) != $index + 1 || $case->onFalse !== null){
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

                    if ($case->onFalse !== null){
                        foreach ($case->onFalse as $item) {
                            new Evaluate($this->compiler, $item);
                        }

                    }

                }

                foreach ($endOffsets as $offset) {
                    $compiler->codes[$offset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                }

                break;

            case Tokens::T_FUNCTION:

                /**
                 * A special handler for writedebug calls
                 *
                 * any writedebug accept countless parameters and need to be seperated
                 * into single calls
                 *
                 * writedebug('here1', 'here2');
                 *
                 * need to be split into
                 *
                 * WriteDebug('here1');
                 * WriteDebug('here2');
                 * WriteDebugFlush();
                 */
                if (
                    strtolower($association->value) == "writedebug" &&
                    count($association->childs) > 1
                ){

                    foreach ($association->childs as $index => $param) {
                        $clone = clone $association;
                        $clone->childs[] = $param;

                        new Evaluate($compiler, $clone);
                    }
                    break;
                }

                foreach ($association->childs as $index => $param) {

                    //TODO das gehört doch auch in T_VARIABLE ODER ?!
                    if ($param->varType == "string") {
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "vec3d") {
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "ecollectabletype") {
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "eaicombattype") {
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }


                    new Evaluate($this->compiler, $param);

                    if ($association->forceFloat){
                        if($association->forceFloat[$index] === true){

                            // floats and REAL are the same...
                            if ($param->type !== Tokens::T_FLOAT && $param->varType != "real"){

                                $this->add('10000000');
                                $this->add('01000000');

                                $this->add('4d000000', 'integer to float');

                            }

                        }
                    }

                    /**
                     * i guess the procedure need only the pointer and not the actual value
                     */
                    if ($association->isProcedure === true){
                        $this->add('10000000');
                        $this->add('01000000');
                        continue;
                    }

                    if($param->type == Tokens::T_STRING){
                        $string = $compiler->strings4Script[strtolower($compiler->currentScriptName)][strtolower($param->value)];

                        $this->msg = sprintf("Read String %s", $string['value']);

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
                    if (
                        strtolower($param->value) == "getentityposition" ||
                        strtolower($param->value) == "getentityview"
                    ){

                    }else{
                        $this->msg = sprintf("Function %s Return", $association->value);
                        $this->add('10000000');
                        $this->add('01000000');
                    }

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


                if (strtolower($association->value) == "writedebug"){
                    $param = $association->childs[0];
                    if ($param->varType == "string" || $param->type == Tokens::T_STRING) {
                        $writeDebugFunction = $compiler->gameClass->getFunction('writedebugstring');
                    }else{
                        throw new Exception("Unknown WriteDebug function for " . $param->varType);
                    }

                    $this->add($writeDebugFunction['offset'], "Offset");
                    $this->add('74000000');
                }else{
                    $this->add($association->offset, "Offset");
                }

                break;

            case Tokens::T_SWITCH:
                /** @var Associations $caseVariable */
                $caseVariable = $association->value;

                $this->msg = sprintf("Switch %s", $caseVariable->value);

                /**
                 * TODO: das gehört in T_VARIABLE
                 */
                $isState = $compiler->getState($caseVariable->varType);

                if ($isState){
                    $this->msg = sprintf("Switch state %s %s", $caseVariable->value, $caseVariable->section);
                    $this->getPointer($caseVariable, 'state');
                }
                /**
                 * TODO: das gehört in T_VARIABLE
                 */

                new Evaluate($this->compiler, $caseVariable);

                $caseStartOffsets = [];
                $caseEndOffsets = [];

                $cases = array_reverse($association->cases);
                foreach (array_reverse($association->cases) as $index => $case) {

                    $realIndex = count($cases) - $index - 1;

                    $this->add('24000000', 'Case ' . $realIndex);
                    $this->add('01000000');

                    if (is_array($case->value)){
                        $this->add(Helper::fromIntToHex($case->value['offset']), 'case Offset');
                    }else{
                        $this->add(Helper::fromIntToHex($realIndex), 'case Offset');
                    }
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

            case Tokens::T_CONSTANT:
                $this->readData($association, 'constant');
                break;
            case Tokens::T_BOOLEAN:
                $this->readData($association, 'boolean');
                break;
            case Tokens::T_FLOAT:
                $this->readData($association, 'float');
                break;
            case Tokens::T_INT:
                $this->msg = sprintf("Handle Integer %s", $association->value);

                $negate = false;
                if ($association->value < 0){
                    $association->value *= -1;
                    $negate = true;
                }

                $this->readData($association, 'integer');

                if ($negate){
                    $this->add('2a000000', 'negate integer');
                    $this->add('01000000', 'negate integer');
                }

                break;
            case Tokens::T_STRING:
                $this->readData($association, 'string');
                break;
            case Tokens::T_CASE:

                foreach ($association->onTrue as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                break;

            default:
                throw new Exception(sprintf("Unable to evaluate %s ", $association->type));
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

        switch ($type){
            case 'eaicombattype':
            case 'ecollectabletype':
                $this->add('13000000', 'Read ecollectabletype from Section ' . $association->section);
                $this->add('01000000', 'Read ecollectabletype');
                $this->add('04000000', 'Read ecollectabletype');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                break;
            case 'vec3d':
                $this->add($association->section == "header" ? '21000000' : '22000000', 'Read String from Section ' . $association->section);
                $this->add('04000000', 'Read String');
                $this->add('01000000', 'Read String');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                break;
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
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                    //then read the given size
                    $this->add('12000000', 'Read String');
                    $this->add('02000000', 'Read String');
                    $this->add('00000000', "Offset / Size (todo)");
                }

                break;
        }
    }

    /**
     * @param Associations $association
     * @return bool|mixed|string|null
     * @throws Exception
     */
    private function getVarType(Associations $association){
        if ($association->type == Tokens::T_BOOLEAN) return 'boolean';
        if ($association->type == Tokens::T_INT) return 'integer';
        if ($association->type == Tokens::T_FLOAT) return 'float';
        if ($association->type == Tokens::T_STRING) return 'string';
        if ($association->type == Tokens::T_STATE) return 'state';
        if ($association->type == Tokens::T_CONSTANT) return 'constant';
        throw new Exception("Unable to resolve type " . $association->type);
    }

    private function writeData($association, $type ){
        switch ($type) {
            case 'state':
            case 'entityptr':
            case 'integer':
            case 'boolean':
                $this->add($association->section == "header" ? '16000000' : '15000000', 'Section ' . $association->section);
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                $this->add('01000000');
                break;
            case 'real':
                $this->add('16000000');
                $this->add('04000000');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                $this->add('01000000');
                break;
            case 'vec3d':
                $this->add('12000000');
                $this->add('03000000');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                $this->add('0f000000');
                $this->add('01000000');
                $this->add('0f000000');
                $this->add('04000000');
                $this->add('44000000');
                break;
            case 'array':
                $this->add('0f000000');
                $this->add('02000000');

                $this->add('17000000');
                $this->add('04000000', 'Offset maybe?');
                $this->add('02000000');
                $this->add('01000000');
                break;
        }

    }

    /**
     * @param $association
     * @param $type
     */
    private function getPointer($association, $type ){

        switch ($type) {
            case 'real':
            case 'state':
            case 'entityptr':
            case 'boolean':
            case 'integer':
                $this->add($association->section == "header" ? '14000000' : '13000000', $type . ' Pointer from Section ' . $association->section);
                $this->add('01000000', 'Read Boolean Variable');
                $this->add('04000000', 'Read Boolean Variable');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                break;
        }
    }

    /**
     * @param Associations $association
     * @param $type
     * @throws Exception
     */
    private function readData($association, $type ){

        switch ($type){
            case 'state':
                $this->msg = sprintf("Read STATE %s", $association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromFloatToHex($association->value), "Offset");

                break;
            case 'array':
                $this->msg = sprintf("Read array entry");

                $this->add('21000000');
                $this->add('04000000');
                $this->add('01000000');

                $this->add(Helper::fromIntToHex($association->offset), 'Offset');
                break;
            case 'string':

                $string = $this->compiler->strings4Script[strtolower($this->compiler->currentScriptName)][strtolower($association->value)];

                $this->msg = sprintf("Move String Pointer to %s", $string['offset']);

                $this->add('21000000');
                $this->add('04000000');
                $this->add('01000000');

                $this->add(Helper::fromIntToHex($string['offset']), 'Offset');

                break;
            case 'float':

                $negate = false;
                if ($association->value < 0){
                    $negate = true;
                    $association->value = $association->value * -1;
                }


                $this->msg = sprintf("Read Float %s", $association->value);
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromFloatToHex($association->value), "Offset");

                if ($negate){
                    $this->add('10000000');
                    $this->add('01000000');

                    $this->add('4f000000', 'Negate Float');
                    $this->add('32000000', 'Negate Float');
                    $this->add('09000000', 'Negate Float');
                    $this->add('04000000', 'Negate Float');
                }

                break;
            case 'vec3d':
                $this->add($association->section == "header" ? '21000000' : '22000000', 'Section ' . $association->section);
                $this->add('04000000');
                $this->add('01000000');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                $this->add('10000000', 'Return');
                $this->add('01000000', 'Return');
                break;
            case 'integer':
            case 'boolean':
                $this->add('12000000');
                $this->add('01000000');
                $this->add(Helper::fromIntToHex((int)$association->value), $type . ' ' . (int)$association->value);
                break;
            case 'constant':
                $this->add('12000000');
                $this->add('01000000');
                //todo das könnte direkt über die association var kommen...
                $this->add($this->compiler->gameClass->getConstant($association->value)['offset'], "Offset");
                break;

            default:
                throw new Exception(sprintf("ReadData unknown type %s", $type));
        }
    }
}