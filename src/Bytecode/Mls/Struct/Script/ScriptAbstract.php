<?php

namespace App\Bytecode\Mls\Struct\Script;


use App\Bytecode\Helper;
use App\Service\Binary;

abstract class ScriptAbstract extends Helper {


    abstract public function toByteCode( $game, &$offset = 0 );

}