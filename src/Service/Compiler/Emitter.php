<?php
namespace App\Service\Compiler;


class Emitter {

    private $combinedVariables = [];
    private $combinedStrings = [];


    private $variables = [];

    /** @var Lines */
    private $lines;

    private $types;
    private $const;

    private $emitters = [
        'T_FOR' => Emitter\T_FOR::class,
        'T_BOOLEAN' => Emitter\T_BOOLEAN::class,
        'T_NIL' => Emitter\T_NIL::class,
        'T_SCRIPT' => Emitter\T_SCRIPT::class,
        'T_PROCEDURE' => Emitter\T_PROCEDURE::class,
        'T_CUSTOM_FUNCTION' => Emitter\T_CUSTOM_FUNCTION::class,
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
        'T_CONDITION' => Emitter\T_CONDITION::class,
        'T_SWITCH' => Emitter\T_SWITCH::class,
    ];

    public function __construct( $combinedVariables, $combinedStrings, $variables, $types, $const, $lineCount = 1 )
    {

        $this->combinedVariables = $combinedVariables;
        $this->combinedStrings = $combinedStrings;


        $this->variables = $variables;
        $this->types = $types;
        $this->const = $const;

        $this->lines = new Lines($lineCount);
    }

    public function emitter( $node, $calculateLineNumber = true, $customData = [] ){

        if (!isset($this->emitters[ $node['type'] ])) return [];

        return (new $this->emitters[ $node['type'] ]($customData))->map(
            $node,

            function( $hex, $forceNewIndex = false, $debug = false ) use ($calculateLineNumber){
                return $this->lines->get($hex, $calculateLineNumber, $forceNewIndex, $debug);
            },

            function($token, $calculateLineNumber = true, $customDataInner = []) use ($customData) {
                return $this->emitter($token, $calculateLineNumber, array_merge($customDataInner,$customData));
            },

            [
                'combinedVariables' => $this->combinedVariables,
                'combinedStrings' => $this->combinedStrings,


                'calculateLineNumber' => $calculateLineNumber,

                'types' => $this->types,
                'variables' => $this->variables,
                'const' => $this->const,
                'customData' => $customData
            ]
        );
    }

}