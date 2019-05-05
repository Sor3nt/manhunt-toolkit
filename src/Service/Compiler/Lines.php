<?php
namespace App\Service\Compiler;

class Lines {

    private $lineCount = 1;
    private $debugLine = 0;

    public function __construct( $lineCount = 1){
        $this->lineCount = $lineCount;
    }

    public function get( $hex, $calculateLineNumber = true, $forceNewIndex = false, $debug = false, $debugLineNext = false ){

        if ($debugLineNext) $this->debugLine++;
        if ($forceNewIndex){
            $this->lineCount = $forceNewIndex;
        }

        if ($calculateLineNumber){
            $line = new Line( $hex, $this->lineCount, $debug, $this->debugLine);
            $this->lineCount++;

        }else{
            $line = new Line( $hex, 0, $debug);
        }

        return $line;
    }


}