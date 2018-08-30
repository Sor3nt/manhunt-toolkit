<?php
namespace App\Service\Archive;

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

    private function toInt8($hex){
        return is_int($hex) ? pack("c", $hex) :  current(unpack("c", hex2bin($hex)));
    }

    private function toInt16($hex){
        return is_int($hex) ? pack("s", $hex) : current(unpack("s", hex2bin($hex)));
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


        $testing = [];

        $entry = strtolower(bin2hex($data));

        $headerType = $this->toString($this->substr($entry, 0, 4));
        $version = $this->toInt($this->substr($entry, 0, 4));

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
//            $result['repeatingNameId'] = $repeatingNameId;

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
                    $result['unknownId2'] = $unknownId2;
                    $result['unknownFlag2'] = $this->substr($entry, 0, 4);
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

//            $result['flagCount'] = $flagCount;

            while($flagCount > 0){


                $flag = [
                    'key' => $this->substr($entry, 0, 4),
                    'active' => $this->substr($entry, 0, 4),
//                    'end' => $this->substr($entry, 0, 4)
                ];

                $end = $this->substr($entry, 0, 4);
                if ($end !== "00000000" && $end !== "01000000"){
//                    $flag['unknown'] = $end;
                    throw new \Exception(sprintf('Excepted 00000000 git %s', $end));
                }

                if (!isset($testing[$flag['key']])){
                    $testing[$flag['key']] = [];
                }
                if (!in_array($flag['active'], $testing[$flag['key']])){

                    $testing[$flag['key']][] = $flag['active'];
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

//        var_dump($testing);
//        exit;

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
            throw new \Exception('Remained content found, parseing is not valid!');
        }

        return $results;

    }

    public function pack( $records ){

    }

}