<?php

namespace App\Bytecode\Mls;


use App\Service\Binary;
use App\Service\FileFormat\Mls\Struct\Header\HeaderAbstract;
use Psr\Log\LoggerInterface;

class Srce {

    /** @var LoggerInterface  */
    var $logger;

    public function __construct( LoggerInterface $logger ) {
        $this->logger = $logger;
    }


    /**
     * @param $srceCode
     * @return array
     */
    public function parse( $srceCode ){


        return [
            new Header( $srceCode),
            new Scripts( $srceCode, $this->logger)
        ];
    }

}