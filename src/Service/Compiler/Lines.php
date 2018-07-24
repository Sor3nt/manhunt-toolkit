<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Lines {

    private $lineCount = 1;

    public function get( $hex, $calculateLineNumber = true, $forceNewIndex = false ){

        if ($forceNewIndex){
            $this->lineCount = $forceNewIndex;
        }

        if ($calculateLineNumber){
            $line = new Line( $hex, $this->lineCount);
            $this->lineCount++;

        }else{

            $line = new Line( $hex, 0);

        }

        return $line;
    }


}