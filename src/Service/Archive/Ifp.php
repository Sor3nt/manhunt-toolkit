<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Binary;
use Symfony\Component\Console\Output\OutputInterface;

class Ifp {
    private $game = false;

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

    public function unpack($data, OutputInterface $output = null, $outputTo, $saveAsJson = true){

        $entry = bin2hex($data);

        /**
         * ROOT (ANCT)
         */
        $headerType = $this->toString($this->substr($entry, 0, 4));
        $numBlock = $this->toInt($this->substr($entry, 0, 4));

        if ($headerType !== "ANCT")
            throw new \Exception(sprintf('Expected ANCT got: %s', $headerType));


        if (!is_null($output)) $output->writeln(
            sprintf('| <info>Header:</info> %s', $headerType . "\n") .
            sprintf('| <info>Number of Blocks:</info> %s', $numBlock)
        );

        /**
         * BLOCK (BLOC)
         */
        $count = 1;
        while($numBlock > 0){

            $sectionBLOC = $this->toString($this->substr($entry, 0, 4));
            $blockNameLength = $this->toInt($this->substr($entry, 0, 4));


            if ($sectionBLOC !== "BLOC")
                throw new \Exception(
                    sprintf('Expected BLOC got: %s', $sectionBLOC)
                );


            //Get Block name
            $blockName = $this->toString(
                $this->substr($entry, 0, $blockNameLength)
            );

            if (!is_null($output)) $output->writeln(
                sprintf('| <info>Found Block:</info> %s', $blockName)
            );

            $outputToBlock = $outputTo . $count . "#" . $blockName . '/';
            @mkdir($outputToBlock, 0777, true);

            /**
             * Animation Packs
             */

            $headerType = $this->toString($this->substr($entry, 0, 4));
            $animationCount = $this->toInt($this->substr($entry, 0, 4));

            if ($headerType !== "ANPK")
                throw new \Exception(
                    sprintf('Expected ANPK got: %s', $headerType)
                );

            if (!is_null($output)) $output->writeln(
                sprintf('  | <info>Current Section:</info> %s', $headerType) . "\n" .
                sprintf('    | <info>Animations:</info> %s', $animationCount)
            );


            /**
             * Animation Pack Entries
             */
            $this->extractAnimation($animationCount, $entry, $output, $outputToBlock, $saveAsJson);

            $numBlock--;
            $count++;
        }

    }

