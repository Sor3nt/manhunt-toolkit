<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\NBinary;

class Ifp {

    /**
     * Todo:
     * it looks like we messed up the command block for mh2, a \x00 get lost
     */

    private $game = false;

    private function toInt8($hex){
        return is_int($hex) ? pack("c", $hex) :  current(unpack("c", hex2bin($hex)));
    }

    private function toInt16($hex){
        return is_int($hex) ? pack("s", $hex) : current(unpack("s", hex2bin($hex)));
    }

    public function unpack($binary, $outputTo){

        $binary = new NBinary($binary);

        $game = "mh2-pc";

        /**
         * ROOT (ANCT)
         */
        $headerType = $binary->consume(4, NBinary::STRING);
//        $headerType = $this->toString($this->substr($entry, 0, 4));

//        $numBlockhex = $this->substr($entry, 0, 4);
        $numBlock = $binary->consume(4, NBinary::INT_32);

        if ($numBlock > 10000){
            $game = "mh2-wii";
            $binary->current -= 4;
            $binary->numericBigEndian = true;

            $numBlock = $binary->consume(4, NBinary::INT_32);
//            $numBlock = $this->toInt(Helper::toBigEndian($numBlockhex));
        }

        if ($headerType !== "ANCT")
            throw new \Exception(sprintf('Expected ANCT got: %s', $headerType));

        /**
         * BLOCK (BLOC)
         */
        $count = 1;
        while($numBlock > 0){

            $sectionBLOC = $binary->consume(4, NBinary::STRING);

            if ($sectionBLOC !== "BLOC")
                throw new \Exception(
                    sprintf('Expected BLOC got: %s', $sectionBLOC)
                );


            //Get Block name
            $blockNameLength = $binary->consume(4, NBinary::INT_32);
            $blockName = $binary->consume($blockNameLength, NBinary::STRING);

            $outputToBlock = $outputTo . $count . "#" . $blockName . '/';
            @mkdir($outputToBlock, 0777, true);

            /**
             * Animation Packs
             */

            $headerType = $binary->consume(4, NBinary::STRING);

            if ($headerType !== "ANPK")
                throw new \Exception(
                    sprintf('Expected ANPK got: %s', $headerType)
                );

            $animationCount = $binary->consume(4, NBinary::INT_32);

            /**
             * Animation Pack Entries
             */
            $this->extractAnimation($animationCount, $binary, $outputToBlock, $game);

            $numBlock--;
            $count++;
        }

    }

