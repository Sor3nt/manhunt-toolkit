<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Archive\Bin\Build;
use App\Service\Archive\Bin\Extract;
use App\Service\Binary;

class Bin {




    public function unpack($entry, $outputTo){
        $extractor = new Extract($entry);
        $extractor->save($outputTo);
    }

    public function pack( $executions, $envExecutions ){
        $builder = new Build();
        return $builder->build( $executions, $envExecutions );

    }
}