<?php

namespace App\Service\CompilerV2;

use App\Service\Helper;
use Exception;

class EvaluateVariable{

    public $msg = "";

    /** @var Compiler */
    private $compiler;


    public function __construct( Compiler $compiler )
    {

        $this->compiler = $compiler;
    }

    public function ret( $msg = "" ){
        $this->add('10000000', 'Return ' . $msg);
        $this->add('01000000', 'Return ' . $msg);
    }

    public function retString(){
        $this->ret();

        $this->add('10000000', 'Return String');
        $this->add('02000000', 'Return String');
    }

    public function readSize( int $offset){
//        $this->msg = "read data ";
        $this->add('12000000', 'read data');
        $this->add('02000000', 'read data');
        $this->add(Helper::fromIntToHex($offset), 'Size of ' . $offset);
    }

    public function valuePointer($offset ){

        $this->add('12000000', 'Simple Read ' . $offset);
        $this->add('01000000', 'Simple Read ' . $offset);
        $this->add(
            is_int($offset) ?
                    Helper::fromIntToHex($offset) :
                    Helper::fromFloatToHex($offset),
            "Offset " . (is_int($offset) ? "as int" : "as float")
        );
    }

    public function variablePointer( Associations $association, $type = null){
        $type = is_null($type) ? $association->varType : $type;

        if (
            $association->parent != null &&
            $association->parent->varType == "vec3d"
        ) {

            $this->compiler->evalVar->memoryPointer($association->parent);

            $this->compiler->evalVar->ret();

            if ($association->parent->value . '.x' !== $association->value) {
                $this->add('0f000000', 'object secondary');
                $this->add('01000000', 'object secondary');

                $this->add('32000000', 'object secondary value');
                $this->add('01000000', 'object secondary value');
                $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);

                $this->compiler->evalVar->ret();
            }


            //read attribute from vec3d
            $this->add('0f000000');
            $this->add('02000000');

            $this->add('18000000');
            $this->add('01000000');
            $this->add('04000000', 'Offset for ' . $association->value);
            $this->add('02000000');

        }else{
            if (in_array($type,
                    ['real', 'float', 'state', 'entityptr', 'boolean', 'integer', 'eaicombattype', 'ecollectabletype']
                ) !== false ){
                $this->add($association->section == "header" ? '14000000' : '13000000', $type . ' from Section ' . $association->section);
                $this->add('01000000', 'Read Variable ' . $association->value);
                $this->add('04000000', 'Read Variable ' . $association->value);
                $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);
            }
        }

    }

    public function gameVarPointer( Associations $association){
        $this->add($association->section == "header" ? '1e000000' : '1e000000', $association->varType . ' from Section ' . $association->section);
        $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);
        $this->add('04000000', 'Read value ' . $association->value);
        $this->add('01000000', 'Read value ' . $association->value);

    }
    public function levelVarPointer( Associations $association){
        $this->add($association->section == "header" ? '1b000000' : '1b000000', $association->varType . ' from Section ' . $association->section);
        $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);
        $this->add('04000000', 'Read value ' . $association->value);
        $this->add('01000000', 'Read value ' . $association->value);

    }
    public function memoryPointer( Associations $association){
        $this->add($association->section == "header" || $association->section == "constant" ? '21000000' : '22000000', $association->value . ' from Section ' . $association->section);
        $this->add('04000000', 'Read memory');
        $this->add('01000000', 'Read memory');
        $this->add(Helper::fromIntToHex($association->offset), 'Offset for ' . $association->value);

    }

    public function setCompareMode( $mode ){

        // all the same, convert them into integer
        if ($mode == "boolean") $mode = "integer";
        if ($mode == "entityptr") $mode = "integer";
        if ($mode == "state") $mode = "integer";

        if ($mode == "float"){
            $this->add('4e000000', 'compare float');
            $this->compiler->evalVar->valuePointer(1);

        }elseif ($mode == "string"){

            $this->add('49000000', 'compare string ');
            $this->compiler->evalVar->valuePointer(1);

        }elseif ($mode == "integer"){
            $this->add('0f000000', "compare integer");
            $this->add('04000000', "compare integer");

            $this->add('23000000', "compare integer");
            $this->add('04000000', "compare integer");
            $this->add('01000000', "compare integer");

            $this->compiler->evalVar->valuePointer(1);
        }
    }

    public function negate(Associations $association){
        if (is_float($association->value)){
            $this->compiler->evalVar->ret();

            $this->add('4f000000', 'Negate Float');
            $this->add('32000000', 'Negate Float');
            $this->add('09000000', 'Negate Float');
            $this->add('04000000', 'Negate Float');

        }else{
            $this->add('2a000000', 'Negate Integer');
            $this->add('01000000', 'Negate Integer');

        }
    }

    public function not(){
        $this->add('29000000', 'Not');
        $this->add('01000000', 'Not');
        $this->add('01000000', 'Not');
    }

    public function int2float(){
        $this->ret();

        //convert to float
        $this->add('4d000000', 'Convert INT to FLOAT');
    }

    /**
     * @param $type
     * @throws Exception
     */
    public function math( $type, $varType ){


        if ($varType !== "float" && $varType !== "integer"){
            throw new \Exception("Math handler, received no float/int type!");
        }

        if ($varType == "float"){

            if ($type == Tokens::T_ADDITION) {
                $this->add('50000000', 'T_ADDITION (float)');
            }else if ($type == Tokens::T_MULTIPLY){
                $this->add('52000000', 'T_MULTIPLY (float)');
            }else if ($type == Tokens::T_SUBSTRACTION){
                $this->add('51000000', 'T_SUBSTRACTION (float)');
            }else if ($type == Tokens::T_DIVISION){
                $this->add('53000000', 'T_DIVISION (float)');
            }else{
                throw new Exception("Math-Type not implemented " . $type);
            }


        }else{

            $this->add('0f000000', 'integer math');
            $this->add('04000000', 'integer math');

            if ($type == Tokens::T_ADDITION) {
                $this->add('31000000', 'T_ADDITION (int)');
                $this->add('01000000', 'T_ADDITION (int)');
                $this->add('04000000', 'T_ADDITION (int)');
            }else if ($type == Tokens::T_MULTIPLY){
                $this->add('35000000', 'T_MULTIPLY (int)');
                $this->add('04000000', 'T_MULTIPLY (int)');
            }else if ($type == Tokens::T_SUBSTRACTION){
                $this->add('33000000', 'T_SUBSTRACTION (int)');
                $this->add('04000000', 'T_SUBSTRACTION (int)');
                $this->add('01000000', 'T_SUBSTRACTION (int)');

                $this->add('11000000', 'T_SUBSTRACTION (int)');
                $this->add('01000000', 'T_SUBSTRACTION (int)');
                $this->add('04000000', 'T_SUBSTRACTION (int)');
            }else if ($type == Tokens::T_DIVISION){
                $this->add('4d000000', 'T_DIVISION (int)');
            }else{
                throw new Exception("Math-Type not implemented " . $type);
            }

        }
    }

    public function reserveMemory(int $size){
        if ($size == 0 ) return;

        $this->add('34000000', 'Reserve Memory');
        $this->add('09000000', 'Reserve Memory');
        $this->add(Helper::fromIntToHex($size), 'Size of ' . $size);
    }

    public function scriptStart( $blockName ){
        $this->msg = sprintf("Initialize Script %s", $blockName);
        $this->add('10000000');
        $this->add('0a000000');
        $this->add('11000000');
        $this->add('0a000000');
        $this->add('09000000');
    }

    public function scriptEnd( $blockName ){
        $this->msg = sprintf("Closing Script %s", $blockName);
        $this->add('11000000');
        $this->add('09000000');
        $this->add('0a000000');
        $this->add('0f000000');
        $this->add('0a000000');
        $this->add('3b000000');
        $this->add('00000000');

    }

    public function procedureEnd( Associations $association ){
        $this->msg = sprintf("Closing Procedure %s", $association->value);
        $this->add('11000000');
        $this->add('09000000');
        $this->add('0a000000');
        $this->add('0f000000');
        $this->add('0a000000');
        $this->add('3a000000');

//        $this->add(Helper::fromIntToHex(count($this->compiler->codes) * 4), 'End Offset');

        /**
         * The last line represents the arguments
         * Each argument reserve 4bytes.
         * First 4bytes are always reserved.
         */
        $variables = $this->compiler->getProcedureArgumentsByScriptName($association->value);
        $this->add(Helper::fromIntToHex(4 + (count($variables) * 4)), 'Variable count ' . count($variables));
    }

    private function add($code, $appendix = null ){
        $msg = $this->msg;

        if (!is_null($appendix)) $msg .= ' | ' . $appendix;


        $this->compiler->codes[] = [
            'code' => $code,
            'msg' => $msg
        ];
    }

    /**
     * @param Associations $association
     * @throws Exception
     */
    public function readFromArrayIndex( Associations $association ){

        $this->compiler->evalVar->memoryPointer($association);
        $this->compiler->evalVar->ret();

        if ($association->forIndex != null){
            new Evaluate($this->compiler, $association->forIndex);
        }else{
            //todo, no int convertion should happen here...
            $this->compiler->evalVar->valuePointer((int)$association->index);
        }
//var_dump($association);
        if ($association->typeOf == "vec3d" || $association->varType == "vec3d"){
            $this->readArray(12);
        }else{
            $this->readArray(4);
        }
    }

    public function readArray($indexVariableOffset = 4){

        $msg = "Read array";

        $this->add('34000000', $msg);
        $this->add('01000000', $msg);
        $this->add('01000000', $msg);

        $this->add('12000000', $msg);
        $this->add('04000000', $msg);
        $this->add(Helper::fromIntToHex($indexVariableOffset), 'index variable offsety');

        $this->add('35000000', $msg);
        $this->add('04000000', $msg);

        $this->add('0f000000', $msg);
        $this->add('04000000', $msg);

        $this->add('31000000', $msg);
        $this->add('04000000', $msg);
        $this->add('01000000', $msg);
        $this->add('10000000', $msg);
        $this->add('04000000', $msg);

    }
}


