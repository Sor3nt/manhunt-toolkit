<?php
namespace App\Service\Archive;

use App\Service\Binary;

class Fsb {

    private function getHeader(Binary $header, $version ){


        /* number of samples in the file */
        $numsamples = $header->substr(0, 4, $header)->toInt();

        /* size in bytes of all of the sample headers including extended information */
        $shdrsize = $header->substr(0, 4, $header)->toInt();

        /* size in bytes of compressed sample data */
        $datasize = $header->substr(0, 4, $header)->toInt();

        /* extended fsb version */
        $version = $header->substr(0, 4, $header)->toHex();

        /* flags that apply to all samples in the fsb */
        $flags = $header->substr(0, 4, $header)->toInt();

        return [
            'count' => $numsamples,
            'headerSize' => $shdrsize,
            'contentSize' => $datasize,
            'version' => $version,
            'flags' => $flags
        ];


    }


    public function unpack($data){

        $binary = new Binary( $data );

        $version = (int) $binary->substr(3, 1)->toString();

        $header = $binary->substr(4, 20, $content);


        /** @var Binary $content */
        $header = $this->getHeader($header, $version);

        for($i = 0; $i < $header['count']; $i++){

            $sampleHeader = $content->substr(0, 80, $content);

            //2
            $size = $sampleHeader->substr(0, 2, $sampleHeader)->toUInt16();

            //32
            $name = $sampleHeader->substr(0, 30, $sampleHeader)->toString();

            //36
            $lengthsamples = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();
            //40
            $lengthcompressedbytes = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();
            //44
            $loopstart = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();
            //48
            $loopend = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();

            //52
            $mode = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();
            //56
            $deffreq = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();

            //58
            $defvol = $sampleHeader->substr(0, 2, $sampleHeader)->toUInt16();
            //60
            $defpan = $sampleHeader->substr(0, 2, $sampleHeader)->toInt();
            //62
            $defpri = $sampleHeader->substr(0, 2, $sampleHeader)->toUInt16();
            //64
            $numchannels = $sampleHeader->substr(0, 2, $sampleHeader)->toUInt16();
            //68
            $mindistance = $sampleHeader->substr(0, 4, $sampleHeader)->toFloat();
            //72
            $maxdistance = $sampleHeader->substr(0, 4, $sampleHeader)->toFloat();
            //76
            $size_32bits = $sampleHeader->substr(0, 4, $sampleHeader)->toInt();
            //80
            $varvol = $sampleHeader->substr(0, 2, $sampleHeader)->toUInt16();
            $varpan = $sampleHeader->substr(0, 2, $sampleHeader)->toInt();
            var_dump($mindistance);
            var_dump($maxdistance);


//            var_dump($lengthsamples);
exit;

        }

var_dump($header);
exit;



//        /* trunacted MD5 hash generated using only information which would break FEV/FSB combatibility */
//        $hash = $header->substr(0, 8, $header)->toInt();
//
//        /* Unique identifier. */
//        $guid = $header->substr(0, 4, $header)->toInt();
//

        var_dump($hash->uInt64());
exit;
    }

    public function pack( $records ){

    }

}