<?php
namespace App\Service\Compiler\Parser;

use App\Bytecode\Helper;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\EvaluateAssign;
use App\Service\Compiler\Token;

class T_WHILE {


    static public function map( $tokens, $current, \Closure $parseToken ){
        return T_IF::map($tokens, $current, $parseToken);
    }

}