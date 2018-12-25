<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class Dff {

    private $offset = 0;

    private function getBlock( NBinary $binary ){

        $id = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        //Dummy
        $binary->consume(4, NBinary::BINARY);

        return [ $id, $size ];
    }

    private function getEntry( NBinary $binary ){
        list($clumpID, $size) = $this->getBlock($binary);
        $size += 12;

        list($structID, $structSize) = $this->getBlock($binary);

        $binary->jumpTo($structSize + 12, false);

        list($struct2ID, $struct2Size) = $this->getBlock($binary);

        $binary->jumpTo($struct2Size, false);

        list($extId, $extSize) = $this->getBlock($binary);
        list($id, $nSize) = $this->getBlock($binary);


        $name = false;
        if ($id == 3){
            list($sId, $sSize) = $this->getBlock($binary);

            if ($sId == 286){
                $binary->jumpTo($sSize, false);

                list($s2Id, $s2Size) = $this->getBlock($binary);

                $name = $binary->consume($s2Size, NBinary::STRING);
            }else{
                $name = $binary->consume($sSize, NBinary::STRING);
            }

        }else if ($id == 39056126){
            $name = $binary->consume($nSize, NBinary::STRING);
        }

        if ($name == false){
            throw new \Exception('Name not found!');
        }


        return [
            'name' => $name,
            'offset' => $this->offset,
            'size' => $size
        ];

    }

    public function unpack($binary){
        $binary = new NBinary($binary);
        $fileSIZE = $binary->length();

        $results = [];

        do{
            $binary->jumpTo($this->offset);

            $entry = $this->getEntry($binary);

            $binary->jumpTo($entry['offset']);
            $entry['data'] = $binary->consume($entry['size'], NBinary::BINARY);

            $this->offset += $entry['size'];

            $results[] = $entry;

        }while( $this->offset < $fileSIZE );


        return $results;
    }

    public function pack( $files ){

        $binary = "";

        foreach ($files as $data) {
            $binary .= $data;
        }

        return $binary;
    }



}