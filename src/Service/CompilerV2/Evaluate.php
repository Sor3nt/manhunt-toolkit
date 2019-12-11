<?php

namespace App\Service\CompilerV2;

use App\Service\Compiler\Token;
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

            case Tokens::T_MATH:
                $this->doMath($association->childs);
                break;
            case Tokens::T_SCRIPT:
            case Tokens::T_PROCEDURE:
                $this->compiler->currentScriptName = $association->value;
                $scriptSize = $compiler->getScriptSize($association->value);

                $this->compiler->evalVar->scriptStart($association->value);

                $compiler->evalVar->reserveMemory($scriptSize);

                foreach ($association->childs as $condition) {
                    new Evaluate($this->compiler, $condition);
                }

                if ($association->type == Tokens::T_PROCEDURE){
                    $this->compiler->evalVar->procedureEnd($association);
                }else{
                    $this->compiler->evalVar->scriptEnd($association->value);
                }

                break;


            case Tokens::T_ASSIGN:
                $compiler->evalVar->msg = sprintf("Process Assign %s", $association->value);

                /**
                 * Some Elements need to be initialized first
                 *
                 * Init left hand
                 */

                if ($association->varType == "vec3d") {
                    $this->readData($association, "vec3d");
                    $this->compiler->evalVar->ret();

                //we access a object attribute
                }else if (
                    $association->parent != null &&
                    $association->parent->varType == "vec3d"
                ) {
                    $this->readData($association->parent, "vec3d");
                    $this->compiler->evalVar->ret();

                    //we do not assign to the first entry
                    if ($association->parent->value . '.x' !== $association->value){
                        $this->add('0f000000', 'assign to secondary');
                        $this->add('01000000', 'assign to secondary');

                        $this->add('32000000', 'assign to secondary');
                        $this->add('01000000', 'assign to secondary');
                        $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);

                        $this->compiler->evalVar->ret();
                    }
                }



                /**
                 * We assign to an array index (by id)
                 *
                 * itemsSpawned[1] := FALSE;
                 */
                if ($association->forIndex !== null) {

                    $this->compiler->evalVar->memoryPointer($association);

                    $this->compiler->evalVar->ret();

                    new Evaluate($this->compiler, $association->forIndex);


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

                if ($association->fromArray == true) {
                    $this->readData($association, "array");

                    $this->compiler->evalVar->ret();

                    $this->compiler->evalVar->valuePointer( (int)$association->index );

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
                    $association->assign->type == Tokens::T_FLOAT ||
                    $association->assign->type == Tokens::T_INT ||
                    $association->assign->type == Tokens::T_FUNCTION ||
                    $association->assign->type == Tokens::T_VARIABLE
                ) {
                    new Evaluate($this->compiler, $association->assign);
                }else if (
                    $association->assign->type == Tokens::T_MATH
                ){
                    $this->doMath($association->assign->childs, $association->varType);
                }else{

                    $rightHandReturn = $this->getVarType($association->assign);
                    $this->readData($association->assign, $rightHandReturn);
                }

                /**
                 * These types accept only floats, given int need to be converted
                 */
                if ($association->varType == "real" && $association->assign->type == Tokens::T_INT){
                    $this->compiler->evalVar->int2float();
                }

                /**
                 * Block 2: Write to leftHand
                 */
                $compiler->evalVar->msg = sprintf("Assign to Variable %s", $association->value);

                if ($association->fromArray == true) {
                    $this->writeData($association, "array");
                }else if (
                    $association->parent != null &&
                    $association->parent->varType == "vec3d"
                ) {
                    $this->add('0f000000');
                    $this->add('02000000');

                    $this->add('17000000');
                    $this->add('04000000');
                    $this->add('02000000');
                    $this->add('01000000');
                }else{
                    $this->writeData($association, $association->varType);
                }

                break;


            case Tokens::T_VARIABLE:
                $compiler->evalVar->msg = sprintf("Use Variable %s / %s", $association->value, $association->varType);

                if ($association->isGameVar === true){
                    $compiler->evalVar->gameVarPointer($association);
                }else if ($association->fromArray === true) {
                    $this->compiler->evalVar->memoryPointer($association);

                }else{
                    $compiler->evalVar->variablePointer($association);
                }


                break;

            case Tokens::T_FOR:


                new Evaluate($this->compiler, $association->start);

                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('15000000');
                $this->add('04000000');
                $this->add('20000000');
                $this->add('01000000');

                $startOffset = count($this->compiler->codes);

                new Evaluate($this->compiler, $association->end);


                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('13000000');
                $this->add('02000000');
                $this->add('04000000');
                $this->add('20000000');
                $this->add('23000000');
                $this->add('01000000');
                $this->add('02000000');
                $this->add('41000000');
                $this->add('00390000');

                $this->add('3c000000');
                $endOffset = count($this->compiler->codes);
                $this->add('offset', 'End Offset');

                foreach ($association->onTrue as $item) {
                    new Evaluate($this->compiler, $item);
                }

                $compiler->evalVar->msg = sprintf("For statement");
                $this->add('2f000000');
                $this->add('04000000');
                $this->add('1c000000');


                $this->add('3c000000');
                $this->add(Helper::fromIntToHex($startOffset * 4), 'Start Offset');


                $compiler->codes[$endOffset]['code'] = Helper::fromIntToHex(count($compiler->codes) * 4);
                $this->add('30000000');
                $this->add('04000000');
                $this->add('1c000000');

                break;

            case Tokens::T_CONDITION:
                $compiler->evalVar->msg = sprintf("IF Condition");

                $compareAgainst = false;

                $onlyConditions = true;
                foreach ($association->childs as $index => $param) {

                    $isState = $compiler->getState($param->varType);

                    if ($isState) {
                        $compareAgainst = "state";

                        $compiler->evalVar->variablePointer($param, "state");

                        //TODO das gehört doch auch in T_VARIABLE ODER ?!
//                    }else if ($param->fromArray === true) {
//                        $this->compiler->evalVar->memoryPointer($param);

                    }else if ($param->varType == "string") {
                        $compareAgainst = "string";
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "vec3d") {
                        $compareAgainst = "vec3d";
                        // move the internal pointer to the offset
                        $this->compiler->evalVar->memoryPointer($param);
                    }else if ($param->varType == "ecollectabletype") {
                        $compareAgainst = "ecollectabletype";
                        // move the internal pointer to the offset
                        $this->compiler->evalVar->memoryPointer($param);
                    }else if ($param->varType == "eaicombattype") {
                        $compareAgainst = "eaicombattype";
                        // move the internal pointer to the offset
                        $this->compiler->evalVar->memoryPointer($param);
                    }else{
                        var_dump($association->type);
                    }

                    new Evaluate($this->compiler, $param);

                    if ($param->type !== Tokens::T_CONDITION) $onlyConditions = false;

                    if ($association->operatorValue !== null){
                        $this->compiler->evalVar->ret();
                    }
                }

                if ($association->isNot === true){
                    $compiler->evalVar->not();
                }

                if ($association->operatorValue !== null){

                    //todo should check both sides to find the right type
                    if ($association->operatorValue->type == Tokens::T_STRING){
                        $this->add('10000000', 'Return string');
                        $this->add('02000000', 'Return string');
                    }

                    if ($compareAgainst == "state"){
                        $this->compiler->evalVar->valuePointer($association->operatorValue->offset);

                    }else{
                        new Evaluate($this->compiler, $association->operatorValue);
                    }

                    if ($association->operatorValue->type == Tokens::T_STRING){
                        $this->compiler->evalVar->readSize( strlen($association->operatorValue->value) + 1 );

                        $this->compiler->evalVar->retString();

                        $this->add('49000000', 'compare string ' . $association->operatorValue->value);

                    }else if ($association->operatorValue->type == Tokens::T_FLOAT){
                        //value evaluated already just apply the return
                        $this->compiler->evalVar->ret();

                        $this->add('4e000000', 'compare float');

                    }else{

                        $this->add('0f000000', "Return last case");
                        $this->add('04000000', "Return last case");

                        $this->add('23000000');
                        $this->add('04000000');
                        $this->add('01000000');
                    }

                    $this->compiler->evalVar->valuePointer(1);

                    switch ($association->operator){
                        case Tokens::T_IS_SMALLER:
                            $this->add('3d000000');
                            break;
                        case Tokens::T_IS_SMALLER_EQUAL:
                            $this->add('3e000000');
                            break;
                        case Tokens::T_IS_EQUAL:
                            $this->add('3f000000');
                            break;
                        case Tokens::T_IS_NOT_EQUAL:
                            $this->add('40000000');
                            break;
                        case Tokens::T_IS_GREATER_EQUAL:
                            $this->add('41000000');
                            break;
                        case Tokens::T_IS_GREATER:
                            $this->add('42000000');
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
                    $this->compiler->evalVar->ret();
                }

                break;
            case Tokens::T_DO:
            case Tokens::T_IF:

                $compiler->evalVar->msg = sprintf("IF Statement ");

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
                        $clone->childs = [$param];
                        $clone->isLastWriteDebugParam = count($association->childs) == $index + 1;

                        new Evaluate($compiler, $clone);
                    }
                    break;
                }

                $compiler->evalVar->msg = sprintf("Process Function %s", $association->value);
                foreach ($association->childs as $index => $param) {

                    //TODO das gehört doch auch in T_VARIABLE ODER ?!
                    if ($param->varType == "string") {
                        // move the internal pointer to the offset
                        $this->movePointer($param);
                    }else if ($param->varType == "vec3d") {
                        // move the internal pointer to the offset
                        $this->compiler->evalVar->memoryPointer($param);
                    }

                    new Evaluate($this->compiler, $param);

                    $compiler->evalVar->msg = sprintf("Process Function %s", $association->value);
                    if ($association->forceFloat){
                        if($association->forceFloat[$index] === true){
                            if ($param->type == Tokens::T_MATH){

                            }else{

                                // floats and REAL are the same...
                                if ($param->type !== Tokens::T_FLOAT && $param->varType != "real"){

                                    $this->compiler->evalVar->ret();
                                    $this->add('4d000000', 'integer to float1 ');

                                }

                            }

                        }
                    }

                    /**
                     * i guess the procedure need only the pointer and not the actual value
                     */
                    if ($association->isProcedure === true){
                        $this->compiler->evalVar->ret();
                        continue;
                    }

                    if($param->type == Tokens::T_STRING){
                        if ($param->value !== " "){
                            $string = $compiler->strings4Script[strtolower($compiler->currentScriptName)][strtolower($param->value)];
                            $this->compiler->evalVar->readSize( $string->size );
                        }
                    }

                    /**
                     * Mystery : these function dont require a return, never
                     */
                    if (
                        strtolower($param->value) == "getentityposition" ||
                        strtolower($param->value) == "getplayerposition" ||
                        strtolower($param->value) == "getentityview" ||
                        strtolower($param->value) == "getentityname"
                    ){

                    //Attribute access dont need a return....
                    }else if ( $param->parent != null){
                        //do nothing


                    //regular parameter return
                    }else if ( $param->type == Tokens::T_STRING || $param->varType == "string" ){
                        // TODO: the param should be converted into a simple int to avoid these hacks
                        if ($param->value !== " "){
                            $this->compiler->evalVar->retString();
                        }

                    }else{
                        $this->compiler->evalVar->ret();
                    }
                }

                $compiler->evalVar->msg = sprintf("Call Function %s", $association->value);
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

                        //Not sure about this part, a space require a different handling
                        if($param->value === " "){
                            $writeDebugFunction = $compiler->gameClass->getFunction('writedebugemptystring');
                        }else{
                            $writeDebugFunction = $compiler->gameClass->getFunction('writedebugstring');
                        }
                    }else if ($param->varType == "float") {
                        $writeDebugFunction = $compiler->gameClass->getFunction('writedebugfloat');
                    }else if ($param->type == Tokens::T_FUNCTION) {
                        switch ($param->return){
                            case 'string':
                                $writeDebugFunction = $compiler->gameClass->getFunction('writedebugstring');
                                break;
                            case 'float':
                                $writeDebugFunction = $compiler->gameClass->getFunction('writedebugfloat');
                                break;
                            default:
                                throw new Exception("Unknown WriteDebug function return " . $param->return);


                        }
                    }else{
                        var_dump($param);
                        throw new Exception("Unknown WriteDebug function for " . $param->varType);
                    }

                    $this->add($writeDebugFunction['offset'], "Offset");

                    if ($association->isLastWriteDebugParam === null || $association->isLastWriteDebugParam === true){
                        $this->add('74000000');
                    }
                }else{
                    $this->add($association->offset, "Offset");
                }

                break;

            case Tokens::T_SWITCH:
                /** @var Associations $caseVariable */
                $caseVariable = $association->value;

                $compiler->evalVar->msg = sprintf("Switch %s", $caseVariable->value);

                /**
                 * TODO: das gehört in T_VARIABLE
                 */
                $isState = $compiler->getState($caseVariable->varType);

                if ($isState){
                    $compiler->evalVar->variablePointer($caseVariable, 'state');
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
                $compiler->evalVar->msg = sprintf("Handle Integer %s", $association->value);

                $this->readData($association, 'integer');


                break;
            case Tokens::T_STRING:

                if ($association->value === " "){
                    $this->compiler->evalVar->valuePointer(32);

                }else{
                    $this->readData($association, 'string');
                }
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
        $msg = $this->compiler->evalVar->msg;

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
            case 'string':
                if (in_array($association->section, ['header', 'script']) !== false){

                    $this->compiler->evalVar->memoryPointer($association);
                    $this->compiler->evalVar->readSize( $association->sizeWithoutPad4 );

                }else{


                    //custom parameter
                    $this->add('13000000', 'Read String from Section ' . $association->section);
                    $this->add('01000000', 'Read String');
                    $this->add('04000000', 'Read String');
                    $this->add(Helper::fromIntToHex($association->offset), 'Offset');

                    //then read the given size
                    $this->compiler->evalVar->readSize( 0 );
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
            case 'real':
                $this->add($association->section == "header" ? '16000000' : '15000000', 'Section ' . $association->section);
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
     * @param Associations $association
     * @param $type
     * @throws Exception
     */
    private function readData($association, $type ){

        switch ($type){
            case 'integer':
            case 'state':
            case 'float':
            case 'constant':

                $this->compiler->evalVar->valuePointer($association->offset );
                if ($association->negate) $this->compiler->evalVar->negate($association);
                break;

            case 'vec3d':
            case 'array':
            case 'string':
                $this->compiler->evalVar->memoryPointer( $association );
                break;

            default:
                throw new Exception(sprintf("ReadData unknown type %s", $type));
        }
    }

    /**
     * @param Associations[] $associations
     * @param null $varType
     * @throws Exception
     */
    public function doMath( $associations, $varType = null ){


        /**
         * Sometimes we need to look around which vartype we have...
         */
        if ($varType == null){

            foreach ($associations as $association) {
                if (
                    $association->type == Tokens::T_INT ||
                    $association->varType == 'integer'
                ){
                    $varType = "integer";
                    break;
                }

                if (
                    $association->type == Tokens::T_FLOAT ||
                    $association->varType == 'float' ||
                    $association->varType == 'real'
                ){
                    $varType = "float";
                    break;
                }

                if (
                    $association->type == Tokens::T_FUNCTION &&
                    $association->return !== null
                ){
                    $varType = $association->return;
                    break;
                }
            }
        }


        if ($varType == null){
            throw new \Exception("Unable to detect vartype");
        }


        $this->compiler->evalVar->msg = sprintf("Math Operation ");

        foreach ($associations as $index => $association) {



            if (in_array($association->type, [
                Tokens::T_ADDITION,
                Tokens::T_SUBSTRACTION,
                Tokens::T_DIVISION,
                Tokens::T_MULTIPLY,
            ])){
                $isLast = count($associations) == $index + 1;
                $this->compiler->evalVar->math($association->type, $varType);

                if ($isLast == false) $this->compiler->evalVar->ret();

                /**
                 * Looks like a hack but the extra return appears only in
                 * function parameters that ends with a multiply operation...
                 */
                if ($isLast == true && $association->type == Tokens::T_MULTIPLY){
                    $this->compiler->evalVar->ret();
                }

            }else{
                //we reached the last token followed by a operator
                $isLast = count($associations) == $index + 2;

                new Evaluate($this->compiler, $association);


                if (
                    ($varType == "real" || $varType == "float")
                    ||
                    ($varType == "integer" && $isLast == false)
                ){
                    $this->compiler->evalVar->ret();

                }


                if (
                    ($varType == "real" || $varType == "float") &&
                    $association->type == Tokens::T_INT
                ){
                    $this->add('4d000000', 'integer to float2');
                    $this->compiler->evalVar->ret();

                }

            }


        }

    }
}