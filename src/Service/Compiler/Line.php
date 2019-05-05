<?php
namespace App\Service\Compiler;

class Line {

    public $lineNumber = 0;
    public $debugLine = 0;
    public $hex = "";
    public $debug = "";

    public function __construct($hex, $lineNumber, $debug, $debugLineNext = 0) {
        $this->hex = $hex;
        $this->lineNumber = $lineNumber;
        $this->debug = $debug;
        $this->debugLine = $debugLineNext;
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