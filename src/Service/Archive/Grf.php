<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Binary;

class Grf {


    private function toString( $hex ){
        $hex = str_replace('00', '', $hex);
        return hex2bin($hex);
    }

    private function toInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));
    }

    private function toFloat( $hex ){
        return (float) current(unpack("f", hex2bin($hex)));
    }

    private function substr(&$hex, $start, $end){

        $result = substr($hex, $start * 2, $end * 2);
        $hex = substr($hex, $end * 2);
        return $result;
    }

    private function mbSubstr(&$hex, $start, $end){

        $result = mb_substr($hex, $start, $end );
        $hex = mb_substr($hex, $end);
        return $result;
    }

    public function unpack($data){

        $entry = strtolower(bin2hex($data));

        $headerType = $this->toString($this->substr($entry, 0, 4));

        //version
        $this->toInt($this->substr($entry, 0, 4));

        if ($headerType !== "GNIA")
            throw new \Exception(sprintf('Expected GNIA got: %s', $headerType));

        $entries = $this->toInt($this->substr($entry, 0, 4));

        $results = [
            'block1' => [],
            'block2' => [],
            'block3' => []
        ];

        while($entries > 0){
            $result = [];

            $entry = hex2bin($entry);
            $name = $this->mbSubstr($entry, 0, mb_strpos($entry, "\x00"));
            $entry = bin2hex($entry);

            $result['name'] = $name;
            if (trim($result['name']) == "") throw new \Exception(sprintf('Name not found, parsing invalid'));

            //we have full 4-bytes just remove the last 00
            if (strlen($name) + 1 == 4){
                $this->substr($entry, 0, 1);
            }else{
                $missed = 4 - strlen($name) % 4;
                $this->substr($entry, 0, $missed);
            }

            $repeatingNameId = $this->toInt($this->substr($entry, 0, 4));
            $result['repeatingNameId'] = $repeatingNameId;

            $xOrNull = $this->substr($entry, 0, 4);

            if ($xOrNull != "00000000"){

                $positions = [];

                $x = $xOrNull;
                while(substr($entry, 0, 8) != "0000003f"){
                    $positions[] = [
                        'x' => $x !== false ? $this->toFloat($x) : $this->toFloat($this->substr($entry, 0, 4)),
                        'y' => $this->toFloat($this->substr($entry, 0, 4)),
                        'z' => $this->toFloat($this->substr($entry, 0, 4)),
                    ];

                    $x = false;
                }

                $result['positions'] = $positions;

            }

            //remove the position ending sequence 0000003f
            $entry = substr($entry, 8);

            /**
             * 3 cases:
             * 1: nothing, here is the end
             * 2: 00707070, no name but parameters
             * 3: TEXT, label/name ending with 00
             */

            $entry = hex2bin($entry);
            $name2 = $this->mbSubstr($entry, 0, mb_strpos($entry, "\x00"));
            $entry = bin2hex($entry);

            $flags = [];

            //we have no second name
            if (strlen($name2) == 0){
                //remove the 00707070
                $test = $this->substr($entry, 0, 4);
                if ($test != "00707070") throw new \Exception(sprintf('Excepted 00707070 and got %s', $test));

                //remove the 00000000
                $test = $this->substr($entry, 0, 4);
                if ($test != "00000000") throw new \Exception(sprintf('Excepted 00000000 and got %s', $test));

                //remove the 00000000
                $unknownId2 = $this->substr($entry, 0, 4);

                if ($unknownId2 != "00000000"){

                    if ($unknownId2 != "01000000") throw new \Exception(sprintf('Excepted 01000000 and got %s', $test));

//                    $result['unknownId2'] = $unknownId2;
                    $result['unknown'] = $this->substr($entry, 0, 4);
                }

            }else{

                $result['action'] = $name2;
                $missed = 4 - strlen($name2) % 4;
                $this->substr($entry, 0, $missed );

                //remove the 00000000
                $test = $this->substr($entry, 0, 4);
                if ($test != "00000000") throw new \Exception(sprintf('Excepted 00000000 and got %s', $test));

                $unknownId2 = $this->substr($entry, 0, 4);

                if ($unknownId2 != "00000000"){
                    $result['unknownFlag2'] = $this->substr($entry, 0, 4);
                }

            }

            $flagCount = $this->toInt($this->substr($entry, 0, 4));

            while($flagCount > 0){


                $flag = [
                    'key' => $this->substr($entry, 0, 4),
                    'active' => $this->substr($entry, 0, 4)
                ];

                $end = $this->substr($entry, 0, 4);
                if ($end !== "00000000" && $end !== "01000000"){
                    throw new \Exception(sprintf('Excepted 00000000 git %s', $end));
                }

                if ($end == "01000000"){
                    $flag['additional'] = $this->substr($entry, 0, 4);
                }

                $flags[] = $flag;

                $flagCount--;
            }

            if (count($flags)){

                $result['flags'] = $flags;

                //skip flag ending sequence
                $entry = substr($entry, 8);
                $entry = substr($entry, 8);

            }

            $results['block1'][] = $result;

            $entries--;
        }

        $nextBlockCount = $this->toInt($this->substr($entry, 0, 4));

        while($nextBlockCount > 0){

            $result = [];

            $entry = hex2bin($entry);
            $name = $this->mbSubstr($entry, 0, mb_strpos($entry, "\x00"));
            $entry = bin2hex($entry);

            $result['name'] = $name;

            //we have full 4-bytes just remove the last 00
            if (strlen($name) + 1 == 4){
                $this->substr($entry, 0, 1);
            }else{
                $missed = 4 - strlen($name) % 4;
                $this->substr($entry, 0, $missed);
            }

            $flagCount = $this->toInt($this->substr($entry, 0, 4));

            $flags = [];
            while($flagCount > 0){
                $flags[] = $this->substr($entry, 0, 4);
                $flagCount--;
            }

            $result['flags'] = $flags;

            $results['block2'][] = $result;

            $nextBlockCount--;
        }

        $nextBlockCount = $this->toInt($this->substr($entry, 0, 4));

        while($nextBlockCount > 0){
            $entry = hex2bin($entry);
            $name = $this->mbSubstr($entry, 0, mb_strpos($entry, "\x00"));
            $entry = bin2hex($entry);

            //we have full 4-bytes just remove the last 00
            if (strlen($name) + 1 == 4){
                $this->substr($entry, 0, 1);
            }else{
                $missed = 4 - strlen($name) % 4;
                $this->substr($entry, 0, $missed);
            }

            $results['block3'][] = $name;

            $nextBlockCount--;
        }

        if ($entry != ""){
            throw new \Exception('Remained content found, parsing is not valid!');
        }

        return $results;

    }

    public function pack( $record ){

        $nameIndex = [];

        $data = current(unpack("H*", "GNIA"));
        $data .= Helper::fromIntToHex(1);

        $data .= Helper::fromIntToHex(count($record['block1']));

        foreach ($record['block1'] as $entry) {

            /**
             * Generate the NAME
             */

            $data .= $this->packString($entry['name']);


            // count up the name usage
//            if (!isset($nameIndex[$name])) $nameIndex[$name] = -1;
//            $nameIndex[$name]++;

//            $data .= Helper::fromIntToHex($nameIndex[$name]);
            $data .= Helper::fromIntToHex($entry['repeatingNameId']);


            /**
             * Generate the POSITIONS
             */
            foreach ($entry['positions'] as $position) {
                $data .= Helper::fromFloatToHex($position['x']);
                $data .= Helper::fromFloatToHex($position['y']);
                $data .= Helper::fromFloatToHex($position['z']);
            }

            $data .= "0000003f";

            /**
             * Generate the ACTION
             */

            if (isset($entry['action'])){

                $data .= $this->packString($entry['action']);
                $data .= "00000000";

                if (isset($entry['unknownFlag2'])){
                    //todo: ggf falsch, kommt von $unknownId2
                    $data .= "01000000";

                    $data .= $entry['unknownFlag2'];
                }else{
                    $data .= "00000000";
                }

            }else{
                $data .= "00707070";
                $data .= "00000000";

                if (isset($entry['unknown'])){
                    $data .= "01000000";
                    $data .= $entry['unknown'];
                }else{
                    $data .= "00000000";
                }
            }

            /**
             * Generate the FLAGS
             */

            $data .= Helper::fromIntToHex(count($entry['flags']));

            if (count($entry['flags'])){
                foreach ($entry['flags'] as $flag) {
                    $data .= $flag['key'];
                    $data .= $flag['active'];

                    if (isset($flag['additional'])){
                        $data .= "01000000";
                        $data .= $flag['additional'];
                    }else{
                        $data .= "00000000";
                    }
                }

                $data .= "00000000";
                $data .= "00000000";
            }
        }

        $data .= Helper::fromIntToHex(count($record['block2']));

        foreach ($record['block2'] as $entry) {

            $data .= $this->packString($entry['name']);
            $data .= Helper::fromIntToHex(count($entry['flags']));

            foreach ($entry['flags'] as $flag) {
                $data .= $flag;
            }
        }

        $data .= Helper::fromIntToHex(count($record['block3']));

        foreach ($record['block3'] as $entry) {
            $data .= $this->packString($entry);
        }

        return $data;

    }

    private function packString($string){
        $string = current(unpack("H*", $string)) . '00';

        //add padding
        $missed = 4 - (strlen($string) / 2) % 4;

        if ($missed > 0 && $missed < 4 ){
            $string .= str_repeat('70', $missed);
        }

        return $string;
    }

}