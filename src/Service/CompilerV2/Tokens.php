<?php
namespace App\Service\CompilerV2;

 class Tokens {
    const T_MATH                        = "T_MATH";
    const T_NOP                         = "T_NOP";
    const T_BEGIN_WRAPPER               = "T_BEGIN_WRAPPER";
    const T_UNKNOWN                     = "T_UNKNOWN";
    const T_CONSTANT                    = "T_CONSTANT";
    const T_INT                         = 'T_INT';
    const T_STRING                      = 'T_STRING';
    const T_FLOAT                       = 'T_FLOAT';
    const T_IF_CASE                     = 'T_IF_CASE';
    const T_BOOLEAN                     = 'T_BOOLEAN';
    const T_CUSTOM_FUNCTION             = 'T_CUSTOM_FUNCTION';
    const T_FOR                         = 'T_FOR';
    const T_MULTIPLY                    = 'T_MULTIPLY';
    const T_MOD                         = 'T_MOD';
    const T_DIVISION                    = 'T_DIVISION';
    const T_STATE                       = 'T_STATE';
    const T_SWITCH                      = 'T_SWITCH';
    const T_CASE                        = 'T_CASE';
    const T_PROCEDURE                   = 'T_PROCEDURE';
    const T_FORWARD                     = 'T_FORWARD';
    const T_ADDITION                    = 'T_ADDITION';
    const T_SUBSTRACTION                = 'T_SUBSTRACTION';
    const T_SCRIPT                      = 'T_SCRIPT';
    const T_SELF                        = 'T_SELF';
    const T_ELSE                        = 'T_ELSE';
    const T_BRACKET_OPEN                = 'T_BRACKET_OPEN';
    const T_BRACKET_CLOSE               = 'T_BRACKET_CLOSE';
    const T_IF                          = 'T_IF';
    const T_THEN                        = 'T_THEN';
    const T_END                         = 'T_END';
    const T_DO                          = 'T_DO';
    const T_ASSIGN                      = 'T_ASSIGN';
    const T_CONDITION                   = 'T_CONDITION';
    const T_IS_NOT_EQUAL                = 'T_IS_NOT_EQUAL';
    const T_IS_EQUAL                    = 'T_IS_EQUAL';
    const T_IS_GREATER                  = 'T_IS_GREATER';
    const T_IS_GREATER_EQUAL            = 'T_IS_GREATER_EQUAL';
    const T_IS_SMALLER                  = 'T_IS_SMALLER';
    const T_IS_SMALLER_EQUAL            = 'T_IS_SMALLER_EQUAL';
    const T_NOT                         = 'T_NOT';
    const T_OR                          = 'T_OR';
    const T_AND                         = 'T_AND';
    const T_FUNCTION                    = 'T_FUNCTION';
    const T_VARIABLE                    = 'T_VARIABLE';
}