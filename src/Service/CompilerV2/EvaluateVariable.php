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

    public function ret(){
        $this->add('10000000', 'Return');
        $this->add('01000000', 'Return');
    }

    public function retString(){
        $this->ret();

        $this->add('10000000', 'Return String');
        $this->add('02000000', 'Return String');
    }

    public function readSize( int $offset){
        $this->msg = "read data ";
        $this->add('12000000');
        $this->add('02000000');
        $this->add(Helper::fromIntToHex($offset), 'Size of ' . $offset);
    }

    public function valuePointer($offset ){

        $this->msg = "simple value pointer";
        $this->add('12000000', 'Read ' . $offset);
        $this->add('01000000', 'Read ' . $offset);
        $this->add(
            is_int($offset) ?
                    Helper::fromIntToHex($offset) :
                    Helper::fromFloatToHex($offset),
            "Offset"
        );
    }

    public function variablePointer( Associations $association, $type = null){
        $type = is_null($type) ? $association->varType : $type;

        if (in_array($type,
            ['real', 'state', 'entityptr', 'boolean', 'integer', 'eaicombattype', 'ecollectabletype']
        ) !== false ){
            $this->add($association->section == "header" ? '14000000' : '13000000', $type . ' from Section ' . $association->section);
            $this->add('01000000', 'Read Variable ' . $association->value);
            $this->add('04000000', 'Read Variable ' . $association->value);
            $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);
        }
    }

    public function gameVarPointer( Associations $association){
        $this->add($association->section == "header" ? '1e000000' : '1e000000', $association->varType . ' from Section ' . $association->section);
        $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);
        $this->add('04000000', 'Read value ' . $association->value);
        $this->add('01000000', 'Read value ' . $association->value);

    }
    public function memoryPointer( Associations $association){
        $this->add($association->section == "header" ? '21000000' : '22000000', $association->varType . ' from Section ' . $association->section);
        $this->add('04000000', 'Read memory');
        $this->add('01000000', 'Read memory');
        $this->add(Helper::fromIntToHex($association->offset), 'Offset ' . $association->offset);

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
    public function math( $type ){
        $this->msg = "Math Operator";

        $this->add('0f000000', 'init');
        $this->add('04000000', 'init');

        if ($type == Tokens::T_ADDITION) {
            $this->add('31000000', 'T_ADDITION');
            $this->add('01000000', 'T_ADDITION');
            $this->add('04000000', 'T_ADDITION');
        }else if ($type == Tokens::T_MULTIPLY){
            $this->add('35000000', 'T_MULTIPLY');
            $this->add('04000000', 'T_MULTIPLY');
        }else if ($type == Tokens::T_SUBSTRACTION){
            $this->add('33000000', 'T_SUBSTRACTION');
            $this->add('04000000', 'T_SUBSTRACTION');
            $this->add('01000000', 'T_SUBSTRACTION');

            $this->add('11000000', 'T_SUBSTRACTION');
            $this->add('01000000', 'T_SUBSTRACTION');
            $this->add('04000000', 'T_SUBSTRACTION');
        }else if ($type == Tokens::T_DIVISION){
            $this->add('00000000', 'T_DIVISION');
        }else{
            throw new Exception("Math-Type not implemented " . $type);
        }
    }

    public function reserveMemory(int $size){
        if ($size == 0 ) return;
        $this->msg = sprintf("Reserve Memory %s", $size);

        $this->add('34000000');
        $this->add('09000000');
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

        /**
         * The last line represents the arguments
         * Each argument reserve 4bytes.
         * First 4bytes are always reserved.
         */
        $variables = $this->compiler->getArgumentsByScriptName($association->value);
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
}