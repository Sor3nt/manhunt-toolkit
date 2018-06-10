<?php
namespace App\Service\Archive;

use App\Service\Binary;

class Fsb {

    private function getHeader(Binary $header ){
die("todo)");

        /* 'FSB4' */
        $id = $header->substr(0, 4, $header);

        if ($id->toString() !== "FSB4") die(sprintf("Version %s is not supported", $id->toString()));

        /* number of samples in the file */
        $numsamples = $header->substr(0, 4, $header)->toInt();

        /* size in bytes of all of the sample headers including extended information */
        $shdrsize = $header->substr(0, 4, $header)->toInt();

        /* size in bytes of compressed sample data */
        $datasize = $header->substr(0, 4, $header)->toInt();

        /* extended fsb version */
        $version = $header->substr(0, 4, $header)->toHex();

        /* flags that apply to all samples in the fsb */
        $mode = $header->substr(0, 4, $header)->toInt();

        /* trunacted MD5 hash generated using only information which would break FEV/FSB combatibility */
        $hash = $header->substr(0, 8, $header)->toInt();

        /* Unique identifier. */
        $guid = $header->substr(0, 4, $header)->toInt();


        return [
            'count' => $numsamples,
            'headerSize' => $shdrsize,
            'contentSize' => $datasize,
            'version' => $version,
            'mode' => $mode,
            'hash' => $hash,
            'guid' => $guid

        ];


    }


    public function unpack($data){

        $binary = new Binary( $data );

        /* 48 bytes Header */

        $header = $binary->substr(0, 48, $binary);








        var_dump($hash->uInt64());
exit;
    }

    public function pack( $records ){

    }

}