    public function extractAnimation($animationCount, &$entry, OutputInterface $output = null, $outputTo, $saveAsJson, $game = "mh2-pc"){
        $animations = [];

        $count = 1;
        while($animationCount > 0){

            $debug = $this->substr($entry, 0, 4);

            $nameLabel = $this->toString($debug);

            if ($nameLabel !== "NAME")
                throw new \Exception(
                    sprintf('Expected NAME got: %s', $debug)
                );

            if ($game == "mh2-wii"){
                $animationNameLength = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
            }else{
                $animationNameLength = $this->toInt($this->substr($entry, 0, 4));
            }

            $animationName = $this->toString(
                $this->substr($entry, 0, $animationNameLength)
            );


            // if PS2 MH2 !!
//            $numberOfBones = $this->substr($entry, 0, 4);
//            $numberOfBones = str_replace('ff', '', $numberOfBones);
//            if (strlen($numberOfBones) == 2){
//                $numberOfBones = $this->toInt8($numberOfBones) * -1;
//            }else{
//                $numberOfBones = $this->toInt($numberOfBones);
//
//            }
            //end


            if ($game == "mh2-wii"){
                $numberOfBones = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
                $chunkSize = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
                $frameTimeCount = $this->toFloat(Helper::toBigEndian($this->substr($entry, 0, 4)));
            }else{
                $numberOfBones = $this->toInt($this->substr($entry, 0, 4));
                $chunkSize = $this->toInt($this->substr($entry, 0, 4));
                $frameTimeCount = $this->toFloat($this->substr($entry, 0, 4));
            }

            $resultAnimation = [
                'chunkSize' => $chunkSize,
                'frameTimeCount' => $frameTimeCount,
            ];

            if (!is_null($output)) $output->writeln(
                sprintf('      | <info>Animation:</info> %s', $animationName) . "\n" .
                sprintf('        | <info>Bones:</info> %s', $numberOfBones) . "\n" .
                sprintf('        | <info>Chunk Size:</info> %s', $chunkSize) . "\n" .
                sprintf('        | <info>?? Frame time count:</info> %s', $frameTimeCount)
            );


            /**
             * Sequences
             */

            $resultAnimation['bones'] = $this->extractBones($numberOfBones, $entry, $output, $saveAsJson, $game);

            if ($game == "mh2-wii"){
                $headerSize    = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
                $unknown5      = $this->substr($entry, 0, 4);
                $eachEntrySize = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
                $numEntry      = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
            }else{
                $headerSize    = $this->toInt($this->substr($entry, 0, 4));
                $unknown5      = $this->substr($entry, 0, 4);
                $eachEntrySize = $this->toInt($this->substr($entry, 0, 4));
                $numEntry      = $this->toInt($this->substr($entry, 0, 4));

            }

            $resultAnimation['unknown5'] = $unknown5;

            $resultAnimation['entry'] = [];
            while ($numEntry > 0){

                if ($this->game == "mh1"){
                    if ($saveAsJson){

                        $resultAnimation['entry'][] = [
                            'time' => $this->toFloat($this->substr($entry, 0, 4)),
                            'unknown' => $this->substr($entry, 0, 4),
                            'unknown2' => $this->substr($entry, 0, 4),
                            'unknown3' => $this->substr($entry, 0, 4),
                            'unknown4' => $this->substr($entry, 0, 4),
                            'unknown6' => $this->toFloat($this->substr($entry, 0, 4)),
                            'particleName' => $this->toString($this->substr($entry, 0, 8)),
                            'particlePosition' => [
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                            ],
                            'unknown5' => $this->substr($entry, 0, 4),
                        ];
                    }else{

                        $resultAnimation['entry'][] = $this->substr($entry, 0, 16 * 4);
                    }
                }else{
                    if ($saveAsJson){
                        $resultAnimation['entry'][] = [
                            'time' => $this->toFloat($this->substr($entry, 0, 4)),
                            'unknown' => $this->substr($entry, 0, 4),
                            'unknown2' => $this->substr($entry, 0, 4),
                            'CommandName' => $this->toString($this->substr($entry, 0, 64)),
                            'unknown3' => $this->substr($entry, 0, 4),
                            'unknown6' => $this->toFloat($this->substr($entry, 0, 4)),
                            'particleName' => $this->toString($this->substr($entry, 0, 8)),
                            'particlePosition' => [
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                                $this->toFloat($this->substr($entry, 0, 4)),
                            ],
                            'unknown5' => $this->substr($entry, 0, 40),
                        ];
                    }else{

                        $resultAnimation['entry'][] = $this->substr($entry, 0, 40 * 4);
                    }
                }
                $numEntry--;
            }

            $animations[] = $resultAnimation;

            if (!is_null($output)) $output->writeln(
                sprintf('        | <info>Animation name:</info> %s', $animationName) . "\n" .
                sprintf('        | <info>Header size:</info> %s', $headerSize) . "\n" .
                sprintf('        | <info>Unknown 5:</info> %s', $unknown5) . "\n" .
                sprintf('        | <info>Each Entry Size:</info> %s', $eachEntrySize) . "\n" .
                sprintf('        | <info>Entry Count:</info> %s', $numEntry)
            );

            file_put_contents($outputTo . $count . "#". $animationName . ".json", \json_encode($resultAnimation, JSON_PRETTY_PRINT));

            $animationCount--;
            $count++;
        }

    }

    private function extractBones($numberOfBones, &$entry, OutputInterface $output = null, $saveAsJson, $game = "mh2-pc"){

        $bones = [];
        while($numberOfBones > 0){
            $debug = $this->substr($entry, 0, 4);
            $sequenceLabel = $this->toString($debug);

            if ($sequenceLabel !== "SEQT" && $sequenceLabel !== "SEQU")
                throw new \Exception(
                    sprintf('Expected SEQT or SEQU got: %s', $debug)
                );

            if ($game == "mh2-wii"){
                $boneId = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                $frameType = $this->toInt8(Helper::toBigEndian($this->substr($entry, 0, 1)));
                $frames = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                $startTime = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));

            }else{
                $boneId = $this->toInt16($this->substr($entry, 0, 2));
                $frameType = $this->toInt8($this->substr($entry, 0, 1));
                $frames = $this->toInt16($this->substr($entry, 0, 2));
                $startTime = $this->toInt16($this->substr($entry, 0, 2));
            }

