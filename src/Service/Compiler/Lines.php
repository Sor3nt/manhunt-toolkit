<?php
namespace App\Service\Compiler;

use App\Bytecode\Helper;
use App\Service\Compiler\FunctionMap\Manhunt2;

class Lines {

    private $lineCount = 0;

    public function get( $hex, $calculateLineNumber = true ){


        if ($calculateLineNumber){
            $line = new Line( $hex, $this->lineCount);
            $this->lineCount++;

        }else{
            $line = new Line( $hex, 0);

        }

        return $line;
    }


}