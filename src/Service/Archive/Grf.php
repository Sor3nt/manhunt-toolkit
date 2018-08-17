<?php
namespace App\Service\Archive;

use App\Service\Binary;

class Grf {

    public function unpack($data){

        /** @var Binary $remain */

        $binary = new Binary( $data );

        $scriptName = $binary->substr(0, 4, $remain)->toString();

        $version = $remain->substr(0, 4, $remain)->toInt();


        $entryCount = $remain->substr(0, 4, $remain)->toInt();
        $unknownSize = $remain->substr(0, 4, $remain)->toInt16();

        //??
        $spacer = $remain->substr(0, 4, $remain)->toHex();
        if ($spacer !== "00000000") die("ehh");

//        $unknown = $remain->substr(0, 4, $remain)->toHex();
//        $unknown = $remain->substr(0, 4, $remain)->toHex();
//        $unknown = $remain->substr(0, 4, $remain)->toHex();
//        $unknown = $remain->substr(0, 4, $remain)->toHex();
//        $unknown = $remain->substr(0, 4, $remain)->toHex();
//;

        //test hack

        $test = $remain->toHex();
        $test = str_replace([
            '3f00707070',
            '3f007070',
            '3f0070',
        ],'|', $test);
        $entries = explode("|", $test);


        foreach ($entries as $index => $entry) {

            if ($index == 0){
                $header = (new Binary($entry));

            }else{
                $parts = (new Binary($entry))->split(8);
                var_dump($parts[4]);

            }

//            exit;
        }


//        var_dump($entries, $entryCount);
        exit;
        do{
            $entry = $remain->substr(0, "\x3f", $remain);
//            $remain = $remain->skipBytes($entry->getMissedBytes());
var_dump($remain->substr(0,1)->toHex());
exit;
            if ($entry->length()){
                $entries[] = $entry;
            }

            while(
                $remain->substr(0,1)->toBinary() == "\x70" ||
                $remain->substr(0,1)->toBinary() == "\x00"
            ){
                $remain = $remain->substr(1);
            }


//            var_dump($entry->toHex());

        }while($remain->length());


        var_dump($scriptName, $version, $entryCount, count($entries));
exit;
    }

    public function pack( $records ){

    }

}