            $resultBone = [
                'boneId' => $boneId,
                'frameType' => $frameType,
                'startTime' => $startTime,
                'frames' => []
            ];

            if ($saveAsJson == false){
                $resultBone['frameCount'] = $frames;
            }

            if (!is_null($output)) $output->writeln(
                sprintf('          | <info>Bone Id:</info> %s', $boneId) . "\n" .
                sprintf('          | <info>Frame Type:</info> %s', $frameType) . "\n" .
                sprintf('          | <info>Frames:</info> %s', $frames) . "\n" .
                sprintf('          | <info>Start Time:</info> %s', $startTime)
            );

            if ($frameType > 2){

                if ($startTime > 0){
                    $unknown1 = $this->substr($entry, 0, 2);

                    $resultBone['unknown1'] = $unknown1;

                    if (!is_null($output)) $output->writeln(
                        sprintf('          | <info>?? Unknown:</info> %s', $unknown1)
                    );
                }

                $unknown2 = $this->substr($entry, 0, 2);
                $unknown3 = $this->substr($entry, 0, 2);
                $unknown4 = $this->substr($entry, 0, 2);

                $resultBone['unknown2'] = $unknown2;
                $resultBone['unknown3'] = $unknown3;
                $resultBone['unknown4'] = $unknown4;


                if (!is_null($output)) $output->writeln(
                    sprintf(
                        '          | <info>?? Unknown:</info> %s,%s,%s',
                        $unknown2,
                        $unknown3,
                        $unknown4
                    )
                );
            }

            /**
             * FRAMES
             */

            $this->game = $sequenceLabel == "SEQU" ? "mh1" : "mh2";

            $resultBone['frames'] = $this->extractFrames(
                $startTime,
                $frames,
                $frameType,
                $entry,
                $output,
                $saveAsJson,
                $game
            );

            $bones[] = $resultBone;

