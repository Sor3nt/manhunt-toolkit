<?php
namespace App\Service\Archive\Grf;

use App\MHT;
use App\Service\NBinary;

class Extract {
    
    /** @var  NBinary */
    private $binary;

    public function get( NBinary $binary, $game ){
        $this->binary = $binary;


        $fourCC = $this->binary->consume(4, NBinary::BINARY);
        $const = $this->binary->consume(4, NBinary::INT_32);

        $positions = $this->parsePositions();
        $table1 = $this->parseTabel1();
        $table2Names = $this->parseTabel2Names();

        $results = [];
//        foreach ($table1 as $entry) {
//            foreach ($entry['entries'] as &$positionIndex) {
//                $positionIndex = $positions[$positionIndex]['position'];
//            }
//
//            $results[ 'table1/' . $entry['name'] . '.json' ] = $entry;
//        }

        $positionsByIndex = [];
        foreach ($positions as $position) {
            $positionsByIndex[$position['groupIndex']][] = $position;
        }

        foreach ($table2Names as $index => $name) {
            $results[ 'table2/' . $name . '.json' ] = $positionsByIndex[$index];
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

            $commandName = $this->binary->getString();


            $unknown = $this->parseBlock();
            if(count($unknown)){
                die("unknown is not empty !!");
            }

            $unknown2 = $this->parseBlock();

            $waypoints = $this->parseWayPointBlock();

            $zero1 = $this->binary->consume(4, NBinary::INT_32);
            if ($zero1 != 0) die("zero is not zero ...");
            $zero2 = $this->binary->consume(4, NBinary::INT_32);
            if ($zero2 != 0) die("zero2 is not zero ...");

            $entries[] = [
                'id' => $id,
                'groupIndex' => $groupIndex,
                'commandName' => $commandName,
                'position' => $position,
                'rotation' => $rotation,
                'unknown' => $unknown,
                'unknown2' => $unknown2,
                'waypoints' => $waypoints
            ];
        }

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


    private function parseWayPointBlock(){

        $count = $this->binary->consume(4, NBinary::INT_32);

        $result = [];
        for($x = 0; $x < $count; $x++){

            $linkId = $this->binary->consume(4, NBinary::INT_32);
            $unknownNumber = $this->binary->consume(4, NBinary::INT_32);

            $unknown3 = $this->parseBlock();

            $result[] = [
                'linkId' => $linkId,
                'unknownNumber' => $unknownNumber,
                'unknown3' => $unknown3
            ];
        }

        return $result;
    }

}