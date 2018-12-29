<?php
namespace App\Service\Archive;

use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;

class Mls {


    /**
     * @param $data
     * @param string $game
     * @return array
     */
    public function unpack($data, $game = "mh2"){

        $extractor = new Extract($data, $game);

        return $extractor->get();
    }


    /**
     * @param $scripts
     * @return string
     */
    public function pack( $scripts ){

        $builder = new Build();
        return $builder->build( $scripts );

    }
}