            $numberOfBones--;
        }

        return $bones;
    }

    private function extractFrames($startTime, $frames, $frameType, &$entry, OutputInterface $output = null, $saveAsJson, $game = "mh2-pc"){

        //bigedian swap not supported
        if ($game == "mh2-wii"){
            $saveAsJson = true;
        }

        $resultFrames = [
            'frames' => []
        ];

        $index = 0;
        $bytes = 0;

        while($frames > 0){

            if ($saveAsJson){


                $resultFrame = [];

                if ($startTime == 0){

                    // first frame == starTime
                    if ($index == 0 && $frameType < 3){
                        $time = $startTime;
                    }else{
                        if ($game == "mh2-wii"){
                            $time = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        }else{
                            $time = $this->toInt16($this->substr($entry, 0, 2));
                        }
                    }

                    $resultFrame['time'] = $time;

                    if (!is_null($output)) $output->writeln(
                        sprintf('            | <info>Time:</info> %s', $time)
                    );
                }

                if ($frameType < 3){

                    if ($game == "mh2-wii") {
                        $x = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        $y = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        $z = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        $w = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                    }else{
                        $x = $this->toInt16($this->substr($entry, 0, 2));
                        $y = $this->toInt16($this->substr($entry, 0, 2));
                        $z = $this->toInt16($this->substr($entry, 0, 2));
                        $w = $this->toInt16($this->substr($entry, 0, 2));
                    }

                    $resultFrame['quat'] = [$x,$y,$z,$w];

                    if (!is_null($output)) $output->writeln(
                        sprintf(
                            '            | <info>Quat:</info> %s,%s,%s,%s',
                            $x,
                            $y,
                            $z,
                            $w
                        )
                    );
                }


                if ($frameType > 1){

                    if ($game == "mh2-wii") {
                        $x = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        $y = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                        $z = $this->toInt16(Helper::toBigEndian($this->substr($entry, 0, 2)));
                    }else{
                        $x = $this->toInt16($this->substr($entry, 0, 2));
                        $y = $this->toInt16($this->substr($entry, 0, 2));
                        $z = $this->toInt16($this->substr($entry, 0, 2));

                    }

                    $resultFrame['position'] = [$x,$y,$z];

                    if (!is_null($output)) $output->writeln(
                        sprintf(
                            '            | <info>Position:</info> %s,%s,%s',
                            $x,
                            $y,
                            $z
                        )
                    );
                }

                $resultFrames['frames'][] = $resultFrame;
            }else{

                if ($startTime == 0){

                    // first frame == starTime
                    if ($index == 0 && $frameType < 3){
                    }else{
                        $bytes += 2;
//                        $time = $this->toInt16($this->substr($entry, 0, 2));
                    }

                }

                if ($frameType < 3){

                    $bytes += 8;

//                    $x = $this->toInt16($this->substr($entry, 0, 2));
//                    $y = $this->toInt16($this->substr($entry, 0, 2));
//                    $z = $this->toInt16($this->substr($entry, 0, 2));
//                    $w = $this->toInt16($this->substr($entry, 0, 2));
                }


                if ($frameType > 1){
                    $bytes += 6;

                }

            }

            $frames--;
            $index++;
        }


        if ($this->game == "mh2"){
            if ($saveAsJson) {

                if ($game == "mh2-wii") {
                    $resultFrames['lastFrameTime'] = $this->toFloat(Helper::toBigEndian($this->substr($entry, 0, 4)));
                }else{
                    $resultFrames['lastFrameTime'] = $this->toFloat($this->substr($entry, 0, 4));

                }


                if (!is_null($output)) $output->writeln(
                    sprintf('          | <info>Last frame time:</info> %s', $resultFrames['lastFrameTime'])
                );
            }else{
                $bytes += 4;

            }
        }

        if ($saveAsJson == false){
            return $this->substr($entry, 0, $bytes);
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
            $data .= Helper::fromIntToHex($animation['chunkSize']);
            $data .= Helper::fromFloatToHex($animation['frameTimeCount']);


            foreach ($animation['bones'] as $bone) {

                $data .= current(unpack("H*", $game == "mh1" ? "SEQU" : "SEQT"));

                $data .= bin2hex($this->toInt16($bone['boneId']));
                $data .= bin2hex($this->toInt8($bone['frameType']));
                if (!is_string($bone['frames'])){
                    $data .= bin2hex($this->toInt16(count($bone['frames']['frames'])));

                }else{
                    $data .= bin2hex($this->toInt16($bone['frameCount']));
                }
                $data .= bin2hex($this->toInt16($bone['startTime']));


                if ($bone['frameType'] > 2){
                    if ($bone['startTime'] > 0){
                        $data .= $bone['unknown1'];
                    }

                    $data .= $bone['unknown2'];
                    $data .= $bone['unknown3'];
                    $data .= $bone['unknown4'];

                }

                if (!is_string($bone['frames'])){

                    foreach ($bone['frames']['frames'] as $index => $frame) {
//var_dump($bone['startTime']);
//exit;
                        if ($bone['startTime'] == 0){

                            if ($index == 0 && $bone['frameType'] < 3){

                            }else{
                                $data .= bin2hex($this->toInt16($frame['time']));

                            }

                        }

                        if ($bone['frameType'] < 3){
                            $data .= bin2hex($this->toInt16($frame['quat'][0]));
                            $data .= bin2hex($this->toInt16($frame['quat'][1]));
                            $data .= bin2hex($this->toInt16($frame['quat'][2]));
                            $data .= bin2hex($this->toInt16($frame['quat'][3]));


                        }

                        if ($bone['frameType'] > 1){

                            $data .= bin2hex($this->toInt16($frame['position'][0]));
                            $data .= bin2hex($this->toInt16($frame['position'][1]));
                            $data .= bin2hex($this->toInt16($frame['position'][2]));


                        }

                    }


                    if ($game == "mh2"){
                        $data .= Helper::fromFloatToHex($bone['frames']['lastFrameTime']);
                    }

                }else{
                    $data .= $bone['frames'];

                }





            }


            //headerSize
            $data .= Helper::fromIntToHex(16);

            $data .= $animation['unknown5'];

            //eachEntrySize
            if($game == "mh2"){
                $data .= Helper::fromIntToHex(160);
            }else{
                $data .= Helper::fromIntToHex(64);
            }

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
        }

        return $data;
    }
}