    public function extractAnimation($animationCount, NBinary $binary, $outputTo, $game = "mh2-pc"){
        $animations = [];

        $count = 1;
        while($animationCount > 0){

            $nameLabel = $binary->consume(4, NBinary::STRING);

            if ($nameLabel !== "NAME")
                throw new \Exception(
                    sprintf('Expected NAME got: %s', $nameLabel)
                );


            $animationNameLength = $binary->consume(4, NBinary::INT_32);

            $animationName = $binary->consume($animationNameLength, NBinary::STRING);


            $numberOfBones = bin2hex($binary->get(4));

            if (strpos(strtolower($numberOfBones), 'ff') !== false){


                $game = "mh2-ps2";
                $numberOfBones = substr($numberOfBones, 0, 2);
                if (strlen($numberOfBones) == 2){
                    $binary->consume(4, NBinary::BINARY);
                    $numberOfBones = $binary->unpack(hex2bin($numberOfBones), NBinary::INT_8) * -1;
                }else{

                    die("PS2 error");
                }

            }else{
                $numberOfBones = $binary->consume(4, NBinary::INT_32);
            }

            $chunkSize = $binary->consume(4, NBinary::INT_32);
            $frameTimeCount = $binary->consume(4, NBinary::FLOAT_32);

            if ($game == "mh2-ps2"){

                $frameTimeCount = (string) $frameTimeCount;
                if (strlen($frameTimeCount) > 15){
                    $frameTimeCount = (float) substr($frameTimeCount, 0, -5);
                }
            }

            $resultAnimation = [
                'chunkSize' => $chunkSize,
                'frameTimeCount' => $frameTimeCount,
            ];

            /**
             * Sequences
             */
            $bones = $this->extractBones($numberOfBones, $binary, $game, $chunkSize);

            $resultAnimation['bones'] = $bones;

            //headerSize
            $binary->consume(4, NBinary::INT_32);
            $unknown5      = $binary->consume(4, NBinary::HEX);

            //eachEntrySize
            $binary->consume(4, NBinary::INT_32);
            $numEntry      = $binary->consume(4, NBinary::INT_32);



            $resultAnimation['unknown5'] = $unknown5;

            $resultAnimation['entry'] = [];
            while ($numEntry > 0){

                if ($this->game == "mh1"){

                    $resultAnimation['entry'][] = [
                        'time' => $binary->consume(4, NBinary::FLOAT_32),
                        'unknown' => $binary->consume(4, NBinary::HEX),
                        'unknown2' => $binary->consume(4, NBinary::HEX),
                        'unknown3' => $binary->consume(4, NBinary::HEX),
                        'unknown4' => $binary->consume(4, NBinary::HEX),
                        'unknown6' => $binary->consume(4, NBinary::FLOAT_32),
                        'particleName' => $binary->consume(8, NBinary::STRING),
                        'particlePosition' => [
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32)
                        ],
                        'unknown5' => $binary->consume(4, NBinary::HEX)
                    ];
                }else{

                    $resultAnimation['entry'][] = [
                        'time' => $binary->consume(4, NBinary::FLOAT_32),
                        'unknown' => $binary->consume(4, NBinary::HEX),
                        'unknown2' => $binary->consume(4, NBinary::HEX),
                        'CommandName' => $binary->consume(64, NBinary::STRING),
                        'unknown3' => $binary->consume(4, NBinary::HEX),
                        'unknown6' => $binary->consume(4, NBinary::FLOAT_32),
                        'particleName' => $binary->consume(8, NBinary::STRING),
                        'particlePosition' => [
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32)
                        ],
                        'unknown5' => $binary->consume(40, NBinary::HEX)
                    ];

                }
                $numEntry--;
            }

            $animations[] = $resultAnimation;


            file_put_contents($outputTo . $count . "#". $animationName . ".json", \json_encode($resultAnimation, JSON_PRETTY_PRINT));

