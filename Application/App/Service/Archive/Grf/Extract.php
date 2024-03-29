<?php
namespace App\Service\Archive\Grf;

use App\MHT;
use App\Service\NBinary;

class Extract {
    
    /** @var  NBinary */
    private $binary;

    private $game;

    public $keepOrder = true;

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

        $areaPositions = $this->parseAreaPositions();

        $waypoints = $this->parseWaypoints();
        $areaNames = $this->parseAreaNames();

        $results = [];
        foreach ($waypoints as $entry) {
            $results[ 'path_' . $entry['name'] . '.json' ] = $entry;
        }

        $positionsByIndex = [];

        foreach ($areaPositions as $position) {
            $positionsByIndex[$position['groupIndex']][] = $position;
        }

        foreach ($areaNames as $index => $name) {
            $results[ $index . '#area_' . $name . '.json' ] = $positionsByIndex[$index];
        }

        return $results;
    }

    
    private function parseAreaNames(){
        $count = $this->binary->consume(4, NBinary::INT_32);

        $results = [];
        for($x = 0; $x < $count; $x++){
            $results[] = $this->binary->getString();
        }

        return $results;
    }

    private function parseWaypoints(){

        $count = $this->binary->consume(4, NBinary::INT_32);

        $results = [];
        for($i = 0; $i < $count; $i++){
            $results[] = [
                'order' => $i,
                'name' => $this->binary->getString(),
                'entries' => $this->parseBlock()
            ];
        }


        return $results;

    }


    private function parseAreaPositions(){

        $entryCount = $this->binary->consume(4, NBinary::INT_32);
        $entries = [];

        for($i = 0; $i < $entryCount; $i++){

            $name = $this->binary->getString();

            $groupIndex = $this->binary->consume(4, NBinary::INT_32);

            $position = $this->binary->readXYZ();

            $speed = $this->binary->consume(4, NBinary::FLOAT_32);

            $nodeName = $this->binary->getString();


            $unknown = $this->parseBlock();


            $unknown2 = [];
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
                'id' => $name,
                'linkId' => $i,
                'groupIndex' => $groupIndex,
                'nodeName' => $nodeName,
                'position' => $position,
                'speed' => $speed,
                'unknown' => $unknown,
                'unknown2' => $unknown2,
                'entries' => $waypoints
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

            $linkId1 = $this->binary->consume(4, NBinary::INT_32);
            $type = $this->binary->consume(4, NBinary::INT_32);

            $entry = [
                'linkId' => $linkId1,
                'type' => $type,
                'unknown' => $this->parseBlock()
            ];

            $result[] = $entry;
        }

        return $result;
    }

}