<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;

class Emitter extends Helper {

    private $variables = [];

    /** @var Lines */
    private $lines;

    private $strings;

    private $types;

    private $emitters = [
        'T_DEFINE_SECTION_ENTITY' => Emitter\T_DEFINE_SECTION_ENTITY::class,
        'T_DEFINE_SECTION_VAR' => Emitter\T_DEFINE_SECTION_VAR::class,
        'T_DEFINE_SECTION_TYPE' => Emitter\T_DEFINE_SECTION_TYPE::class,
        'T_DEFINE_TYPE' => Emitter\T_DEFINE_TYPE::class,
        'T_FALSE' => Emitter\T_FALSE::class,
        'T_TRUE' => Emitter\T_TRUE::class,
        'T_SCRIPT' => Emitter\T_SCRIPT::class,
        'T_PROCEDURE' => Emitter\T_PROCEDURE::class,
        'T_WHILE' => Emitter\T_WHILE::class,
        'T_ASSIGN' => Emitter\T_ASSIGN::class,
        'T_INT' => Emitter\T_INT::class,
        'T_IF' => Emitter\T_IF::class,
        'T_SELF' => Emitter\T_SELF::class,
        'T_FLOAT' => Emitter\T_FLOAT::class,
        'T_STRING' => Emitter\T_STRING::class,
        'T_FUNCTION' => Emitter\T_FUNCTION::class,
        'T_VARIABLE' => Emitter\T_VARIABLE::class,
        'T_END' => Emitter\T_END::class,
        'T_PROCEDURE_END' => Emitter\T_PROCEDURE_END::class,
        'T_END_CODE' => Emitter\T_END_CODE::class,
    ];

    public function __construct( $variables, $strings, $types )
    {
        $this->variables = $variables;
        $this->strings = $strings;
        $this->types = $types;

        $this->lines = new Lines();
    }

    public function emitter( $node, $calculateLineNumber = true ){


        if($node['type'] == "root") return $this->emitRoot($node);

        if (!isset($this->emitters[ $node['type'] ])) {
            throw new \Exception(sprintf('Emitter not found for type %s', $node['type']));
        }

        return (new $this->emitters[ $node['type'] ]())->map(
            $node,

            function( $hex ) use ($calculateLineNumber){
                return $this->lines->get($hex, $calculateLineNumber);
            },

            function($token, $calculateLineNumber = true) {
                return $this->emitter($token, $calculateLineNumber);
            },

            [
                'strings' => $this->strings,
                'types' => $this->types,
                'variables' => $this->variables
            ]
        );


    }


//
//    static function detectVariableType( $value ){
//
//        if (isset(Manhunt2::$levelVarBoolean[ $value ])) return 'level_var boolean';
//        if (isset(Manhunt2::$constants[ $value ])) return 'constant';
//        if (isset(Manhunt2::$functions[ strtolower($value) ])) return 'function';
////        if (isset(self::headerVar[ strtolower($value) ])) return 'header_var';
//
//        return 'script_var';
//        throw new \Exception(sprintf('Unable to detect variable type %s', $value));
//
//    }



    private function emitRoot( $root ){

        $code = [];
        foreach ($root['body'] as $node) {
            $resultCode = $this->emitter( $node );
            foreach ($resultCode as $line) {
                $code[] = $line;
            }

        }

        return $code;
    }
}