            $animationCount--;
            $count++;
        }

    }

    private function extractBones($numberOfBones, NBinary $binary, $game = "mh2-pc", $chunkSize = null){

        $bones = [];

        if ($game == "mh2-ps2"){

            $zlibData = $binary->consume($chunkSize, NBinary::BINARY);

            $zlibData = zlib_decode($zlibData);

            $binary = new NBinary($zlibData);

            //unknown ps2 values
            $binary->consume(4, NBinary::HEX);
            $binary->consume(4, NBinary::HEX);
        }

        while($numberOfBones > 0){


            $sequenceLabel = $binary->consume(4, NBinary::STRING);

            if ($sequenceLabel !== "SEQT" && $sequenceLabel !== "SEQU"){
                throw new \Exception(
                    sprintf('Expected SEQT or SEQU got: %s', $sequenceLabel)
                );
            }

            $boneId = $binary->consume(2, NBinary::INT_16);
            $frameType = $binary->consume(1, NBinary::INT_8);
            $frames = $binary->consume(2, NBinary::INT_16);
            $startTime = $binary->consume(2, NBinary::INT_16);


            //allen: need /2048.0*30 get frameid value
            $startTime = ($startTime / 2048) * 30;

            $resultBone = [
                'boneId' => $boneId,
                'frameType' => $frameType,
                'startTime' => $startTime,
                'frames' => []
            ];


            if ($frameType > 2){

                if ($startTime > 0){

                    $resultBone['unknown1'] = $binary->consume(2, NBinary::HEX);
                }


                $resultBone['unknown2'] = $binary->consume(2, NBinary::HEX);
                $resultBone['unknown3'] = $binary->consume(2, NBinary::HEX);
                $resultBone['unknown4'] = $binary->consume(2, NBinary::HEX);

            }

            /**
             * FRAMES
             */

            $this->game = $sequenceLabel == "SEQU" ? "mh1" : "mh2";

            $resultBone['frames'] = $this->extractFrames(
                $startTime,
                $frames,
                $frameType,
                $binary
            );

            $bones[] = $resultBone;

            $numberOfBones--;
        }

        return $bones;
    }

    private function extractFrames($startTime, $frames, $frameType, NBinary $binary){

        $resultFrames = [
            'frames' => []
        ];

        $index = 0;

        while($frames > 0){


            $resultFrame = [];

            if ($startTime == 0){

                // first frame == starTime
                if ($index == 0 && $frameType < 3){
                    $time = $startTime;
                }else{

                    $time = $binary->consume(2, NBinary::INT_16);
                }

                $resultFrame['time'] = $time;
            }

            if ($frameType < 3){

                $resultFrame['quat'] = [
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                ];

            }


            if ($frameType > 1){

                $resultFrame['position'] = [
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                ];

            }

            $resultFrames['frames'][] = $resultFrame;

            $frames--;
            $index++;
        }


        if ($this->game == "mh2"){
            $resultFrames['lastFrameTime'] = $binary->consume(4, NBinary::FLOAT_32);
        }


        return $resultFrames;
    }

    public function pack( $records, $game ){

        // Add ANCT
        $data = current(unpack("H*", "ANCT"));

        $data .= Helper::fromIntToHex(count($records));

        foreach ($records as $blockName => $animations) {

            $data .= current(unpack("H*", "BLOC"));

            /*
             * Add the length of the Block name and the block name itself
             */
            $blockName = explode("#", $blockName)[1] . "\x00";
            $data .= Helper::fromIntToHex(strlen($blockName));
            $data .= current(unpack("H*", $blockName));

            $data .= $this->packAnimation($animations, $game);
        }

        return $data;

    }

    public function packAnimation($animations, $game){

        $data = current(unpack("H*", "ANPK"));
        $data .= Helper::fromIntToHex(count($animations));

        foreach ($animations as $animationName => $animation) {

            $data .= current(unpack("H*", "NAME"));

            /*
             * Add the length of the Animation name and the Animation name itself
             */
            $animationName = explode("#", $animationName)[1];
            $animationName = explode(".json", $animationName)[0];
            $animationName .= "\x00";
            $data .= Helper::fromIntToHex(strlen($animationName));
            $data .= current(unpack("H*", $animationName));

            $data .= Helper::fromIntToHex(count($animation['bones']));

            $chunkData = "";
            $chunkSize = 0;

            foreach ($animation['bones'] as $bone) {

                $chunkData .= current(unpack("H*", $game == "mh1" ? "SEQU" : "SEQT"));

                $boneId = $bone['boneId'];


                $chunkData .= bin2hex($this->toInt16($boneId));
                $chunkData .= bin2hex($this->toInt8($bone['frameType']));
                if (!is_string($bone['frames'])){
                    $chunkData .= bin2hex($this->toInt16(count($bone['frames']['frames'])));

                }else{
                    $chunkData .= bin2hex($this->toInt16($bone['frameCount']));
                }

                /**
                 * Chunk start
                 */

                $chunk = bin2hex($this->toInt16((int)(($bone['startTime'] / 30) * 2048)));

                if ($bone['frameType'] > 2){
                    if ($bone['startTime'] > 0){
                        $chunk .= $bone['unknown1'];
                    }

                    $chunk .= $bone['unknown2'];
                    $chunk .= $bone['unknown3'];
                    $chunk .= $bone['unknown4'];

                }

                if (!is_string($bone['frames'])){

                    foreach ($bone['frames']['frames'] as $index => $frame) {

                        if ($bone['startTime'] == 0){

                            if ($index == 0 && $bone['frameType'] < 3){
                            }else{
                                $chunk .= bin2hex($this->toInt16($frame['time']));
                            }

                        }
//
//                        if ($bone['frameType'] < 3){
//                            $chunk .= bin2hex($this->toInt16($frame['quat'][0]));
//                            $chunk .= bin2hex($this->toInt16($frame['quat'][1]));
//                            $chunk .= bin2hex($this->toInt16($frame['quat'][2]));
//                            $chunk .= bin2hex($this->toInt16($frame['quat'][3]));
//                        }
//
//                        if ($bone['frameType'] > 1){
//                            $chunk .= bin2hex($this->toInt16($frame['position'][0]));
//                            $chunk .= bin2hex($this->toInt16($frame['position'][1]));
//                            $chunk .= bin2hex($this->toInt16($frame['position'][2]));
//                        }

                        if ($bone['frameType'] < 3){

                            $chunk .= bin2hex($this->toInt16( intval($frame['quat'][0] * 2048) ));
                            $chunk .= bin2hex($this->toInt16( intval($frame['quat'][1] * 2048) ));
                            $chunk .= bin2hex($this->toInt16( intval($frame['quat'][2] * 2048) ));
                            $chunk .= bin2hex($this->toInt16( intval($frame['quat'][3] * 2048) ));
                        }

                        if ($bone['frameType'] > 1){

                            $chunk .= bin2hex($this->toInt16( intval($frame['position'][0] * 2048) ));
                            $chunk .= bin2hex($this->toInt16( intval($frame['position'][1] * 2048) ));
                            $chunk .= bin2hex($this->toInt16( intval($frame['position'][2] * 2048) ));
                        }

                    }

                    $chunkSize += strlen($chunk);

                    $chunkData .= $chunk;
                    if ($game == "mh2"){

                        if (!isset($bone['frames']['lastFrameTime'])){


                            if ($bone['startTime'] == 0){
                                $lastFrameTime = end($bone['frames']['frames'])['time'] / 2048;
                            }else if (count($bone['frames']['frames']) == 1) {#
                                $lastFrameTime = ($bone['startTime'] / 2048);
                            }else {

                                $startTime = ($bone['startTime'] / 2048);
                                $frameCount = (count($bone['frames']['frames'])/ 30) / 2048;

                                $lastFrameTime = $startTime - $frameCount;
                            }



                            $bone['frames']['lastFrameTime'] = $lastFrameTime;
                        }

                        $chunkData .= Helper::fromFloatToHex($bone['frames']['lastFrameTime']);
                    }

                }else{
                    $chunkData .= $bone['frames'];

                }



            }

            $data .= Helper::fromIntToHex($chunkSize / 2);
            $data .= Helper::fromFloatToHex($animation['frameTimeCount']);

            $data .= $chunkData;



            //headerSize
            $data .= Helper::fromIntToHex(16);

            $data .= $animation['unknown5'];

            //eachEntrySize
            if($game == "mh2"){
                $data .= Helper::fromIntToHex(160);
            }else{
                $data .= Helper::fromIntToHex(64);
            }

//            if ($isMh1To2Port){
//                $data .= Helper::fromIntToHex(0);
//
//            }else{

            $data .= Helper::fromIntToHex(count($animation['entry']));

            foreach ($animation['entry'] as $entry) {
                if (!is_string($entry)) {

                    $data .= Helper::fromFloatToHex($entry['time']);
                    $data .= $entry['unknown'];
                    $data .= $entry['unknown2'];

                    if ($game == "mh2"){


                        $commandName = current(unpack("H*", $entry['CommandName']));
                        $missed = 128 - strlen($commandName) % 128;
                        if ($missed > 0){
                            $commandName .= str_repeat('00', $missed / 2);
                        }
                        $data .= $commandName;

                        $data .= $entry['unknown3'];
                    }else{
                        $data .= $entry['unknown3'];
                        $data .= $entry['unknown4'];
                    }

                    $data .= Helper::fromFloatToHex($entry['unknown6']);

                    $particleName = current(unpack("H*", $entry['particleName']));
                    $missed = 16 - strlen($particleName) % 16;
                    if ($missed > 0){
                        $particleName .= str_repeat('00', $missed / 2);
                    }
                    $data .= $particleName;


                    foreach ($entry['particlePosition'] as $pPos) {
                        $data .= Helper::fromFloatToHex($pPos);
                    }

                    $data .= $entry['unknown5'];

                }else{
                    $data .= $entry;

                }
            }
//            }

        }

        return $data;
    }
}