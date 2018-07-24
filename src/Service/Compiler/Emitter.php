<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;

class Emitter extends Helper {

    private $variables = [];

    /** @var Lines */
    private $lines;

    private $strings;

    private $types;
    private $const;
    private $stringHandler;

    private $emitters = [
        'T_DEFINE_SECTION_ENTITY' => Emitter\T_DEFINE_SECTION_ENTITY::class,
        'T_DEFINE_SECTION_VAR' => Emitter\T_DEFINE_SECTION_VAR::class,
        'T_DEFINE_SECTION_TYPE' => Emitter\T_DEFINE_SECTION_TYPE::class,
        'T_DEFINE_SECTION_CONST' => Emitter\T_DEFINE_SECTION_CONST::class,
        'T_DEFINE_TYPE' => Emitter\T_DEFINE_TYPE::class,
        'T_FALSE' => Emitter\T_FALSE::class,
        'T_TRUE' => Emitter\T_TRUE::class,
        'T_NIL' => Emitter\T_NIL::class,
        'T_SCRIPT' => Emitter\T_SCRIPT::class,
        'T_PROCEDURE' => Emitter\T_PROCEDURE::class,
        'T_WHILE' => Emitter\T_WHILE::class,
        'T_ASSIGN' => Emitter\T_ASSIGN::class,
        'T_INT' => Emitter\T_INT::class,
        'T_IF' => Emitter\T_IF::class,
        'T_SELF' => Emitter\T_SELF::class,
        'T_FLOAT' => Emitter\T_FLOAT::class,
        'T_IS_EQUAL' => Emitter\T_IS_EQUAL::class,
        'T_IS_NOT_EQUAL' => Emitter\T_IS_NOT_EQUAL::class,
        'T_STRING' => Emitter\T_STRING::class,
        'T_FUNCTION' => Emitter\T_FUNCTION::class,
        'T_VARIABLE' => Emitter\T_VARIABLE::class,
        'T_END' => Emitter\T_END::class,
        'T_CONDITION' => Emitter\T_CONDITION::class,
        'T_PROCEDURE_END' => Emitter\T_PROCEDURE_END::class,
        'T_END_CODE' => Emitter\T_END_CODE::class,
    ];

    public function __construct( $variables, $strings, $types, $const, $lineCount = 1 )
    {
        $this->variables = $variables;
        $this->strings = $strings;
        $this->types = $types;
        $this->const = $const;

        $this->lines = new Lines($lineCount);
    }

    public function emitter( $node, $calculateLineNumber = true, $customData = [] ){


        if($node['type'] == "root") return $this->emitRoot($node);

        if (!isset($this->emitters[ $node['type'] ])) {
            throw new \Exception(sprintf('Emitter not found for type %s', $node['type']));
        }

        return (new $this->emitters[ $node['type'] ]())->map(
            $node,

            function( $hex, $forceNewIndex = false ) use ($calculateLineNumber){
                return $this->lines->get($hex, $calculateLineNumber, $forceNewIndex);
            },

            function($token, $calculateLineNumber = true, $customData = []) {
                return $this->emitter($token, $calculateLineNumber, $customData);
            },

            [
                'calculateLineNumber' => $calculateLineNumber,
                'strings' => $this->strings,
                'types' => $this->types,
                'variables' => $this->variables,
                'const' => $this->const,
                'customData' => $customData
            ]
        );
    }

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