<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;

class Inst {

    /**
     * Manhunt 2 - entity_pc.inst pack/unpack
     */

    public function unpack($data){

        $extractor = new Extract($data);

        return $extractor->get();

    }

    public function pack( $records, $bigEndian = false ){
        $builder = new Build();
        return $builder->build( $records, $bigEndian );

    }



}
