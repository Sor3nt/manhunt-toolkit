<?php
namespace App\Service\Compiler;

 class Token {

    const T_OBJECT                      = "object";
    const T_REAL                        = "real";
    const T_VEC3D                       = "vec3d";
    const T_STRING_ARRAY                = "stringarray";
    const T_CONSTANT_INTEGER            = "constant";
    const T_INT                         = 'integer';
    const T_STRING                      = 'string';
    const T_FLOAT                       = 'float';

    const T_BOOLEAN                     = 'T_BOOLEAN';
    const T_ARRAY                       = 'T_ARRAY';
    const T_RECORD                      = 'T_RECORD';
    const T_RECORD_END                  = 'T_RECORD_END';
    const T_CUSTOM_FUNCTION             = 'T_CUSTOM_FUNCTION';
    const T_CUSTOM_FUNCTION_NAME        = 'T_CUSTOM_FUNCTION_NAME';
    const T_CUSTOM_FUNCTION_END         = 'T_CUSTOM_FUNCTION_END';
    const T_TO                          = 'T_TO';
    const T_FOR                         = 'T_FOR';
    const T_MULTIPLY                    = 'T_MULTIPLY';
    const T_DEVISION                    = 'T_DEVISION';
    const T_DIVISION                    = 'T_DIVISION';
    const T_STATE                       = 'T_STATE';
    const T_IF_END                      = 'T_IF_END';
    const T_FOR_END                     = 'T_FOR_END';
    const T_WHILE_END                   = 'T_WHILE_END';
    const T_CASE_END                    = 'T_CASE_END';
    const T_SWITCH                      = 'T_SWITCH';
    const T_SWITCH_END                  = 'T_SWITCH_END';
    const T_SCRIPT_END                  = 'T_SCRIPT_END';
    const T_OF                          = 'T_OF';
    const T_CASE                        = 'T_CASE';
    const T_DEFINE_SECTION_ENTITY       = 'T_DEFINE_SECTION_ENTITY';
    const T_PROCEDURE                   = 'T_PROCEDURE';
    const T_PROCEDURE_DEFINE            = 'T_PROCEDURE_DEFINE';
    const T_FORWARD                     = 'T_FORWARD';
    const T_ADDITION                    = 'T_ADDITION';
    const T_SUBSTRACTION                = 'T_SUBSTRACTION';
    const T_DEFINE_SECTION_CONST        = 'T_DEFINE_SECTION_CONST';
    const T_PROCEDURE_NAME              = 'T_PROCEDURE_NAME';
    const T_PROCEDURE_END               = 'T_PROCEDURE_END';
    const T_DEFINE_SECTION_VAR          = 'T_DEFINE_SECTION_VAR';
    const T_DEFINE_SECTION_ARG          = 'T_DEFINE_SECTION_ARG';
    const T_DEFINE_SECTION_TYPE         = 'T_DEFINE_SECTION_TYPE';
    const T_TYPE_VAR                    = 'T_TYPE_VAR';
    const T_TYPE_ARG                    = 'T_TYPE_ARG';
    const T_HEADER_DEFINE               = 'T_HEADER_DEFINE';
    const T_WHITESPACE                  = 'T_WHITESPACE';
    const T_LINEEND                     = 'T_LINEEND';
    const T_DEFINE_TYPE                 = 'T_DEFINE_TYPE';
    const T_SCRIPTMAIN_NAME             = 'T_SCRIPTMAIN_NAME';
    const T_DEFINE_VAR                  = 'T_DEFINE_VAR';
    const T_DEFINE_ARG                  = 'T_DEFINE_ARG';
    const T_SCRIPTMAIN                  = 'T_SCRIPTMAIN';
    const T_ENTITY                      = 'T_ENTITY';
    const T_DEFINE                      = 'T_DEFINE';
    const T_SCRIPT                      = 'T_SCRIPT';
    const T_SCRIPT_NAME                 = 'T_SCRIPT_NAME';
    const T_SELF                        = 'T_SELF';
    const T_ELSE                        = 'T_ELSE';
    const T_SQUARE_BRACKET_OPEN         = 'T_SQUARE_BRACKET_OPEN';
    const T_SQUARE_BRACKET_CLOSE        = 'T_SQUARE_BRACKET_CLOSE';
    const T_BRACKET_OPEN                = 'T_BRACKET_OPEN';
    const T_BRACKET_CLOSE               = 'T_BRACKET_CLOSE';
    const T_SEPERATOR                   = 'T_SEPERATOR';
    const T_IF                          = 'T_IF';
    const T_WHILE                       = 'T_WHILE';
    const T_THEN                        = 'T_THEN';
    const T_BEGIN                       = 'T_BEGIN';
    const T_END                         = 'T_END';
    const T_END_ELSE                    = 'T_END_ELSE';
    const T_END_CODE                    = 'T_END_CODE';
    const T_DO                          = 'T_DO';
    const T_LEVEL_VAR                   = 'T_LEVEL_VAR';
    const T_NULL                        = 'T_NULL';
    const T_ASSIGN                      = 'T_ASSIGN';
    const T_CONDITION                   = 'T_CONDITION';
    const T_OPERATION                   = 'T_OPERATION';
    const T_IS_NOT_EQUAL                = 'T_IS_NOT_EQUAL';
    const T_IS_EQUAL                    = 'T_IS_EQUAL';
    const T_IS_GREATER                  = 'T_IS_GREATER';
    const T_IS_GREATER_EQUAL            = 'T_IS_GREATER_EQUAL';
    const T_IS_SMALLER                  = 'T_IS_SMALLER';
    const T_IS_SMALLER_EQUAL            = 'T_IS_SMALLER_EQUAL';
    const T_NOT                         = 'T_NOT';
    const T_OR                          = 'T_OR';
    const T_AND                         = 'T_AND';
    const T_NIL                         = 'T_NIL';
    const T_FUNCTION                    = 'T_FUNCTION';
    const T_VARIABLE                    = 'T_VARIABLE';
    const T_ENTITY_DEFINE               = 'T_ENTITY_DEFINE';
    const T_IF_VARIABLE                 = 'T_IF_VARIABLE';
}