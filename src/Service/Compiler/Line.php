<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Line {

    public $lineNumber = 0;
    public $hex = "";

    public function __construct($hex, $lineNumber) {
        $this->hex = $hex;
        $this->lineNumber = $lineNumber;
    }

    public function __toString(){

        return $this->hex;
    }


    public function getValue(){
        return $this->hex;
    }

    public function getLine(){
        return $this->lineNumber;
    }

    public function getOffset(){
        return $this->lineNumber * 4;
    }


}