/**
 * Class RPN
 * Based on https://github.com/skugubaev/RPN thank u skugubaev!
 * @package App\Service\CompilerV2
 */
Class RPN {


    /**
     * @param Associations[] $tokens
     * @return array|string
     */
    public function convertToReversePolishNotation($tokens)
    {

        /** @var Associations[] $stack */
        $stack = [];

        /** @var Associations[] $result */
        $result = [];

        foreach ($tokens as $symbol) {

            switch ($symbol->type) {

                case Tokens::T_BRACKET_OPEN:
                    array_push($stack, $symbol);
                    break;
                case Tokens::T_BRACKET_CLOSE:
                    $operand = array_pop($stack);
                    while ($operand->type !=  Tokens::T_BRACKET_OPEN) {
                        $result[] = $operand;
                        $operand = array_pop($stack);
                    }
                    break;
                default:
                    if (in_array($symbol->type, [
                        Tokens::T_IS_SMALLER,
                        Tokens::T_IS_SMALLER_EQUAL,
                        Tokens::T_IS_GREATER_EQUAL,
                        Tokens::T_IS_GREATER,
                        Tokens::T_IS_EQUAL,
                        Tokens::T_IS_NOT_EQUAL,
                        'T_ADDITION',
                        'T_SUBSTRACTION',
                        'T_MULTIPLY',
                        'T_DIVISION',
                        'T_AND',
                        'T_OR',
                        'T_NOT'
                    ])) {
                        if (empty($stack)) {
                            array_push($stack, $symbol);
                        } else {
                            $weight = $this->getWeight($symbol);
                            $operand = array_pop($stack);

                            if ($weight <= $this->getWeight($operand)) {
                                while ($weight <= $this->getWeight($operand)) {
                                    $result[] = $operand;
                                    if (empty($stack)) {
                                        break;
                                    }
                                    $operand = array_pop($stack);
                                }
                                if ($weight > $this->getWeight($operand)) {
                                    array_push($stack, $operand);
                                }
                            } else {
                                array_push($stack, $operand);
                            }
                            array_push($stack, $symbol);
                        }
                        break;

                    } else {
                        $result[] = $symbol;
                    }

                    break;
            }

        }

        while (!empty($stack)) {
            $result[] = array_pop($stack);
        }

        return $result;
    }

    private function getWeight(Associations $operation)
    {
        $result = 0;
        switch ($operation->type) {
            case 'T_SUBSTRACTION':
            case 'T_ADDITION':
            case 'T_AND':
                $result = 2;
                break;
            case 'T_MULTIPLY':
            case 'T_DIVISION':
            case 'T_OR':
                $result = 3;
                break;
            case 'T_NOT':

            case Tokens::T_IS_GREATER:
            case Tokens::T_IS_GREATER_EQUAL:
            case Tokens::T_IS_EQUAL:
            case Tokens::T_IS_NOT_EQUAL:
            case Tokens::T_IS_SMALLER_EQUAL:
            case Tokens::T_IS_SMALLER:
            case '(':
                $result = 1;
                break;
        }

        return $result;
    }

}