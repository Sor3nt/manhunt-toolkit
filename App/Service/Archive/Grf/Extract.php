<?php
namespace App\Service\Archive\Grf;

use App\MHT;
use App\Service\NBinary;

class Extract {
    
    /** @var  NBinary */
    private $binary;

    private $game;

    public function get( NBinary $binary, $game ){
        $this->binary = $binary;


        $fourCC = $this->binary->consume(4, NBinary::BINARY);

        if ($fourCC === "GNIA"){
            $game = MHT::GAME_MANHUNT_2;
            $const = $this->binary->consume(4, NBinary::INT_32);
        }else{
            $game = MHT::GAME_MANHUNT;
            $this->binary->current = 0;
        }

        $this->game = $game;

        $positions = $this->parsePositions();

        $table1 = $this->parseTabel1();
        $table2Names = $this->parseTabel2Names();

        $results = [];
        foreach ($table1 as $entry) {
            foreach ($entry['entries'] as &$positionIndex) {
                $positionIndex = $positions[$positionIndex]['position'];
            }

            $results[ 'path_' . $entry['name'] . '.json' ] = $entry;
        }

        $positionsByIndex = [];
        foreach ($positions as $position) {
            $positionsByIndex[$position['groupIndex']][] = $position;
        }

        foreach ($table2Names as $index => $name) {
            $results[ 'area_' . $name . '.json' ] = $positionsByIndex[$index];
        }

        return $results;
    }

    
    private function parseTabel2Names(){
        $count = $this->binary->consume(4, NBinary::INT_32);

        $results = [];
        for($x = 0; $x < $count; $x++){
            $results[] = $this->binary->getString();
        }

        return $results;
    }

    private function parseTabel1(){

        $count = $this->binary->consume(4, NBinary::INT_32);

        $results = [];
        for($i = 0; $i < $count; $i++){
            $results[] = [
                'name' => $this->binary->getString(),
                'entries' => $this->parseBlock()
            ];
        }


        return $results;

    }


    private function parsePositions(){

        $entryCount = $this->binary->consume(4, NBinary::INT_32);
        $entries = [];

        for($i = 0; $i < $entryCount; $i++){

            $id = (int) $this->binary->getString();

            $groupIndex = $this->binary->consume(4, NBinary::INT_32);

            $position = $this->binary->readXYZ();

            $rotation = $this->binary->consume(4, NBinary::FLOAT_32);

            $nodeName = $this->binary->getString();


            $unknown = $this->parseBlock();
//            if(count($unknown)){
//                die("unknown is not empty !!");
//            }


            if ($this->game == MHT::GAME_MANHUNT_2){
                $unknown2 = $this->parseBlock();
            }

            $waypoints = $this->parseWayPointBlock();


            if ($this->game == MHT::GAME_MANHUNT_2) {
                $zero1 = $this->binary->consume(4, NBinary::INT_32);
                if ($zero1 != 0) die("zero is not zero ...");
                $zero2 = $this->binary->consume(4, NBinary::INT_32);
                if ($zero2 != 0) die("zero2 is not zero ...");
            }

            $entries[] = [
                'id' => $id,
                'groupIndex' => $groupIndex,
                'nodeName' => $nodeName,
                'position' => $position,
                'speed' => $rotation,
                'unknown' => $unknown,
//                'unknown2' => $unknown2,
                'entries' => $waypoints
            ];
        }

        usort($entries, function ($a, $b){
            return $a['id'] > $b['id'];
        });
//
//        sort($this->tmp);
//        var_dump($this->tmp);
//        exit;

        return $entries;
    }

    private function parseBlock($type = NBinary::INT_32){
        $count = $this->binary->consume(4, NBinary::INT_32);

        $result = [];
        for($x = 0; $x < $count; $x++){
            $result[] = $this->binary->consume(4, $type);
        }

        return $result;
    }


    private $tmp = [];

    private function parseWayPointBlock(){

        $count = $this->binary->consume(4, NBinary::INT_32);

        $result = [];
        for($x = 0; $x < $count; $x++){

            $linkId1 = $this->binary->consume(4, NBinary::INT_32);
//            $linkId1 = $this->binary->consume(2, NBinary::INT_16);
//            $linkId2 = $this->binary->consume(2, NBinary::INT_16);
            $type = $this->binary->consume(4, NBinary::INT_32);

//            $this->tmp[]  = $linkId;
            $entry = [
                'linkId' => $linkId1,
//                'linkId2' => $linkId2,
                'type' => $type
            ];

            $unknown3 = $this->parseBlock();

            if (count($unknown3)){
                $entry['unknown3'] = $unknown3;
            }

            $result[] = $entry;
        }

        return $result;
    }

}